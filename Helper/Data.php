<?php

namespace FS\UalaBis\Helper;

use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Sales\Api\Data\OrderInterface;
use \Magento\Store\Model\StoreManagerInterface;
use \Magento\Checkout\Model\Type\Onepage;
use \Magento\Framework\HTTP\Client\Curl;
use \Magento\Framework\Url\DecoderInterface;
use \Magento\Framework\Url\EncoderInterface;



class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const ENCRYPT = 1;
    const DECRYPT = 2;
    protected $urlEncoder;
    protected $urlDecoder;
    public $scopeConfig;
    public $order;
    public $store;
    protected $checkout;
    protected $curl;
    

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        OrderInterface $order,
        StoreManagerInterface $store,
        Onepage $checkout,
        Curl $curl,
        EncoderInterface $urlEncoder,
        DecoderInterface $urlDecoder
    ) {
        $this->order             = $order;
        $this->store             = $store;
        $this->scopeConfig       = $scopeConfig;
        $this->checkout          = $checkout;
        $this->curl              = $curl;
        $this->urlEncode         = $urlEncoder;
        $this->urlDecode         = $urlDecoder;
    }


    public function generateCheckoutUrl()
	{
        try {
            $userName = $this->scopeConfig->getValue('payment/ualabis/user_name',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $clientId = $this->scopeConfig->getValue('payment/ualabis/client_id',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $clientSecretId = $this->scopeConfig->getValue('payment/ualabis/client_secret_id',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            $grantType = $this->scopeConfig->getValue('payment/ualabis/granttype',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            //get token
            $url = $this->getTokenUrl();
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $headers = array("Content-Type: application/json","Accept: application/json");
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            $data = '{"user_name" : "'.$userName.'","client_id" : "'.$clientId.'","client_secret_id" : "'.$clientSecretId.'","grant_type" : "'.$grantType.'"}';
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            $resp = curl_exec($curl);
            curl_close($curl);
            $arre=json_decode($resp, true);
            //generate order
            $url = $this->getCheckoutUrl();
            $curl = curl_init($url);
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            $checkout = $this->checkout->getCheckout();
            $order = $this->order->loadByIncrementId($checkout->getLastRealOrderId());
            $headers = array("Authorization: Bearer ".$arre['access_token'],"Content-Type: application/json","Accept: application/json");
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            $data = '{
            "amount": "'.round((float)$order->getGrandTotal(),2).'",
            "description": "Order # '.$order->getIncrementId().'",
            "userName": "'.$userName.'",
            "callback_fail": "'.$this->store->getStore()->getBaseUrl().'ualabis/payment/failure/code/'.$this->encodeUrl($order->getIncrementId()).'",
            "callback_success": "'.$this->store->getStore()->getBaseUrl().'ualabis/payment/success/code/'.$this->encodeUrl($order->getIncrementId()).'"
            }';
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            $resp = curl_exec($curl);
            $arre=json_decode($resp, true);
            curl_close($curl);    
            return $arre['links']['checkoutLink'];
        }
        catch(Exception $e) {
            echo $e->getMessage();
        }
	}

    public function getMessage()
    {
        return $this->scopeConfig->getValue(
            'payment/ualabis/message',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getTokenUrl()
    {
        return $this->scopeConfig->getValue(
            'payment/ualabis/token_url',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getCheckoutUrl()
    {
        return $this->scopeConfig->getValue(
            'payment/ualabis/checkout_url',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function encryptDecrypt($action, $string)
    {
        $output = false;
 
        $encrypt_method = "AES-128-ECB";
        $secret_key =  $this->scopeConfig->getValue('payment/ualabis/user_name',\Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $key = hash('sha256', $secret_key);
        if ($action == self::ENCRYPT) {
            $output = openssl_encrypt($string, $encrypt_method, $key);
        } elseif ($action == self::DECRYPT) {
            $output = openssl_decrypt($string, $encrypt_method, $key);
        }
 
        return $output;
    }

    public function encodeUrl($url)
    {
        return $this->urlEncode->encode($url);
    }
 
    public function decodeUrl($url)
    {
        return $this->urlDecode->decode($url);
    }
}
