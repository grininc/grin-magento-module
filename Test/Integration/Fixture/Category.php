<?php

declare(strict_types=1);

namespace Grin\Module\Test\Integration\Fixture;

use Magento\Catalog\Model\Category as CatalogCategory;
use Magento\TestFramework\Helper\Bootstrap;

class Category
{
    /**
     * @return void
     * @throws \Exception
     */
    public function createCategory()
    {
        /** @var CatalogCategory $category */
        $category = Bootstrap::getObjectManager()->create(CatalogCategory::class);
        $category->isObjectNew(true);
        $category->setId(100)
            ->setName('GRIN test Category 1')
            ->setParentId(2)
            ->setPath('1/2/100')
            ->setLevel(2)
            ->setAvailableSortBy('name')
            ->setDefaultSortBy('name')
            ->setIsActive(true)
            ->setPosition(1)
            ->save();
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function updateCategory()
    {
        /** @var CatalogCategory $category */
        $category = Bootstrap::getObjectManager()->create(CatalogCategory::class);
        $category->load(100);
        $category->setName('Updated Category 1');
        $category->save();
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function deleteCategory()
    {
        /** @var CatalogCategory $category */
        $category = Bootstrap::getObjectManager()->create(CatalogCategory::class);
        $category->load(100)->delete();
    }
}
