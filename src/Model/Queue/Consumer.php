<?php

declare(strict_types=1);

namespace Grin\Module\Model\Queue;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Communication\ConfigInterface as CommunicationConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\MessageQueue\ConnectionLostException;
use Magento\Framework\MessageQueue\Consumer\ConfigInterface as ConsumerConfig;
use Magento\Framework\MessageQueue\ConsumerConfigurationInterface;
use Magento\Framework\MessageQueue\ConsumerInterface;
use Magento\Framework\MessageQueue\EnvelopeFactory;
use Magento\Framework\MessageQueue\EnvelopeInterface;
use Magento\Framework\MessageQueue\LockInterface;
use Magento\Framework\MessageQueue\MessageController;
use Magento\Framework\MessageQueue\MessageEncoder;
use Magento\Framework\MessageQueue\MessageLockException;
use Magento\Framework\MessageQueue\MessageValidator;
use Magento\Framework\MessageQueue\QueueInterface;
use Magento\Framework\MessageQueue\QueueRepository;
use Psr\Log\LoggerInterface;

/**
 * @see \Magento\Framework\MessageQueue\Consumer
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Consumer implements ConsumerInterface
{
    /**
     * @var ConsumerConfigurationInterface
     */
    private $configuration;

    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @var MessageEncoder
     */
    private $messageEncoder;

    /**
     * @var InvokerFactory
     */
    private $invokerFactory;

    /**
     * @var MessageController
     */
    private $messageController;

    /**
     * @var QueueRepository
     */
    private $queueRepository;

    /**
     * @var EnvelopeFactory
     */
    private $envelopeFactory;

    /**
     * @var MessageValidator
     */
    private $messageValidator;

    /**
     * @var ConsumerConfig
     */
    private $consumerConfig;

    /**
     * @var CommunicationConfig
     */
    private $communicationConfig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param CommunicationConfig $communicationConfig
     * @param InvokerFactory $invokerFactory
     * @param EnvelopeFactory $envelopeFactory
     * @param MessageController $messageController
     * @param MessageEncoder $messageEncoder
     * @param MessageValidator $messageValidator
     * @param ResourceConnection $resource
     * @param ConsumerConfig $consumerConfig
     * @param ConsumerConfigurationInterface $configuration
     * @param QueueRepository $queueRepository
     * @param LoggerInterface $logger
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        CommunicationConfig $communicationConfig,
        InvokerFactory $invokerFactory,
        EnvelopeFactory $envelopeFactory,
        MessageController $messageController,
        MessageEncoder $messageEncoder,
        MessageValidator $messageValidator,
        ResourceConnection $resource,
        ConsumerConfig $consumerConfig,
        ConsumerConfigurationInterface $configuration,
        QueueRepository $queueRepository,
        LoggerInterface $logger
    ) {
        $this->communicationConfig = $communicationConfig;
        $this->invokerFactory = $invokerFactory;
        $this->envelopeFactory = $envelopeFactory;
        $this->messageController = $messageController;
        $this->messageEncoder = $messageEncoder;
        $this->messageValidator = $messageValidator;
        $this->resource = $resource;
        $this->consumerConfig = $consumerConfig;
        $this->configuration = $configuration;
        $this->queueRepository = $queueRepository;
        $this->logger = $logger;
    }

    /**
     * @param null|int $maxNumberOfMessages
     * @throws LocalizedException
     */
    public function process($maxNumberOfMessages = null)
    {
        $queue = $this->configuration->getQueue();

        if (isset($maxNumberOfMessages)) {
            $this->invokerFactory
                ->get()
                ->invoke($queue, $maxNumberOfMessages, $this->getTransactionCallback($queue));
            return;
        }

        $queue->subscribe($this->getTransactionCallback($queue));
    }

    /**
     * Get transaction callback. This handles the case of both sync and async.
     *
     * @param QueueInterface $queue
     * @return callable
     */
    private function getTransactionCallback(QueueInterface $queue): callable
    {
        return function (EnvelopeInterface $message) use ($queue) {
            /** @var LockInterface $lock */
            $lock = null;
            try {
                $topicName = $message->getProperties()['topic_name'];
                $topicConfig = $this->communicationConfig->getTopic($topicName);
                $lock = $this->messageController->lock($message, $this->configuration->getConsumerName());

                if ($topicConfig[CommunicationConfig::TOPIC_IS_SYNCHRONOUS]) {
                    $responseBody = $this->dispatchMessage($message, true);
                    $responseMessage = $this->envelopeFactory->create(
                        ['body' => $responseBody, 'properties' => $message->getProperties()]
                    );
                    $this->sendResponse($responseMessage);
                } else {
                    $allowedTopics = $this->configuration->getTopicNames();
                    if (in_array($topicName, $allowedTopics)) {
                        $this->dispatchMessage($message);
                    } else {
                        $queue->reject($message);
                        return;
                    }
                }
                $queue->acknowledge($message);
            } catch (MessageLockException $exception) {
                $queue->acknowledge($message);
            } catch (ConnectionLostException $e) {
                if ($lock) {
                    $this->resource->getConnection()
                        ->delete($this->resource->getTableName('queue_lock'), ['id = ?' => $lock->getId()]);
                }
            } catch (NotFoundException $e) {
                $queue->acknowledge($message);
                $this->logger->warning($e->getMessage());
            } catch (\Exception $e) {
                $queue->reject($message, false, $e->getMessage());
                if ($lock) {
                    $this->resource->getConnection()
                        ->delete($this->resource->getTableName('queue_lock'), ['id = ?' => $lock->getId()]);
                }
            }
        };
    }

    /**
     * Decode message and invoke callback method, return reply back for sync processing.
     *
     * @param EnvelopeInterface $message
     * @param boolean $isSync
     * @return string|null
     * @throws LocalizedException
     */
    private function dispatchMessage(EnvelopeInterface $message, $isSync = false)
    {
        $properties = $message->getProperties();
        $topicName = $properties['topic_name'];
        $handlers = $this->configuration->getHandlers($topicName);
        $decodedMessage = $this->messageEncoder->decode($topicName, $message->getBody());
        $decodedMessage->setMessageStatusId((int) $message->getProperties()['relation_id']);

        if (isset($decodedMessage)) {
            $messageSchemaType = $this->configuration->getMessageSchemaType($topicName);
            if ($messageSchemaType == CommunicationConfig::TOPIC_REQUEST_TYPE_METHOD) {
                foreach ($handlers as $callback) {
                    // phpcs:disable Magento2.Functions.DiscouragedFunction.Discouraged
                    $result = call_user_func_array($callback, $decodedMessage);
                    return $this->processSyncResponse($topicName, $result);
                }
            } else {
                foreach ($handlers as $callback) {
                    // phpcs:disable Magento2.Functions.DiscouragedFunction.Discouraged
                    $result = call_user_func($callback, $decodedMessage);
                    if ($isSync) {
                        return $this->processSyncResponse($topicName, $result);
                    }
                }
            }
        }
        return null;
    }

    /**
     * Validate and encode synchronous handler output.
     *
     * @param string $topicName
     * @param mixed $result
     * @return string
     * @throws LocalizedException
     */
    private function processSyncResponse($topicName, $result)
    {
        if (!isset($result)) {
            throw new LocalizedException(__('No reply message resulted in RPC.'));
        }

        $this->messageValidator->validate($topicName, $result, false);
        return $this->messageEncoder->encode($topicName, $result, false);
    }

    /**
     * @param EnvelopeInterface $envelope
     * @throws LocalizedException
     */
    private function sendResponse(EnvelopeInterface $envelope)
    {
        $messageProperties = $envelope->getProperties();
        $connectionName = $this->consumerConfig
            ->getConsumer($this->configuration->getConsumerName())->getConnection();
        $queue = $this->queueRepository->get($connectionName, $messageProperties['reply_to']);
        $queue->push($envelope);
    }
}
