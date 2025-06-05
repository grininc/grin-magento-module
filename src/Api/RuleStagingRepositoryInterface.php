<?php

declare(strict_types=1);

namespace Grin\Module\Api;

interface RuleStagingRepositoryInterface
{
    /**
     * Save sales rule staging
     *
     * @param \Grin\Module\Api\Data\RuleStagingInterface $ruleStaging
     * @return \Magento\SalesRule\Api\Data\RuleInterface
     * @throws \Magento\Framework\Exception\InputException If there is a problem with the input
     * @throws \Magento\Framework\Exception\NoSuchEntityException If a rule ID is sent but the rule does not exist
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Grin\Module\Api\Data\RuleStagingInterface $ruleStaging);
}
