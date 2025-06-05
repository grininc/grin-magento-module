<?php

declare(strict_types=1);

namespace Grin\Module\Test\Integration;

use Grin\Module\Test\Integration\Fixture\Category as CategoryFixture;
use Grin\Module\Test\Integration\Model\MysqlQueueMessageManager;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * @magentoAppArea adminhtml
 */
class CategoryPublisherTest extends TestCase
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
        $this->json = Bootstrap::getObjectManager()->create(Json::class);
        $this->messageManager = Bootstrap::getObjectManager()->create(MysqlQueueMessageManager::class);
    }

    /**
     * @magentoConfigFixture default/grin_integration/webhook/active 1
     * @magentoConfigFixture default/grin_integration/webhook/token integration_tests
     * @magentoDataFixture createCategoryFixture
     * @return void
     */
    public function testCreateCategory()
    {
        $message =  $this->messageManager->getLastMessage();
        $this->assertJson($message->getBody());

        $body = $this->json->unserialize($message->getBody());
        $this->assertJson($body['serialized_data']);
        $this->assertTrue($body['topic'] === 'catalog_category_created');
        $this->assertTrue($this->json->unserialize($body['serialized_data'])['id'] === 100);
    }

    /**
     * @magentoConfigFixture default/grin_integration/webhook/active 1
     * @magentoConfigFixture default/grin_integration/webhook/token integration_tests
     * @magentoDataFixture createCategoryFixture
     * @magentoDataFixture updateCategoryFixture
     * @return void
     */
    public function testUpdateCategory()
    {
        $message =  $this->messageManager->getLastMessage();
        $this->assertJson($message->getBody());

        $body = $this->json->unserialize($message->getBody());
        $this->assertJson($body['serialized_data']);
        $this->assertTrue($body['topic'] === 'catalog_category_updated');
        $this->assertTrue($this->json->unserialize($body['serialized_data'])['id'] === 100);
    }

    /**
     * @magentoConfigFixture default/grin_integration/webhook/active 1
     * @magentoConfigFixture default/grin_integration/webhook/token integration_tests
     * @magentoDataFixture createCategoryFixture
     * @magentoDataFixture deleteCategoryFixture
     * @return void
     */
    public function testDeleteCategory()
    {
        $message =  $this->messageManager->getLastMessage();
        $this->assertJson($message->getBody());

        $body = $this->json->unserialize($message->getBody());
        $this->assertJson($body['serialized_data']);
        $this->assertTrue($body['topic'] === 'catalog_category_deleted');
        $this->assertTrue($this->json->unserialize($body['serialized_data'])['id'] === 100);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public static function createCategoryFixture()
    {
        /** @var CategoryFixture $categoryFixture */
        $categoryFixture = Bootstrap::getObjectManager()->get(CategoryFixture::class);
        $categoryFixture->createCategory();
    }

    /**
     * @return void
     * @throws \Exception
     */
    public static function updateCategoryFixture()
    {
        /** @var CategoryFixture $categoryFixture */
        $categoryFixture = Bootstrap::getObjectManager()->get(CategoryFixture::class);
        $categoryFixture->updateCategory();
    }

    /**
     * @return void
     * @throws \Exception
     */
    public static function deleteCategoryFixture()
    {
        /** @var CategoryFixture $categoryFixture */
        $categoryFixture = Bootstrap::getObjectManager()->get(CategoryFixture::class);
        $categoryFixture->deleteCategory();
    }
}
