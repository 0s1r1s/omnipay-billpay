<?php

namespace Omnipay\BillPay\Message;

use Omnipay\Common\Message\ResponseInterface;
use SimpleXMLElement;

/**
 * BillPay Abstract Request
 *
 * @link      https://techdocs.billpay.de/en/For_developers/Introduction.html
 * @package   Omnipay\BillPay
 * @author    Andreas Lange <andreas.lange@quillo.de>
 * @copyright 2016, Quillo GmbH
 * @license   MIT
 */
abstract class AbstractRequest extends \Omnipay\Common\Message\AbstractRequest
{
    const API_VERSION = '1.5.10';

    protected $liveEndpoint = 'https://api.billpay.de/xml';
    protected $testEndpoint = 'https://test-api.billpay.de/xml/offline';

    /**
     * @return int Merchant ID
     */
    public function getMerchantId()
    {
        return $this->getParameter('merchantId');
    }

    /**
     * @return int Portal ID
     */
    public function getPortalId()
    {
        return $this->getParameter('portalId');
    }

    /**
     * @return string MD5 hash of the security key generated for this portal. (generated and delivered by BillPay)
     */
    public function getSecurityKey()
    {
        return $this->getParameter('securityKey');
    }

    /**
     * Send the request with specified data
     *
     * @param  SimpleXMLElement $data The data to send
     *
     * @return ResponseInterface
     */
    public function sendData($data)
    {
        $httpResponse = $this->httpClient->post($this->getEndpoint(), null, $data->asXML())->send();

        return $this->createResponse($httpResponse->xml());
    }

    /**
     * @param int $value Merchant ID
     *
     * @return AbstractRequest
     */
    public function setMerchantId($value)
    {
        return $this->setParameter('merchantId', $value);
    }

    /**
     * @param int $value Portal ID
     *
     * @return AbstractRequest
     */
    public function setPortalId($value)
    {
        return $this->setParameter('portalId', $value);
    }

    /**
     * @param string $value MD5 hash of the security key generated for this portal. (generated and delivered by BillPay)
     *
     * @return AbstractRequest
     */
    public function setSecurityKey($value)
    {
        return $this->setParameter('securityKey', $value);
    }

    /**
     * @param SimpleXMLElement $response
     *
     * @return ResponseInterface
     */
    abstract protected function createResponse($response);

    /**
     * @return SimpleXMLElement
     */
    protected function getBaseData()
    {
        $data = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><data/>');
        $data['api_version'] = self::API_VERSION;
        $data[0]->default_parms['mid'] = $this->getMerchantId();
        $data[0]->default_parms['pid'] = $this->getPortalId();
        $data[0]->default_parms['bpsecure'] = $this->getSecurityKey();

        return $data;
    }

    /**
     * @param string $country
     *
     * @return string|null ISO-3166-1 Alpha3
     */
    protected function getCountryCode($country)
    {
        $countries = [
            'germany' => 'DEU',
            'deu' => 'DEU',
            'de' => 'DEU',
            'austria' => 'AUT',
            'aut' => 'AUT',
            'at' => 'AUT',
            'switzerland' => 'CHE',
            'swiss' => 'CHE',
            'che' => 'CHE',
            'ch' => 'CHE',
            'netherlands' => 'NLD',
            'the netherlands' => 'NLD',
            'nld' => 'NLD',
            'nl' => 'NLD'
        ];

        return array_key_exists(strtolower($country), $countries) ? $countries[strtolower($country)] : null;
    }

    /**
     * @return string
     */
    protected function getEndpoint()
    {
        return $this->getTestMode() ? $this->testEndpoint : $this->liveEndpoint;
    }
}

# vim :set ts=4 sw=4 sts=4 et :