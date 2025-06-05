<?php

declare(strict_types=1);

namespace Grin\Module\Test\Unit\Plugin\Magento\SalesRule\Model\RulesApplier;

use Grin\Module\Model\SalesRuleValidator;
use Grin\Module\Plugin\Magento\SalesRule\Model\RulesApplier\ValidateByToken;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\RulesApplier;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class ValidateByTokenTest extends TestCase
{
    /**
     * @var SalesRuleValidator&MockObject
     */
    private SalesRuleValidator&MockObject $salesRuleValidator;

    /**
     * @var ValidateByToken
     */
    private ValidateByToken $validateByToken;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->salesRuleValidator = $this->createMock(SalesRuleValidator::class);
        $this->validateByToken = new ValidateByToken($this->salesRuleValidator);
    }

    /**
     * @dataProvider couponCodeProvider
     */
    public function testAroundApplyRules(mixed $couponCode): void
    {
        /** @var RulesApplier&MockObject $subject */
        $subject = $this->createMock(RulesApplier::class);
        /** @var Rule&MockObject $rule */
        $rule = $this->createMock(Rule::class);
        /** @var \Magento\Quote\Model\Quote\Item\AbstractItem&MockObject $item */
        $item = $this->createMock(\Magento\Quote\Model\Quote\Item\AbstractItem::class);
        $rules = [$rule];

        $proceed = function ($item, $rules, $skipValidation, $couponCode) {
            return ['result'];
        };

        $this->salesRuleValidator->expects($this->once())
            ->method('isValid')
            ->with($rule, $couponCode)
            ->willReturn(true);

        $result = $this->validateByToken->aroundApplyRules(
            $subject,
            $proceed,
            $item,
            $rules,
            false,
            $couponCode
        );

        $this->assertEqualsCanonicalizing(['result'], $result);
    }

    /**
     * @return array<string, array<mixed>>
     */
    public static function couponCodeProvider(): array
    {
        return [
            'string coupon code' => ['TEST123'],
            'array coupon code' => [['TEST123', 'TEST456']],
            'empty array' => [[]],
            'null' => [null],
        ];
    }

    /**
     * @dataProvider couponCodeProvider
     */
    public function testAroundApplyRulesWithInvalidRule(mixed $couponCode): void
    {
        /** @var RulesApplier&MockObject $subject */
        $subject = $this->createMock(RulesApplier::class);
        /** @var Rule&MockObject $rule */
        $rule = $this->createMock(Rule::class);
        /** @var \Magento\Quote\Model\Quote\Item\AbstractItem&MockObject $item */
        $item = $this->createMock(\Magento\Quote\Model\Quote\Item\AbstractItem::class);
        $rules = [$rule];

        $proceed = function ($item, $rules, $skipValidation, $couponCode) {
            return ['result'];
        };

        $this->salesRuleValidator->expects($this->once())
            ->method('isValid')
            ->with($rule, $couponCode)
            ->willReturn(false);

        $result = $this->validateByToken->aroundApplyRules(
            $subject,
            $proceed,
            $item,
            $rules,
            false,
            $couponCode
        );

        $this->assertEqualsCanonicalizing(['result'], $result);
    }
}
