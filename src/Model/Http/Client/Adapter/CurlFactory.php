<?php

declare(strict_types=1);

namespace Grin\Module\Model\Http\Client\Adapter;

use Magento\Framework\ObjectManagerInterface;

class CurlFactory
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
     * @return \Laminas\Http\Client\Adapter\Curl|\Zend_Http_Client_Adapter_Curl
     */
    public function create()
    {
        if (class_exists(\Laminas\Http\Client\Adapter\Curl::class, false)) {
            return $this->objectManager->create(\Laminas\Http\Client\Adapter\Curl::class);
        }

        return $this->objectManager->create(\Zend_Http_Client_Adapter_Curl::class);
    }
}
