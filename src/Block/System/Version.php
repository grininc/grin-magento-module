<?php

declare(strict_types=1);

namespace Grin\Module\Block\System;

use Magento\Backend\Block\AbstractBlock;
use Magento\Backend\Block\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Module\PackageInfo;

class Version extends AbstractBlock implements RendererInterface
{
    private const EXTENSION = 'Grin_Module';

    /**
     * @var PackageInfo
     */
    private $packageInfo;

    /**
     * @param Context $context
     * @param PackageInfo $packageInfo
     * @param array $data
     */
    public function __construct(Context $context, PackageInfo $packageInfo, array $data = [])
    {
        parent::__construct($context, $data);

        $this->packageInfo = $packageInfo;
    }

    /**
     * @param AbstractElement $element
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function render(AbstractElement $element): string
    {
        $version = $this->packageInfo->getVersion(self::EXTENSION);
        return '<h4 style="text-align: right;">Version: ' . $version . '</h4>';
    }
}
