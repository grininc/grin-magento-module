<?php

declare(strict_types=1);

namespace Grin\Module\Test\Integration\Fixture;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order as SalesOrder;
use Magento\Sales\Model\Order\Address as OrderAddress;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Sales\Model\Order\Payment;
use Magento\Store\Model\StoreManagerInterface;

class Order
{
    /**
     * @return void
     * @throws \Exception
     */
    public function createOrder()
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = Bootstrap::getObjectManager()->create(Product::class)->createProduct();

        $addressData = $this->getAddressData();

        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        $billingAddress = $objectManager->create(OrderAddress::class, ['data' => $addressData]);
        $billingAddress->setAddressType('billing');

        $shippingAddress = clone $billingAddress;
        $shippingAddress->setId(null)->setAddressType('shipping');

        /** @var Payment $payment */
        $payment = $objectManager->create(Payment::class);
        $payment->setMethod('checkmo')
            ->setAdditionalInformation('last_trans_id', '11122')
            ->setAdditionalInformation(
                'metadata',
                [
                    'type' => 'free',
                    'fraudulent' => false,
                ]
            );

        /** @var OrderItem $orderItem */
        $orderItem = $objectManager->create(OrderItem::class);
        $orderItem->setProductId($product->getId())
            ->setQtyOrdered(2)
            ->setBasePrice($product->getPrice())
            ->setPrice($product->getPrice())
            ->setRowTotal($product->getPrice())
            ->setProductType('simple')
            ->setName($product->getName())
            ->setSku($product->getSku());

        /** @var SalesOrder $order */
        $order = $objectManager->create(SalesOrder::class);
        $order->setIncrementId('100000001')
            ->setState(SalesOrder::STATE_PROCESSING)
            ->setStatus($order->getConfig()->getStateDefaultStatus(SalesOrder::STATE_PROCESSING))
            ->setSubtotal(100)
            ->setGrandTotal(100)
            ->setBaseSubtotal(100)
            ->setBaseGrandTotal(100)
            ->setCustomerIsGuest(true)
            ->setCustomerEmail('customer@null.com')
            ->setBillingAddress($billingAddress)
            ->setShippingAddress($shippingAddress)
            ->setStoreId($objectManager->get(StoreManagerInterface::class)->getStore()->getId())
            ->addItem($orderItem)
            ->setPayment($payment);

        /** @var OrderRepositoryInterface $orderRepository */
        $orderRepository = $objectManager->create(OrderRepositoryInterface::class);
        $orderRepository->save($order);
    }

    /**
     * @return string[]
     */
    private function getAddressData(): array
    {
        return [
            'region' => 'CA',
            'region_id' => '12',
            'postcode' => '11111',
            'lastname' => 'lastname',
            'firstname' => 'firstname',
            'street' => 'street',
            'city' => 'Los Angeles',
            'email' => 'admin@example.com',
            'telephone' => '11111111',
            'country_id' => 'US'
        ];
    }
}
