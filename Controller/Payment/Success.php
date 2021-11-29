<?php

namespace FS\UalaBis\Controller\Payment;

use \Magento\Framework\App\Action\Context;
use \Magento\Sales\Model\Service\InvoiceService;
use \Magento\Sales\Model\Order;
use \Magento\Framework\DB\Transaction; 
use \Magento\Framework\Exception\NotFoundException;
use \FS\UalaBis\Helper\Data;

class Success extends \Magento\Framework\App\Action\Action
{
    public $context;
    protected $_invoiceService;
    protected $_order;
    protected $_transaction;
    protected $_helper;

    public function __construct(
        Context $context,
        InvoiceService $_invoiceService,
        Order $_order,
        Transaction $_transaction,
        Data $_helper
    ) {
        $this->_invoiceService = $_invoiceService;
        $this->_transaction    = $_transaction;
        $this->_order          = $_order;
        $this->context         = $context;
        $this->helper          = $_helper;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $postData = $this->getRequest()->getParams();
            if (!empty($postData) && isset($postData['code'])) {
                $this->_order->loadByIncrementId($this->helper->decodeUrl($postData['code']));
                if ($this->_order->getStatus()=='pending')
                {
                    $this->_order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING, true);
                    $this->_order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
                    $this->_order->save();
                    if ($this->_order->canInvoice()) {
                        $invoice = $this->_invoiceService->prepareInvoice($this->_order);
                        $invoice->register();
                        $invoice->save();
                        $transactionSave = $this->_transaction->addObject($invoice)->addObject($invoice->getOrder());
                        $transactionSave->save();
                        $this->_order->addStatusHistoryComment(__('Invoiced', $invoice->getId()))->setIsCustomerNotified(false)->save();
                    }
                $this->_redirect('checkout/onepage/success');
                }   else 
                throw new NotFoundException(__('Order Status is incorrect.'));
                
            } else {
                throw new NotFoundException(__('Parameter is incorrect.'));
            }
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}
