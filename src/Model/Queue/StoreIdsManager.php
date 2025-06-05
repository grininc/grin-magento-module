<?php

declare(strict_types=1);

namespace Grin\Module\Model\Queue;

use Grin\Module\Model\SystemConfig;

class StoreIdsManager
{
    /**
     * @var SystemConfig
     */
    private $systemConfig;

    /**
     * @param SystemConfig $systemConfig
     */
    public function __construct(SystemConfig $systemConfig)
    {
        $this->systemConfig = $systemConfig;
    }

    /**
     * @param array $allStoreIds
     * @return array
     */
    public function filterStoreIds(array $allStoreIds): array
    {
        $storeIds = [];
        $tokenPool = [];

        foreach ($allStoreIds as $storeId) {
            $token = $this->systemConfig->getWebhookToken((int) $storeId);

            if (!in_array($token, $tokenPool)) {
                $storeIds[] = $storeId;
                $tokenPool[] = $token;
            }
        }

        return $storeIds;
    }
}
