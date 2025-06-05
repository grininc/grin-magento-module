<?php

declare(strict_types=1);

namespace Grin\Module\Plugin\Magento\SalesRule\Model\RulesApplier;

use Grin\Module\Model\SalesRuleValidator;
use Magento\SalesRule\Model\RulesApplier;

class ValidateByToken
{
    /**
     * @var SalesRuleValidator
     */
    private $salesRuleValidator;

    /**
     * @param SalesRuleValidator $salesRuleValidator
     */
    public function __construct(SalesRuleValidator $salesRuleValidator)
    {
        $this->salesRuleValidator = $salesRuleValidator;
    }

    /**
     * @param RulesApplier $subject
     * @param callable $proceed
     * @param $item
     * @param $rules
     * @param $skipValidation
     * @param $couponCode
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundApplyRules(
        RulesApplier $subject,
        callable $proceed,
        $item,
        $rules,
        $skipValidation,
        $couponCode
    ): array {
        foreach ($rules as $rule) {
            if (!$this->salesRuleValidator->isValid($rule, $couponCode)) {
                return $proceed($item, [], $skipValidation, $couponCode);
            }
        }

        return $proceed($item, $rules, $skipValidation, $couponCode);
    }
}
