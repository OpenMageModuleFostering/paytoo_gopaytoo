<?php

/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * @category   PayToo Corp.
 * @package    GoPayToo (go.paytoo.com)
 * @copyright  Copyright (c) 2013 PayToo Corp.
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Paytoo_GoPayToo_Model_Checkout extends Mage_Payment_Model_Method_Abstract {

    protected $_code  = 'gopaytoo';
    protected $_paymentMethod = 'shared';

    public function getCheckout() {
        return Mage::getSingleton('checkout/session');
    }

    public function getOrderPlaceRedirectUrl() {
        return Mage::getUrl('gopaytoo/redirect');
    }

//get SID
    public function getSid() {
	$sid = $this->getConfigData('sid');
	return $sid;
    }

//get Demo Setting
    public function getDemo() {
	if ($this->getConfigData('demo') == '1') {
	    return true;
    	} else {
    	    return false;
    	}
    }

//get purchase routine URL
    public function getUrl() {
        return ($this->getDemo()) ? 'https://go.paytoo.info/gateway' : 'https://go.paytoo.com/gateway';
    }

//get checkout language
    public function getLanguage() {
        $lang = $this->getConfigData('checkout_language');
        return $lang;
    }

//get custom checkout message
    public function getRedirectMessage() {
        $redirect_message = $this->getConfigData('redirect_message');
        return $redirect_message;
    }

//get order
    public function getQuote() {
        $orderIncrementId = $this->getCheckout()->getLastRealOrderId();
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderIncrementId);
        return $order;
    }

//get product data
    public function getProductData() {
	$products = array();
        $items = $this->getQuote()->getAllItems();
        if ($items) {
            $i = 1;
            foreach($items as $item){
                if ($item->getParentItem()) {
                    continue;
                }
		$products['products'][$i]['name'] = $item->getName();
		$products['products'][$i]['description'] = $item->getSku();
		$products['products'][$i]['price'] = number_format($item->getPrice(), 2, '.', '');
		$products['products'][$i]['sku'] = $item->getSku();
		$products['products'][$i]['qty'] = $item->getQtyToInvoice();
		$i++;
            }
        }
        return $products;
    }

	//get lineitem data
    public function getLineitemData() {
        $lineitems = array();
        $items = $this->getQuote()->getAllItems();
        $order_id = $this->getCheckout()->getLastRealOrderId();
        $order    = Mage::getModel('sales/order')->loadByIncrementId($order_id);
        $taxFull = $order->getFullTaxInfo();
        $ship_method   = $order->getShipping_description();
        $coupon = $order->getCoupon_code();
        $lineitem_total = 0;
        $i = 1;
        //get products
        if ($items) {
            foreach($items as $item){
                if ($item->getParentItem()) {
                    continue;
                }
		$lineitems['items'][$i]['type'] = 'product';
		$lineitems['items'][$i]['product_id'] = $item->getSku();
		$lineitems['items'][$i]['quantity'] = $item->getQtyToInvoice();
		$lineitems['items'][$i]['name'] = $item->getName();
		$lineitems['items'][$i]['description'] = $item->getDescription();
		$lineitems['items'][$i]['price'] = number_format($item->getPrice(), 2, '.', '');
		$lineitem_total += number_format($item->getPrice(), 2, '.', '');
            $i++;
            }
	}
	//get taxes
	if ($taxFull) {
            foreach ($taxFull as $rate){
		$lineitems['items'][$i]['type'] = 'tax';
		$lineitems['items'][$i]['name'] = $rate['rates']['0']['code'];
		$lineitems['items'][$i]['price'] = round($rate['amount'], 2);
		$lineitem_total += round($rate['amount'], 2);
		$i++;
	    }
        }
	//get shipping
	if ($ship_method) {
	    $lineitems['items'][$i]['type'] = 'shipping';
	    $lineitems['items'][$i]['name'] = $order->getShipping_description();
	    $lineitems['items'][$i]['price'] = round($order->getShippingAmount(), 2);
	    $lineitem_total += round($order->getShippingAmount(), 2);
	    $i++;
        }
	//get coupons
	if ($coupon) {
	    $lineitems['items'][$i]['type'] = 'coupon';
	    $lineitems['items'][$i]['name'] = $order->getCoupon_code();
	    $lineitems['items'][$i]['price'] = trim(round($order->getBase_discount_amount(), 2), '-');
	    $lineitem_total -= trim(round($order->getBase_discount_amount(), 2), '-');
            $i++;
        }
        return $lineitems;
    }

