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
    protected $invoiceService;
    protected $order;
    protected $transaction;
    protected $helper;

    public function __construct(
        Context $context,
        InvoiceService $invoiceService,
        Order $order,
        Transaction $transaction,
        Data $helper
    ) {
        $this->invoiceService = $invoiceService;
        $this->transaction    = $transaction;
        $this->order          = $order;
        $this->context        = $context;
        $this->helper         = $helper;
        parent::__construct($context);
    }

    public function execute()
    {
        try {
            $postData = $this->getRequest()->getParams();
            if (!empty($postData) && isset($postData['code'])) {
                $this->order->loadByIncrementId($this->helper->decodeUrl($postData['code']));
                if ($this->order->getStatus()=='pending')
                {
                    $this->order->setState(\Magento\Sales\Model\Order::STATE_PROCESSING, true);
                    $this->order->setStatus(\Magento\Sales\Model\Order::STATE_PROCESSING);
                    $this->order->save();
                    if ($this->order->canInvoice()) {
                        $invoice = $this->invoiceService->prepareInvoice($this->order);
                        $invoice->register();
                        $invoice->save();
                        $transactionSave = $this->transaction->addObject($invoice)->addObject($invoice->getOrder());
                        $transactionSave->save();
                        $this->order->addStatusHistoryComment(__('Invoiced', $invoice->getId()))->setIsCustomerNotified(false)->save();
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
