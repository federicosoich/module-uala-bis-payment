<?php

namespace FS\UalaBis\Controller\Payment;

use \Magento\Framework\App\Action\Context;
use \Magento\Sales\Model\Order;
use \Magento\Framework\Exception\NotFoundException;
use \FS\UalaBis\Helper\Data;

class Cancel extends \Magento\Framework\App\Action\Action
{
    public $context;
    protected $order;
    protected $helper;

    public function __construct(
        Context $context,
        Order $order,
        Data $helper
    ) {
        $this->context = $context;
        $this->order = $order;
        $this->helper = $helper;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $postData = $this->getRequest()->getParams();
            if (!empty($postData) && isset($postData['code'])) {
                $this->order->loadByIncrementId($this->helper->decodeUrl($postData['code']));
                if ($this->_order->getStatus()=='pending')
                    {
                        $this->_order->setState(\Magento\Sales\Model\Order::STATE_CANCELED, true);
                        $this->_order->setStatus(\Magento\Sales\Model\Order::STATE_CANCELED);
                        foreach ($this->_order->getAllItems() as $item) { // Cancel order items
                            $item->cancel();
                        }
                        $this->_order->save();
                        $this->_redirect('checkout/onepage/failure');
                    } else 
                    throw new NotFoundException(__('Order Status is incorrect.'));
            } else {
                throw new NotFoundException(__('Parameter is incorrect.'));
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
