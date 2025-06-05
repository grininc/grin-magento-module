<?php

declare(strict_types=1);

namespace Grin\Module\Controller\Adminhtml\Queue;

use Magento\Backend\App\Action;
use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class Messages extends Action implements HttpGetActionInterface
{
    /**
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Magento_Backend::system');
        $resultPage->getConfig()->getTitle()->prepend((__('Grin Module Queue Messages')));

        return $resultPage;
    }
}
