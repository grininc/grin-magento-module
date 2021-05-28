<?php

declare(strict_types=1);

namespace Grin\Module\Model;

use Grin\Module\Api\GrinServiceInterface;
use Grin\Module\Model\Http\Client\Adapter\CurlFactory;
use Grin\Module\Model\Http\UriFactory;
use Grin\Module\Model\SystemConfig;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

class GrinService implements GrinServiceInterface
{
    /**
     * @var CurlFactory
     */
    private $curlFactory;

    /**
     * @var SystemConfig
     */
    private $systemConfig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var UriFactory
     */
    private $uriFactory;

    /**
     * @var Json
     */
    private $json;

    /**
     * @param CurlFactory $curlFactory
     * @param SystemConfig $systemConfig
     * @param LoggerInterface $logger
     * @param UriFactory $uriFactory
     * @param Json $json
     */
    public function __construct(
        CurlFactory $curlFactory,
        SystemConfig $systemConfig,
        LoggerInterface $logger,
        UriFactory $uriFactory,
        Json $json
    ) {
        $this->curlFactory = $curlFactory;
        $this->systemConfig = $systemConfig;
        $this->logger = $logger;
        $this->uriFactory = $uriFactory;
        $this->json = $json;
    }

    /**
     * @inheritDoc
     */
    public function send(string $topic, array $data): bool
    {
        if (!$this->canSend()) {
            return false;
        }

        $payload = $this->json->serialize($data);
        $this->logger->info(sprintf('Sending the webhook "%s" %s', $topic, $payload));

        try {
            $curl = $this->curlFactory->create();

            $curl->setCurlOption(CURLOPT_RETURNTRANSFER, true);
            $curl->setCurlOption(CURLINFO_HEADER_OUT, true);

            if ($curl instanceof \Zend_Http_Client_Adapter_Curl) {
                $curl->setConfig(['timeout' => 30, 'maxredirects' => 1]);
            }

            $uri = $this->getUri();
            $curl->connect($uri->getHost(), $uri->getPort(), true);
            $curl->write('POST', $uri, 1.1, $this->getHeaders($payload, $topic), $payload);
            $code = curl_getinfo($curl->getHandle(), CURLINFO_HTTP_CODE);
            $curl->close();
            if ($code !== 200) {
                throw new LocalizedException(__('Grin service webhook has failed with status code %1', $code));
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage(), $e->getTrace());
            throw new LocalizedException(__('Grin service webhook has failed'), $e);
        }

        return true;
    }

    /**
     * @return bool
     */
    private function canSend(): bool
    {
        if (!$this->systemConfig->isGrinWebhookActive()) {
            return false;
        }

        if (!$this->systemConfig->getWebhookToken()) {
            $this->logger->critical('Authentication token has not been set up for Grin Webhooks');

            return false;
        }

        return true;
    }

    /**
     * @return \Laminas\Uri\Uri|\Zend_Uri_Http
     * @throws \Zend_Uri_Exception
     */
    private function getUri()
    {
        $uri = $this->uriFactory->create();

        if (!$uri instanceof \Laminas\Uri\Uri) {
            $uri = \Zend_Uri_Http::fromString(self::GRIN_URL);
        } else {
            $uri->parse(self::GRIN_URL);
        }

        $uri->setPort($uri->getScheme() === 'https' ? 443 : 80);
        return $uri;
    }

    /**
     * @param string $payload
     * @param string $topic
     * @return string[]
     */
    private function getHeaders(string $payload, string $topic): array
    {
        return [
            'Content-Type: application/json',
            'Content-Length: ' . strlen($payload),
            'Authorization: ' . $this->systemConfig->getWebhookToken(),
            'Magento-Webhook-Topic: ' . $topic
        ];
    }
}
