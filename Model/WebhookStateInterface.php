<?php

declare(strict_types=1);

namespace Grin\Module\Model;

interface WebhookStateInterface
{
    public const POSTFIX_DELETED = '_deleted';
    public const POSTFIX_UPDATED = '_updated';
    public const POSTFIX_CREATED = '_created';
}
