<?php

namespace Omnipay\BillPay\Message;

use Omnipay\BillPay\Customer;
use Omnipay\BillPay\Item;
use Omnipay\Common\CreditCard;
use Omnipay\Common\Exception\InvalidRequestException;
use Omnipay\Common\ItemBag;
use Omnipay\Tests\TestCase;

/**
 * Class AuthorizeRequestTest
 *
 * @package   Omnipay\BillPay
 * @author    Andreas Lange <andreas.lange@quillo.de>
 * @copyright 2016, Quillo GmbH
 * @license   MIT
 */
class AuthorizeRequestTest extends TestCase
{
    /** @var AuthorizeRequest */
    private $request;

    public function setUp()
    {
        $client = $this->getHttpClient();
        $request = $this->getHttpRequest();

        $this->request = new AuthorizeRequest($client, $request);
        $this->request->setPaymentMethod(AuthorizeRequest::PAYMENT_TYPE_INVOICE);
        $this->request->setExpectedDaysTillShipping(2);
        $this->request->setCard(new CreditCard());
        $this->request->setCustomerDetails(new Customer());
        $this->request->setItems(new ItemBag([
            new Item([
                'id' => '1',
                'name' => 'IT-12345',
                'description' => 'Article 12345 - white',
                'quantity' => 1,
                'price' => '5.00',
                'priceNet' => '4.2017'
            ]),
            new Item([
                'id' => '2',
                'name' => 'IT-67890',
                'description' => 'Item 67890',
                'quantity' => 1,
                'price' => '5.00',
                'priceNet' => '4.2017'
            ]),
        ]));
    }

    public function testCardNotExist()
    {
        self::setExpectedException(
            InvalidRequestException::class,
            'Credit card and customer object required for address details.'
        );
        $this->request->setCard(null);
        $this->request->getData();
    }

    public function testCustomerNotExist()
    {
        self::setExpectedException(
            InvalidRequestException::class,
            'Customer object required for additional details not covered by card.'
        );
        $this->request->setCustomerDetails(null);
        $this->request->getData();
    }

    public function testDifferingAddresses()
    {
        $card = new CreditCard([
            'firstName' => 'TEST2',
            'billingFirstName' => 'TEST1'
        ]);

        self::assertXmlStringEqualsXmlFile(
            __DIR__ . '/Xml/AuthorizeRequest.DifferingAddresses.xml',
            $this->request->setCard($card)->getData()->asXML()
        );
    }

    public function testGetData()
    {
        self::assertXmlStringEqualsXmlFile(
            __DIR__ . '/Xml/AuthorizeRequest.GetData.xml',
            $this->request->getData()->asXML()
        );
    }

    public function testItemsIncorrectType()
    {
        self::setExpectedException(InvalidRequestException::class, 'Items must be of instance \Omnipay\BillPay\Item');
        $this->request->setItems(new ItemBag([
            new \Omnipay\Common\Item([
                'id' => '1',
                'name' => 'IT-12345',
                'description' => 'Article 12345 - white',
                'quantity' => 1,
                'price' => '5.00',
                'priceNet' => '4.2017'
            ])
        ]));
        $this->request->getData();
    }

    public function testItemsNotExist()
    {
        self::setExpectedException(InvalidRequestException::class, 'This request requires items.');
        $this->request->setItems(null);
        $this->request->getData();
    }

    public function testPaymentMethodInvalid()
    {
        self::setExpectedException(
            InvalidRequestException::class,
            'Unknown payment method specified \'bananas\' specified.'
        );
        $this->request->setPaymentMethod('bananas');
        $this->request->getData();
    }

    public function testPaymentMethodNotSet()
    {
        self::setExpectedException(InvalidRequestException::class, 'This request requires a payment method.');
        $this->request->setPaymentMethod(null);
        $this->request->getData();
    }
}