//check total
    public function checkTotal() {
        $items = $this->getQuote()->getAllItems();
        $order_id = $this->getCheckout()->getLastRealOrderId();
        $order    = Mage::getModel('sales/order')->loadByIncrementId($order_id);
        $taxFull = $order->getFullTaxInfo();
        $ship_method   = $order->getShipping_description();
        $coupon = $order->getCoupon_code();
        $lineitem_total = 0;
        $i = 1;
        //get products
        if ($items) {
            foreach($items as $item){
                if ($item->getParentItem()) {
                    continue;
                }
                $lineitem_total += number_format($item->getPrice(), 2, '.', '');
            }
        }
        //get taxes
        if ($taxFull) {
            foreach($taxFull as $rate){
		$lineitem_total += round($rate['amount'], 2);
            }
        }
        //get shipping
        if ($ship_method) {
            $lineitem_total += round($order->getShippingAmount(), 2);
        }
        //get coupons
        if ($coupon) {
            $lineitem_total -= trim(round($order->getBase_discount_amount(), 2), '-');
        }
        return $lineitem_total;
    }

//get tax data
    public function getTaxData() {
        $order_id = $this->getCheckout()->getLastRealOrderId();
        $order    = Mage::getModel('sales/order')->loadByIncrementId($order_id);
        $taxes = array();
        $taxFull = $order->getFullTaxInfo();
        if ($taxFull) {
            $i = 1;
            foreach($taxFull as $rate){
		$taxes['tax'][$i]['id'] = $rate['rates']['0']['code'];
		$taxes['tax'][$i]['amount'] = round($rate['amount'], 2);
		$i++;
	    }
	}
        return $taxes;
    }

