<?php

declare(strict_types=1);

namespace Grin\Module\Model;

use Grin\Module\Api\RuleStagingRepositoryInterface;
use Grin\Module\Api\Data\RuleStagingInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\SalesRule\Api\Data\RuleInterface;
use Magento\Framework\ObjectManagerInterface;

class RuleStagingRepository implements RuleStagingRepositoryInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(
        ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * @param RuleStagingInterface $ruleStaging
     * @return RuleInterface
     * @throws LocalizedException
     */
    public function save(RuleStagingInterface $ruleStaging)
    {
        $stagingData = $ruleStaging->getStagingData()->getData();
        $stagingData['mode'] = 'save';

        $this->getStagingUpdateSaver()->execute(
            [
                'entityId' => $ruleStaging->getEntityId(),
                'stagingData' => $stagingData,
                'entityData' => $ruleStaging->getEntityData()->__toArray(),
            ]
        );

        return $ruleStaging->getEntityData();
    }

    /**
     * @return \Magento\Staging\Model\Entity\Update\Save|mixed
     * @throws LocalizedException
     */
    private function getStagingUpdateSaver()
    {
        try {
            return $this->objectManager->create(
                \Magento\Staging\Model\Entity\Update\Save::class,
                ['entityName' => \Magento\SalesRule\Api\Data\RuleInterface::class]
            );
        } catch (\Exception $e) {
            throw new LocalizedException(__('This functionality works only for commerce version of Magento'));
        }
    }
}
