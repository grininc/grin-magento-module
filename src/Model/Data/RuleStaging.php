<?php

declare(strict_types=1);

namespace Grin\Module\Model\Data;

use Grin\Module\Api\Data\RuleStagingInterface;
use Grin\Module\Api\Data\StagingDataInterface;
use Magento\SalesRule\Api\Data\RuleInterface;

class RuleStaging implements RuleStagingInterface
{
    /**
     * @var int
     */
    private $entityId;

    /**
     * @var StagingDataInterface
     */
    private $stagingData;

    /**
     * @var RuleInterface
     */
    private $entityData;

    /**
     * @return int|null
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @param int $entityId
     * @return $this|RuleStaging
     */
    public function setEntityId(int $entityId)
    {
        $this->entityId = $entityId;

        return $this;
    }

    /**
     * @return StagingDataInterface
     */
    public function getStagingData()
    {
        return $this->stagingData;
    }

    /**
     * @param StagingDataInterface $stagingData
     * @return $this|RuleStaging
     */
    public function setStagingData(StagingDataInterface $stagingData)
    {
        $this->stagingData = $stagingData;

        return $this;
    }

    /**
     * @return RuleInterface
     */
    public function getEntityData()
    {
        return $this->entityData;
    }

    /**
     * @param RuleInterface $entityData
     * @return $this|RuleStaging
     */
    public function setEntityData(RuleInterface $entityData)
    {
        $this->entityData = $entityData;

        return $this;
    }
}
