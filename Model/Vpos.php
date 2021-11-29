<?php

namespace FS\UalaBis\Model;

class Vpos extends \Magento\Payment\Model\Method\AbstractMethod
{
    protected $_code = 'ualabis';
    protected $_isOffline = true;
    protected $_isInitializeNeeded = true;
}