<?php

declare(strict_types=1);

namespace Grin\Module\Plugin;

use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Api\ProductAttributeRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class AttributeData
{
    /**
     * @var ProductAttributeRepositoryInterface
     */
    private $attributeRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param ProductAttributeRepositoryInterface $attributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        ProductAttributeRepositoryInterface $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param ProductExtensionInterface $subject
     */
    public function afterSetConfigurableProductOptions(ProductExtensionInterface $subject)
    {
        $productOptions = $subject->getConfigurableProductOptions();

        if (!$productOptions) {
            return;
        }

        $attributeIds = [];
        $productOptionsContainer = [];
        foreach ($productOptions as $productOption) {
            $attributeIds[] = $productOption->getAttributeId();
            $productOptionsContainer[$productOption->getAttributeId()] = $productOption;
        }

        $this->searchCriteriaBuilder->addFilter('attribute_id', implode(',', $attributeIds), 'in');

        $attributes = $this->attributeRepository->getList($this->searchCriteriaBuilder->create())->getItems();

        foreach ($attributes as $attribute) {
            if (isset($productOptionsContainer[$attribute->getAttributeId()])) {
                $extensionAttributes = $productOptionsContainer[$attribute->getAttributeId()]->getExtensionAttributes();
                $extensionAttributes->setAttributeData($attribute);
            }
        }
    }
}
