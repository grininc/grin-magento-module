<?php

declare(strict_types=1);

namespace Grin\Module\Ui\DataProvider\MessageQueue;

use Grin\Module\Api\PublisherInterface;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider;

class ListingDataProvider extends DataProvider
{
    /**
     * @return SearchResultInterface
     */
    public function getSearchResult()
    {
        $result = parent::getSearchResult();

        if ($result->isLoaded()) {
            return $result;
        }

        $result->getSelect()
            ->joinLeft(
                ['queue' => $result->getTable('queue')],
                'queue.id = main_table.queue_id',
                []
            )
            ->joinLeft(
                ['response' => $result->getTable('grin_queue_message_status')],
                'response.id = main_table.id',
                ['response']
            )
            ->where('queue.name = ?', PublisherInterface::TOPIC);

        return $result;
    }
}
