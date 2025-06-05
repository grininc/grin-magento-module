<?php

declare(strict_types=1);

namespace Grin\Module\Api;

use Magento\Framework\Exception\LocalizedException;

/**
 * @api
 */
interface GrinServiceInterface
{
    /**
     * @param string $topic
     * @param array $data
     *
     * @return string|null
     * @throws LocalizedException
     */
    public function send(string $topic, array $data): ?string;

    /**
     * @return bool
     */
    public function hasErrors(): bool;
}
