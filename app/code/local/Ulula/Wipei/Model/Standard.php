<?php
/**
 * @category    Ulula
 * @package     Ulula_Wipei
 * @copyright   Copyright (c) 2019 Ulula IT (http://ulula.net)
 * @author        Gaston De Marsico <gdemarsico@ulula.net>
 */

class Ulula_Wipei_Model_Standard extends Mage_Payment_Model_Method_Abstract
{

    /**
     * Define payment method code
     */
    const CODE = 'wipei_standard';

    /**
     * define URL to go when an order is cancelled
     */
    const FAILURE_ACTION_URL = 'wipei/standard/failure';

    /**
     * define URL to go when an order is placed
     */
    const ACTION_URL = 'wipei/standard/redirect';

    /**
     * define URL to go when an order fail
     */
    const FAILURE_URL = 'wipei/onepage/failure';

    /**
     * define URL to go when an order success
     */
    const SUCCESS_URL = 'checkout/onepage/success';

    /**
     * {@inheritdoc}
     */
    protected $_code = self::CODE;

    /**
     * {@inheritdoc}
     */
    // protected $_formBlockType = 'wipei/standard_form';

    /**
     * {@inheritdoc}
     */
    protected $_infoBlockType = 'wipei/standard_info';
    
    /**
     * {@inheritdoc}
     */
    protected $_isGateway = true;

    /**
     * {@inheritdoc}
     */
    protected $_canOrder = true;

    /**
     * {@inheritdoc}
     */
    protected $_canAuthorize = true;

    /**
     * {@inheritdoc}
     */
    protected $_canCapture = true;

    /**
     * {@inheritdoc}
     */
    protected $_canCapturePartial = true;

    /**
     * {@inheritdoc}
     */
    protected $_canRefund = true;

    /**
     * {@inheritdoc}
     */
    protected $_canRefundInvoicePartial = true;

    /**
     * {@inheritdoc}
     */
    protected $_canVoid = true;

    /**
     * {@inheritdoc}
     */
    protected $_canFetchTransactionInfo = true;

    /**
     * {@inheritdoc}
     */
    protected $_canCreateBillingAgreement = true;

    /**
     * {@inheritdoc}
     */
    protected $_canReviewPayment = true;

    /**
     * {@inheritdoc}
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('wipei/standard/redirect', array('_secure' => true));
    }

    public function submitPayment()
    {
        $apiHelper = Mage::helper('wipei/api');
        $preference = $this->makePreference();
        $apiPreference =  $apiHelper->createPreference($preference);
        $order = Mage::getModel('sales/order')->loadByIncrementId($preference['external_reference']);
        $initPonit = $apiPreference->init_point;
        $order->addStatusToHistory($order->getStatus(), 'Init Ponit: '.$initPonit);
        $order->save();
        return $apiPreference;
    }

    /**
     * Return array with data about the purchase to send to the api
     *
     * @return array $preference
     */
    public function makePreference()
    {
        $_helperData = Mage::helper('wipei');
        $orderIncrementId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
        $payment = $order->getPayment();
        $preference['external_reference'] = $orderIncrementId;
        $preference['items'] = $this->getItems($order);
        $this->_addDiscounts($preference['items'], $order);
        $this->_addTaxes($preference['items'], $order);
        $this->_addShipping($preference['items'], $order);
        $orderAmount = (float)$order->getBaseGrandTotal();

        $preference['total'] = $orderAmount;
        
        if (isset($payment['additional_information']['doc_number']) 
            && $payment['additional_information']['doc_number'] != "") {
            $preference['payer']['identification'] = array(
                "type"   => "CPF",
                "number" => $payment['additional_information']['doc_number']
            );
        }

        $preference['url_success'] = Mage::getUrl('checkout/onepage/success');
        $preference['url_notify'] = Mage::getUrl('wipei/standard/notify');
        $preference['url_failure'] = Mage::getUrl('checkout/onepage/failure');
        return $preference;
    }

    /**
     * Returns items from order
     * @param  Mage_Sales_Model_Order $order Order
     * @return array Array of items
     */
    protected function getItems($order)
    {
        $items = array();
        foreach ($order->getAllVisibleItems() as $item) {
            $product = $item->getProduct();
            $image = (string)Mage::helper('catalog/image')->init($product, 'image');
            $items[] = array(
                'id_ext'     => $item->getSku(),
                'name'       => $product->getName(),
                'quantity'   => (int)number_format($item->getQtyOrdered(), 0, '.', ''),
                'price'      => (float)number_format($item->getPrice(), 2, '.', '')
            );
        }

        return $items;
    }

    /**
     * Calculate discount of magento site and set data in arr param
     *
     * @param $arr
     * @param $order
     */
    protected function _addDiscounts(&$arr, $order)
    {
        if ($order->getDiscountAmount() < 0) {
            $arr[] = array(
                "name"        => "Descuentos",
                "quantity"    => 1,
                "price"       => (float)$order->getDiscountAmount()
            );
        }
    }

    /**
     * Calculate taxes of magento site and set data in arr param
     * @param $arr
     * @param $order
     */
    protected function _addTaxes(&$arr, $order)
    {
        if ($order->getBaseTaxAmount() > 0) {
            $arr[] = array(
                "name"       => "Impuestos",
                "quantity"   => 1,
                "price"      => (float)$order->getBaseTaxAmount()
            );
        }
    }

    /**
     * Calculate shipping cost of magento site and set data in arr param
     * @param $arr
     * @param $order
     */
    protected function _addShipping(&$arr, $order)
    {
        if ($order->getBaseShippingAmount() > 0) {
            $arr[] = array(
                "name"      => "Entrega",
                "quantity"  => 1,
                "price"     => (float)$order->getBaseShippingAmount()
                );
        }
    }
}