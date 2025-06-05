<?php

declare(strict_types=1);

namespace Grin\Module\Api;

/**
 * @api
 */
interface StockItemsInterface
{
    /**
     * Return StockItems by product skus
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\CatalogInventory\Api\Data\StockItemCollectionInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
