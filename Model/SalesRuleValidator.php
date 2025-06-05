<?php

declare(strict_types=1);

namespace Grin\Module\Model;

use Magento\Framework\HTTP\PhpEnvironment\Request;
use Magento\SalesRule\Model\Rule;

class SalesRuleValidator
{
    private const TOKEN_HEADER = 'Validation-Grin-Token';

    /**
     * @var Request
     */
    private $request;

    /**
     * @var SystemConfig
     */
    private $systemConfig;

    /**
     * @param Request $request
     * @param SystemConfig $systemConfig
     */
    public function __construct(Request $request, SystemConfig $systemConfig)
    {
        $this->request = $request;
        $this->systemConfig = $systemConfig;
    }

    /**
     * @param  Rule  $rule
     * @param  string|array $couponCode
     * @return bool
     */
    public function isValid(Rule $rule, $couponCode): bool
    {
        $code = $rule->getPrimaryCoupon()->getCode();
        $couponCodes = is_array($couponCode) ? $couponCode : [$couponCode];

        if ($code !== null && $rule->getData('is_grin_only') && in_array($code, $couponCodes)) {
            return $this->request->getHeader(self::TOKEN_HEADER) === $this->systemConfig->getSalesRuleToken();
        }

        return true;
    }
}
