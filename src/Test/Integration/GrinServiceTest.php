<?php

namespace Grin\Module\Test\Integration;

use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Grin\Module\Model\GrinService;

class GrinServiceTest extends TestCase
{
    /**
     * @magentoConfigFixture default/grin_integration/webhook/active 1
     * @magentoConfigFixture default_store grin_integration/webhook/token integration_tests
     * @dataProvider dataProvider
     */
    public function testSend(string $token, array $data)
    {
        $service = Bootstrap::getObjectManager()->get(GrinService::class);
        $this->assertTrue('422 - "Could not find account"' == $service->send($token, $data));
    }

    /**
     * @return array[]
     */
    public function dataProvider(): array
    {
        return [
            ['sales_order_created', ['id' => 1, 'store_id' => 1]],
            ['sales_order_updated', ['id' => 1, 'store_id' => 1]],
            ['catalog_category_created', ['id' => 1, 'store_id' => 1]],
            ['catalog_category_updated', ['id' => 1, 'store_id' => 1]],
            ['catalog_category_deleted', ['id' => 1, 'store_id' => 1]],
            ['catalog_product_created', ['id' => 1, 'sku' => 'simple-test', 'store_id' => 1]],
            ['stock_item_updated', ['id' => 1, 'product_id' => 1, 'store_id' => 1]],
            ['catalog_product_updated', ['id' => 1, 'sku' => 'simple-test', 'store_id' => 1]],
            ['catalog_product_deleted', ['id' => 1, 'sku' => 'simple-test', 'store_id' => 1]],
        ];
    }

    /**
     * @magentoConfigFixture default/grin_integration/webhook/active 0
     */
    public function testSendNotActive()
    {
        $service = Bootstrap::getObjectManager()->get(GrinService::class);
        $this->assertTrue(null === $service->send('test_topic', []));
    }

    /**
     * @magentoConfigFixture default/grin_integration/webhook/active 1
     * @magentoConfigFixture default_store grin_integration/webhook/token integration_tests
     */
    public function testSendWithoutToken()
    {
        $service = Bootstrap::getObjectManager()->get(GrinService::class);
        $this->assertFalse('422 - "The given data was invalid."' == $service->send('test_topic', []));
    }
}
