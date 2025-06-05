<?php

declare(strict_types=1);

namespace Grin\Module\Api\Data;

interface StagingDataInterface
{
    public const NAME = 'name';
    public const DESCRIPTION = 'description';
    public const START_TIME = 'start_time';
    public const END_TIME = 'end_time';

    /**
     * @return string
     */
    public function getName();

    /**
     * @param string $name
     * @return StagingDataInterface
     */
    public function setName(string $name);

    /**
     * @return string|null
     */
    public function getDescription();

    /**
     * @param string $description
     * @return StagingDataInterface
     */
    public function setDescription(string $description);

    /**
     * @return string|null
     */
    public function getEndTime();

    /**
     * @param string $endTime
     * @return StagingDataInterface
     */
    public function setEndTime(string $endTime);

    /**
     * @return string
     */
    public function getStartTime();

    /**
     * @param string $startTime
     * @return StagingDataInterface
     */
    public function setStartTime(string $startTime);
}
