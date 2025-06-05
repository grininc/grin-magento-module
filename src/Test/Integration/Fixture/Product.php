<?php

declare(strict_types=1);

namespace Grin\Module\Test\Integration\Fixture;

use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Type;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Eav\Model\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\TestFramework\Helper\Bootstrap;

class Product
{
    /**
     * @return Product
     * @throws LocalizedException
     */
    public function createProduct()
    {
        $objectManager = Bootstrap::getObjectManager();
        $defaultAttributeSet = $objectManager->get(Config::class)
            ->getEntityType('catalog_product')
            ->getDefaultAttributeSetId();
        /** @var Product $product */
        $product = $objectManager->create(CatalogProduct::class);
        $product->isObjectNew(true);
        $product->setId(1000)
            ->setTypeId(Type::TYPE_SIMPLE)
            ->setAttributeSetId($defaultAttributeSet)
            ->setStoreId(1)
            ->setWebsiteIds([1])
            ->setName('Simple Product')
            ->setSku('simple-test')
            ->setPrice(10)
            ->setWeight(18)
            ->setStockData(['use_config_manage_stock' => 0])
            ->setVisibility(Visibility::VISIBILITY_BOTH)
            ->setStatus(Status::STATUS_ENABLED)
            ->save();

        return $product;
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function updateProduct()
    {
        /** @var CatalogProduct $product */
        $product = Bootstrap::getObjectManager()->create(CatalogProduct::class);
        $product->load(1000);
        $product->setName('Updated Simple Product');
        $product->save();
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function deleteProduct()
    {
        Bootstrap::getObjectManager()->get(Registry::class)->register('isSecureArea', true, true);
        /** @var Product $product */
        $product = Bootstrap::getObjectManager()->create(CatalogProduct::class);
        $product->load(1000)->delete();
    }
}
