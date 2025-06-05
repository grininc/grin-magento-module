<?php

declare(strict_types=1);

namespace Grin\Module\Api;

/**
 * @api
 */
interface PublisherInterface
{
    public const TOPIC = 'grin_module_webhook';

    /**
     * @param string $topic
     * @param array $data
     * @return void
     */
    public function publish(string $topic, array $data);
}
