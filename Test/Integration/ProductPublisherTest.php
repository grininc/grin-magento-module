<?php

declare(strict_types=1);

namespace Grin\Module\Test\Integration;

use Grin\Module\Test\Integration\Fixture\Product as ProductFixture;
use Grin\Module\Test\Integration\Model\MysqlQueueMessageManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppArea adminhtml
 */
class ProductPublisherTest extends TestCase
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
     * @magentoDataFixture createProductFixture
     * @return void
     */
    public function testCreateProduct()
    {
        $messages = $this->messageManager->getLastMessages(2);

        $productMessage = current($messages);

        $this->assertJson($productMessage->getBody());
        $body = $this->json->unserialize($productMessage->getBody());
        $this->assertJson($body['serialized_data']);
        $this->assertTrue($body['topic'] === 'catalog_product_created');
        $this->assertTrue($this->json->unserialize($body['serialized_data'])['id'] === 1000);
        $this->assertTrue($this->json->unserialize($body['serialized_data'])['sku'] === 'simple-test');

        $stockMessage = end($messages);
        $this->assertJson($stockMessage->getBody());
        $body = $this->json->unserialize($stockMessage->getBody());
        $this->assertJson($body['serialized_data']);
        $this->assertTrue($body['topic'] === 'stock_item_updated');
        $this->assertTrue($this->json->unserialize($body['serialized_data'])['id'] === 1);
        $this->assertTrue($this->json->unserialize($body['serialized_data'])['product_id'] === 1000);
    }

    /**
     * @magentoConfigFixture default/grin_integration/webhook/active 1
     * @magentoConfigFixture default/grin_integration/webhook/token integration_tests
     * @magentoDataFixture createProductFixture
     * @magentoDataFixture updateProductFixture
     * @return void
     */
    public function testUpdateProduct()
    {
        $productMessage = $this->messageManager->getLastMessage();

        $this->assertJson($productMessage->getBody());
        $body = $this->json->unserialize($productMessage->getBody());
        $this->assertJson($body['serialized_data']);
        $this->assertTrue($body['topic'] === 'catalog_product_updated');
        $this->assertTrue($this->json->unserialize($body['serialized_data'])['id'] === 1000);
        $this->assertTrue($this->json->unserialize($body['serialized_data'])['sku'] === 'simple-test');
    }

    /**
     * @magentoConfigFixture default/grin_integration/webhook/active 1
     * @magentoConfigFixture default/grin_integration/webhook/token integration_tests
     * @magentoDataFixture createProductFixture
     * @magentoDataFixture deleteProductFixture
     * @return void
     */
    public function testDeleteProduct()
    {
        $message = $this->messageManager->getLastMessage();
        $this->assertJson($message->getBody());

        $body = $this->json->unserialize($message->getBody());
        $this->assertJson($body['serialized_data']);
        $this->assertTrue($this->json->unserialize($body['serialized_data'])['id'] === 1000);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public static function createProductFixture()
    {
        /** @var ProductFixture $productFixture */
        $productFixture = Bootstrap::getObjectManager()->get(ProductFixture::class);
        $productFixture->createProduct();
    }

    /**
     * @return void
     * @throws \Exception
     */
    public static function updateProductFixture()
    {
        /** @var ProductFixture $productFixture */
        $productFixture = Bootstrap::getObjectManager()->get(ProductFixture::class);
        $productFixture->updateProduct();
    }

    /**
     * @return void
     * @throws \Exception
     */
    public static function deleteProductFixture()
    {
        /** @var ProductFixture $productFixture */
        $productFixture = Bootstrap::getObjectManager()->get(ProductFixture::class);
        $productFixture->deleteProduct();
    }
}
