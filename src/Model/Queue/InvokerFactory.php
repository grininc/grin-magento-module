<?php

declare(strict_types=1);

namespace Grin\Module\Model\Queue;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;

class InvokerFactory
{
    /**
     * @return \Magento\Framework\MessageQueue\CallbackInvoker|\Magento\Framework\MessageQueue\CallbackInvokerInterface
     * @throws LocalizedException
     */
    public function get()
    {
        if (class_exists(\Magento\Framework\MessageQueue\CallbackInvokerInterface::class, false)) {
            return ObjectManager::getInstance()->get(\Magento\Framework\MessageQueue\CallbackInvokerInterface::class);
        }

        return ObjectManager::getInstance()->get(\Magento\Framework\MessageQueue\CallbackInvoker::class);
    }
}
