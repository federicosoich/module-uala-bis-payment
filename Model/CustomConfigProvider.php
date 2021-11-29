<?php

namespace FS\UalaBis\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use \FS\UalaBis\Helper\Data;

class CustomConfigProvider implements ConfigProviderInterface
{

    protected $helper;

    public function __construct(
        Data $helper
    ) {
        $this->helper = $helper;
    }

    public function getConfig()
    {
        $config = [
            'payment' => [
                'ualabis' => [
                    'message' => $this->helper->getMessage()
                ]
            ]
        ];
        return $config;
    }
}