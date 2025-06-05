<?php

declare(strict_types=1);

namespace Grin\Module\Model\Data;

use Grin\Module\Api\Data\RequestInterface;

class Request implements RequestInterface
{
    /**
     * @var string
     */
    private $topic;

    /**
     * @var string
     */
    private $serializedData;

    /**
     * @var int|null
     */
    private $messageStatusId;

    /**
     * @return string
     */
    public function getTopic(): string
    {
        return $this->topic;
    }

    /**
     * @param string $topic
     * @return RequestInterface
     */
    public function setTopic(string $topic): RequestInterface
    {
        $this->topic = $topic;
        return $this;
    }

    /**
     * @return string
     */
    public function getSerializedData(): string
    {
        return $this->serializedData;
    }

    /**
     * @param string $serializedData
     * @return RequestInterface
     */
    public function setSerializedData(string $serializedData): RequestInterface
    {
        $this->serializedData = $serializedData;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getMessageStatusId(): ?int
    {
        return $this->messageStatusId;
    }

    /**
     * @param int $messageStatusId
     * @return RequestInterface
     */
    public function setMessageStatusId(int $messageStatusId): RequestInterface
    {
        $this->messageStatusId = $messageStatusId;

        return $this;
    }
}
