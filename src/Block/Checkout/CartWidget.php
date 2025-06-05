<?php

declare(strict_types=1);

namespace Grin\Module\Block\Checkout;

use Grin\Module\Model\SystemConfig;
use Magento\Checkout\Model\CompositeConfigProvider;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class CartWidget extends Template
{
    /**
     * @var CompositeConfigProvider
     */
    private $configProvider;

    /**
     * @var SystemConfig
     */
    private $systemConfig;

    /**
     * @param Context $context
     * @param CompositeConfigProvider $configProvider
     * @param SystemConfig $systemConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        CompositeConfigProvider $configProvider,
        SystemConfig $systemConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configProvider = $configProvider;
        $this->systemConfig = $systemConfig;
    }

    /**
     * @return string
     */
    public function toHtml(): string
    {
        if (!$this->systemConfig->isGrinCartWidgetActive()) {
            return '';
        }

        return parent::toHtml();
    }

    /**
     * @return string
     */
    public function getQuoteId(): string
    {
        return (string) ($this->configProvider->getConfig()['quoteData']['entity_id'] ?? '');
    }
}
