<?php

declare(strict_types=1);

namespace Grin\Module\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class SystemConfig
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var string[]|null
     */
    private $data;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param string[]|null $data
     */
    public function __construct(ScopeConfigInterface $scopeConfig, $data = [])
    {
        $this->scopeConfig = $scopeConfig;
        $this->data = $data;
    }

    /**
     * @return bool
     */
    public function isGrinScriptActive(): bool
    {
        return $this->scopeConfig->isSetFlag($this->data['grin_script_active'] ?? '', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function isGrinCartWidgetActive(): bool
    {
        return $this->scopeConfig->isSetFlag($this->data['grin_cart_widget_active'] ?? '', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function isGrinWebhookActive(): bool
    {
        return $this->scopeConfig->isSetFlag($this->data['grin_webhook_active'] ?? '');
    }

    /**
     * @return string
     */
    public function getGrinWebhookUrl(): string
    {
        return (string) $this->scopeConfig->getValue($this->data['grin_webhook_url'] ?? '');
    }

    /**
     * @param int|null $storeId (Optional)
     * @return string
     */
    public function getWebhookToken(int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            $this->data['grin_webhook_token'] ?? '',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param int|null $storeId (Optional)
     * @return string
     */
    public function getSalesRuleToken(int $storeId = null): string
    {
        return (string) $this->scopeConfig->getValue(
            $this->data['grin_sales_rule_token'] ?? '',
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
