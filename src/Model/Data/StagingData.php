<?php

declare(strict_types=1);

namespace Grin\Module\Model\Data;

use Grin\Module\Api\Data\StagingDataInterface;
use Magento\Framework\DataObject;

class StagingData extends DataObject implements StagingDataInterface
{
    /**
     * @return string
     */
    public function getName()
    {
        return (string) $this->getData(self::NAME);
    }

    /**
     * @param string $name
     * @return StagingDataInterface
     */
    public function setName(string $name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return (string) $this->getData(self::DESCRIPTION);
    }

    /**
     * @param string $description
     * @return StagingDataInterface
     */
    public function setDescription(string $description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * @return string|null
     */
    public function getEndTime()
    {
        return $this->getData(self::END_TIME);
    }

    /**
     * @param string $endTime
     * @return StagingDataInterface
     */
    public function setEndTime(string $endTime)
    {
        return $this->setData(self::END_TIME, $endTime);
    }

    /**
     * @return string
     */
    public function getStartTime()
    {
        return $this->getData(self::START_TIME);
    }

    /**
     * @param string $startTime
     * @return StagingDataInterface
     */
    public function setStartTime(string $startTime)
    {
        return $this->setData(self::START_TIME, $startTime);
    }
}
