<?php

declare(strict_types=1);

namespace Grin\Module\Test\Unit\Model;

use Grin\Module\Model\SalesRuleValidator;
use Grin\Module\Model\SystemConfig;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\Coupon;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class SalesRuleValidatorTest extends TestCase
{
    /**
     * @var Request&MockObject
     */
    private Request&MockObject $request;

    /**
     * @var SystemConfig&MockObject
     */
    private SystemConfig&MockObject $systemConfig;

    /**
     * @var SalesRuleValidator
     */
    private SalesRuleValidator $salesRuleValidator;

    /**
     * @return void
     */
    protected function setUp(): void
    {
        $this->request = $this->createMock(Request::class);
        $this->systemConfig = $this->createMock(SystemConfig::class);
        $this->salesRuleValidator = new SalesRuleValidator($this->request, $this->systemConfig);
    }

    /**
     * @dataProvider couponCodeProvider
     */
    public function testIsValidWithNonGrinRule(mixed $couponCode): void
    {
        /** @var Rule&MockObject $rule */
        $rule = $this->createMock(Rule::class);
        /** @var Coupon&MockObject $coupon */
        $coupon = $this->createMock(Coupon::class);

        $rule->expects($this->once())
            ->method('getPrimaryCoupon')
            ->willReturn($coupon);

        $coupon->expects($this->once())
            ->method('getCode')
            ->willReturn('TEST123');

        $rule->expects($this->once())
            ->method('getData')
            ->with('is_grin_only')
            ->willReturn(0);

        $this->assertTrue($this->salesRuleValidator->isValid($rule, $couponCode));
    }

    /**
     * @dataProvider couponCodeProvider
     */
    public function testIsValidWithGrinRuleAndValidToken(mixed $couponCode): void
    {
        /** @var Rule&MockObject $rule */
        $rule = $this->createMock(Rule::class);
        /** @var Coupon&MockObject $coupon */
        $coupon = $this->createMock(Coupon::class);

        $rule->expects($this->once())
            ->method('getPrimaryCoupon')
            ->willReturn($coupon);

        $coupon->expects($this->once())
            ->method('getCode')
            ->willReturn('TEST123');

        $rule->expects($this->once())
            ->method('getData')
            ->with('is_grin_only')
            ->willReturn(1);

        $this->request->expects($this->once())
            ->method('getHeader')
            ->with('Validation-Grin-Token')
            ->willReturn('valid-token');

        $this->systemConfig->expects($this->once())
            ->method('getSalesRuleToken')
            ->willReturn('valid-token');

        $this->assertTrue($this->salesRuleValidator->isValid($rule, $couponCode));
    }

    /**
     * @dataProvider couponCodeProvider
     */
    public function testIsValidWithGrinRuleAndInvalidToken(mixed $couponCode): void
    {
        /** @var Rule&MockObject $rule */
        $rule = $this->createMock(Rule::class);
        /** @var Coupon&MockObject $coupon */
        $coupon = $this->createMock(Coupon::class);

        $rule->expects($this->once())
            ->method('getPrimaryCoupon')
            ->willReturn($coupon);

        $coupon->expects($this->once())
            ->method('getCode')
            ->willReturn('TEST123');

        $rule->expects($this->once())
            ->method('getData')
            ->with('is_grin_only')
            ->willReturn(1);

        $this->request->expects($this->once())
            ->method('getHeader')
            ->with('Validation-Grin-Token')
            ->willReturn('invalid-token');

        $this->systemConfig->expects($this->once())
            ->method('getSalesRuleToken')
            ->willReturn('valid-token');

        $this->assertFalse($this->salesRuleValidator->isValid($rule, $couponCode));
    }

    /**
     * @dataProvider emptyCouponCodeProvider
     */
    public function testIsValidWithEmptyCouponCode(mixed $couponCode): void
    {
        /** @var Rule&MockObject $rule */
        $rule = $this->createMock(Rule::class);
        /** @var Coupon&MockObject $coupon */
        $coupon = $this->createMock(Coupon::class);

        $rule->expects($this->once())
            ->method('getPrimaryCoupon')
            ->willReturn($coupon);

        $coupon->expects($this->once())
            ->method('getCode')
            ->willReturn('TEST123');

        $rule->expects($this->once())
            ->method('getData')
            ->with('is_grin_only')
            ->willReturn(1);

        $this->assertTrue($this->salesRuleValidator->isValid($rule, $couponCode));
    }

    /**
     * @return array<string, array<mixed>>
     */
    public static function couponCodeProvider(): array
    {
        return [
            'string coupon code' => ['TEST123'],
            'array coupon code' => [['TEST123', 'TEST456']],
        ];
    }

    /**
     * @return array<string, array<mixed>>
     */
    public static function emptyCouponCodeProvider(): array
    {
        return [
            'empty array' => [[]],
            'null' => [null],
        ];
    }
} 