//get HTML form data
    public function getFormFields() {
        $order_id = $this->getCheckout()->getLastRealOrderId();
        $order    = Mage::getModel('sales/order')->loadByIncrementId($order_id);
        $amount   = round($order->getGrandTotal(), 2);
        $a = $this->getQuote()->getShippingAddress();
        $b = $this->getQuote()->getBillingAddress();
        $country = $b->getCountry();
        $currency_code = $order->getOrderCurrencyCode();
        $shipping = round($order->getShippingAmount(), 2);
        $weight = round($order->getWeight(), 2);
        $ship_method   = $order->getShipping_description();
        $tax = trim(round($order->getTaxAmount(), 2));
        $productData = $this->getProductData();
        $taxData = $this->getTaxData();
        $cart_order_id = $order_id;
        $lineitemData = $this->getLineitemData();

	$gopaytooFields = array();

	$gopaytooFields['cart_type'] = 'magento';
	$gopaytooFields['merchant_id'] = $this->getSid();
	$gopaytooFields['lang'] = $this->getLanguage();
	$gopaytooFields['amount'] = $amount;
	$gopaytooFields['tax'] = $tax;
	$gopaytooFields['currency'] = $order->getOrderCurrencyCode(); // USD
	$gopaytooFields['order_ref'] = $order_id;
        $gopaytooFields['order_description'] = Mage::helper('gopaytoo')->__('Order #%s on %s', $order_id, Mage::app()->getWebsite()->getName());

	$gopaytooFields['sub_account_id'] = Mage::app()->getStore()->getId();
	$is_guest = $order->getData('customer_is_guest');
	$customer_id = (!empty($is_guest) && $is_guest=='1') ? 0 : $order->getData('customer_id');
	$gopaytooFields['custom_field1'] = $customer_id;
	$gopaytooFields['custom_field2'] = Mage::app()->getWebsite()->getName();
	$gopaytooFields['custom_field3'] = '';
	$gopaytooFields['custom_field4'] = '';
	$gopaytooFields['custom_field5'] = '';

	/*
	echo "Website ID: " . Mage::app()->getWebsite()->getId() . "<br/>";
	echo "Website Name: " . Mage::app()->getWebsite()->getName() . "<br/>";
	echo "Store ID: " . Mage::app()->getStore()->getId() . "<br/>";
	echo "Store Name: ".Mage::app()->getStore()->getName(). "<br/>";
	echo "Store code: ". Mage::app()->getStore()->getCode()."<br/>";
	*/

	$gopaytooFields['lang'] = $this->getLanguage();
	$gopaytooFields['completed_url'] = Mage::getUrl('gopaytoo/redirect/success', array('_secure' => true));
	$gopaytooFields['cancelled_url'] = Mage::getUrl('gopaytoo/redirect/cancel', array('_secure' => true));
	$gopaytooFields['rejected_url'] = Mage::getUrl('gopaytoo/redirect/reject', array('_secure' => true));;
	$gopaytooFields['esign_url'] = Mage::getUrl('gopaytoo/redirect/esign', array('_secure' => true));;

	$gopaytooFields['user']['email'] = $order->getData('customer_email');
	$gopaytooFields['user']['firstname'] = $b->getFirstname();
	$gopaytooFields['user']['lastname'] = $b->getLastname();
	$gopaytooFields['user']['address'] = $b->getStreet1()." ".$b->getStreet2();
	$gopaytooFields['user']['zipcode'] = $b->getPostcode();
	$gopaytooFields['user']['city'] = $b->getCity();
	$gopaytooFields['user']['country'] = $b->getCountry();
	$gopaytooFields['user']['state'] = ($gopaytooFields['user']['country']=='US') ? $b->getRegion() : '';
	$gopaytooFields['user']['cellphone'] = $b->getTelephone();
	$gopaytooFields['user']['birthday'] = $order->getData('customer_dob');

	if ($a) {
	    $gopaytooFields['shipping']['firstname'] = $a->getFirstname();
	    $gopaytooFields['shipping']['lastname'] = $a->getLastname();
	    $gopaytooFields['shipping']['country'] = $a->getCountry();
	    $gopaytooFields['shipping']['address'] = $a->getStreet1();
	    $gopaytooFields['shipping']['address2'] = $a->getStreet2();
	    $gopaytooFields['shipping']['city'] = $a->getCity();
	    $gopaytooFields['shipping']['state'] = $a->getRegion();
	    $gopaytooFields['shipping']['zip'] = $a->getPostcode();
	    $gopaytooFields['shipping']['cost'] = $shipping;
	    $gopaytooFields['shipping']['weight'] = $weight;
	    $gopaytooFields['shipping']['method'] = $ship_method;
        }

	$gopaytooFields['recurring']['enabled'] = 'no';
	$gopaytooFields['recurring']['amount'] = '';
	$gopaytooFields['recurring']['cycles'] = '';
	$gopaytooFields['recurring']['periodicity'] = '';
	$gopaytooFields['recurring']['start_date'] = '';

	// Calculate/Set the hash keys
	$hashKey = Mage::getStoreConfig('payment/gopaytoo/secret_word');
	$hash = md5($gopaytooFields['merchant_id'].$gopaytooFields['amount'].$gopaytooFields['currency'].$gopaytooFields['order_ref'].$hashKey);
	$gopaytooFields['hash'] = $hash;
	$rhash = md5($gopaytooFields['recurring']['amount'].$gopaytooFields['recurring']['cycles'].$gopaytooFields['recurring']['periodicity'].$gopaytooFields['recurring']['start_date'].$hashKey);
	$gopaytooFields['recurring']['hash'] = $rhash;

	$result = $gopaytooFields + $lineitemData + $taxData + $productData;

	return $result;
    }

}
