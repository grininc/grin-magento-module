<?php

declare(strict_types=1);

namespace Grin\Module\Model\Http;

use Magento\Framework\ObjectManagerInterface;

class UriFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @return \Laminas\Uri\Uri|bool
     */
    public function create()
    {
        if (class_exists(\Laminas\Uri\Uri::class, false)) {
            return $this->objectManager->create(\Laminas\Uri\Uri::class);
        }

        return false;
    }
}
