<?php

declare(strict_types=1);

namespace Grin\Module\Test\Integration;

use Grin\Module\Test\Integration\Fixture\Order as OrderFixture;
use Grin\Module\Test\Integration\Model\MysqlQueueMessageManager;
use Grin\Module\Test\Integration\Model\OrderManager;
use Magento\Sales\Model\Order;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Serialize\Serializer\Json;

class OrderPublisherTest extends TestCase
{
    /**
     * @var Json
     */
    private $json;

    /**
     * @var MysqlQueueMessageManager
     */
    private $messageManager;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->messageManager = Bootstrap::getObjectManager()->create(MysqlQueueMessageManager::class);
        $this->json = Bootstrap::getObjectManager()->create(Json::class);
    }

    /**
     * @magentoConfigFixture default/grin_integration/webhook/active 1
     * @magentoConfigFixture default/grin_integration/webhook/token integration_tests
     * @magentoDataFixture createOrderFixture
     * @return void
     */
    public function testCreateOrder()
    {
        $message = $this->messageManager->getLastMessage();

        $this->assertJson($message->getBody());
        $body = $this->json->unserialize($message->getBody());
        $this->assertJson($body['serialized_data']);
        $this->assertTrue($body['topic'] === 'sales_order_created');
        $this->assertTrue(is_int($this->json->unserialize($body['serialized_data'])['id']));
    }

    /**
     * @magentoConfigFixture default/grin_integration/webhook/active 1
     * @magentoConfigFixture default/grin_integration/webhook/token integration_tests
     * @magentoDataFixture createOrderFixture
     * @return void
     */
    public function testUpdateOrder()
    {
        $order = Bootstrap::getObjectManager()->get(OrderManager::class)->getLastOrder();
        $order->setStatus(Order::STATE_HOLDED);
        $order->save();

        $message = $this->messageManager->getLastMessage();

        $this->assertJson($message->getBody());
        $body = $this->json->unserialize($message->getBody());
        $this->assertJson($body['serialized_data']);
        $this->assertTrue($body['topic'] === 'sales_order_updated');
        $this->assertTrue($this->json->unserialize($body['serialized_data'])['id'] === (int) $order->getId());
    }

    /**
     * @return void
     * @throws \Exception
     */
    public static function createOrderFixture()
    {
        /** @var OrderFixture $categoryFixture */
        $fixture = Bootstrap::getObjectManager()->get(OrderFixture::class);
        $fixture->createOrder();
    }
}
