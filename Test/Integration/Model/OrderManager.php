<?php

declare(strict_types=1);

namespace Grin\Module\Test\Integration\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderManager
{
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @param OrderRepositoryInterface $orderRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrderBuilder $sortOrderBuilder
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrderBuilder $sortOrderBuilder
    ) {
        $this->orderRepository = $orderRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->sortOrderBuilder = $sortOrderBuilder;
    }

    /**
     * @return OrderInterface|false
     */
    public function getLastOrder()
    {
        $creationReverseOrder = $this->sortOrderBuilder->setField('entity_id')
            ->setDescendingDirection()
            ->create();

        $this->searchCriteriaBuilder->addSortOrder($creationReverseOrder);
        $this->searchCriteriaBuilder->setPageSize(1);

        $searchCriteria = $this->searchCriteriaBuilder->create();

        $items = $this->orderRepository->getList($searchCriteria)->getItems();

        return current($items);
    }
}
