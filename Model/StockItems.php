<?php

declare(strict_types=1);

namespace Grin\Module\Model;

use Grin\Module\Api\StockItemsInterface;
use Magento\CatalogInventory\Api\Data\StockItemCollectionInterface;
use Magento\CatalogInventory\Api\StockItemCriteriaInterfaceFactory;
use Magento\CatalogInventory\Model\ResourceModel\Stock\Item\StockItemCriteria;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\Framework\Api\Filter;
use Magento\Framework\Api\Search\FilterGroup;
use Magento\Framework\Validation\ValidationException;
use Magento\Framework\Api\SearchCriteriaInterface;

class StockItems implements StockItemsInterface
{
    /**
     * @var StockItemCriteriaInterfaceFactory
     */
    private $stockItemCriteriaFactory;

    /**
     * @var StockItemRepository
     */
    private $repository;

    /**
     * @param StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory
     * @param StockItemRepository $repository
     */
    public function __construct(
        StockItemCriteriaInterfaceFactory $stockItemCriteriaFactory,
        StockItemRepository $repository
    ) {
        $this->repository = $repository;
        $this->stockItemCriteriaFactory = $stockItemCriteriaFactory;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return StockItemCollectionInterface
     * @throws ValidationException
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        return $this->repository->getList($this->transformCriteria($searchCriteria));
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return StockItemCriteria
     * @throws ValidationException
     */
    protected function transformCriteria(SearchCriteriaInterface $searchCriteria)
    {
        $criteria = $this->stockItemCriteriaFactory->create();

        $products = [];
        /** @var FilterGroup $filterGroup */
        foreach ($searchCriteria->getFilterGroups() as $filterGroup) {
            /** @var Filter $filter */
            foreach ($filterGroup->getFilters() as $filter) {
                if ($filter->getField() !== 'product_id') {
                    throw new ValidationException(__('Only filtering by productId is supported'));
                }
                if (!in_array($filter->getConditionType(), ['eq', 'in'], true)) {
                    throw new ValidationException(__('Only "eq" and "in" condition types are supported'));
                }
                if ($filter->getConditionType() === 'eq') {
                    $products[] = $filter->getValue();

                    break 2;
                }
                if ($filter->getConditionType() === 'in') {
                    // phpcs:ignore Magento2.Performance.ForeachArrayMerge
                    $products = array_merge(explode(',', $filter->getValue()), $products);

                    break 2;
                }
            }
        }

        if (!$products) {
            throw new ValidationException(__('Please define at least one product filter'));
        }

        $criteria->setProductsFilter($products);
        $pageSize = $searchCriteria->getPageSize() ?: 100;
        $criteria->setLimit($pageSize * ($searchCriteria->getCurrentPage() - 1), $pageSize);

        return $criteria;
    }
}
