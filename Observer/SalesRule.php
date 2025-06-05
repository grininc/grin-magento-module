<?php

declare(strict_types=1);

namespace Grin\Module\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\SalesRule\Model\Rule;

class SalesRule implements ObserverInterface
{
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $salesRule = $observer->getRule();
        if (!$salesRule instanceof Rule) {
            return;
        }

        $extensionAttributes = $salesRule->getExtensionAttributes();
        if (isset($extensionAttributes['is_grin_only'])) {
            $salesRule->setData('is_grin_only', (int) ($extensionAttributes['is_grin_only']));
        }
    }
}
