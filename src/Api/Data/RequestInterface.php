<?php

declare(strict_types=1);

namespace Grin\Module\Api\Data;

/**
 * @api
 */
interface RequestInterface
{
    /**
     * @return string
     */
    public function getTopic(): string;

    /**
     * @param string $topic
     * @return RequestInterface
     */
    public function setTopic(string $topic): RequestInterface;

    /**
     * @return string
     */
    public function getSerializedData(): string;

    /**
     * @param string $serializedData
     * @return RequestInterface
     */
    public function setSerializedData(string $serializedData): RequestInterface;

    /**
     * @return int|null
     */
    public function getMessageStatusId(): ?int;

    /**
     * @param int $messageStatusId
     * @return RequestInterface
     */
    public function setMessageStatusId(int $messageStatusId): RequestInterface;
}
