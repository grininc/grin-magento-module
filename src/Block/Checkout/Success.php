<?php

declare(strict_types=1);

namespace Grin\Module\Block\Checkout;

use Grin\Module\Model\SystemConfig;
use Magento\Checkout\Model\Session;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class Success extends Template
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var OrderInterface|null
     */
    private $order;

    /**
     * @var SystemConfig
     */
    private $systemConfig;

    /**
     * @param Context $context
     * @param Session $checkoutSession
     * @param OrderRepositoryInterface $orderRepository
     * @param SystemConfig $systemConfig
     * @param array $data
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        OrderRepositoryInterface $orderRepository,
        SystemConfig $systemConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->checkoutSession = $checkoutSession;
        $this->orderRepository = $orderRepository;
        $this->systemConfig = $systemConfig;
    }

    /**
     * @return OrderInterface|null
     */
    protected function getOrder(): ?OrderInterface
    {
        if (!$this->order) {
            $this->order = $this->orderRepository->get($this->checkoutSession->getLastOrderId());
        }

        return $this->order;
    }

    /**
     * @return string
     */
    public function toHtml(): string
    {
        if (!$this->systemConfig->isGrinScriptActive()) {
            return '';
        }

        return parent::toHtml();
    }

    /**
     * @param OrderInterface $order
     * @return $this
     */
    public function setOrder(OrderInterface $order): Success
    {
        $this->order = $order;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrderId(): string
    {
        return (string) $this->getOrder()->getIncrementId();
    }

    /**
     * @return float|int|null
     */
    public function getSubtotal()
    {
        $order = $this->getOrder();

        // float added to type cast from string as some plugins were writing strings of the values.
        return $order->getBaseSubtotal() - abs((float)$order->getDiscountAmount() ?: 0);
    }
}
