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
 * @package    GoPayToo (gopaytoo.com)
 * @copyright  Copyright (c) 2013 PayToo Corp.
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Paytoo_GoPayToo_RedirectController extends Mage_Core_Controller_Front_Action {

    public function getCheckout() {
    return Mage::getSingleton('checkout/session');
    }

    protected $order;

    protected function _expireAjax() {
        if (!Mage::getSingleton('checkout/session')->getQuote()->hasItems()) {
            $this->getResponse()->setHeader('HTTP/1.1','403 Session Expired');
            exit;
        }
    }

    public function indexAction() {
        $this->getResponse()
                ->setHeader('Content-type', 'text/html; charset=utf8')
                ->setBody($this->getLayout()
                ->createBlock('gopaytoo/redirect')
                ->toHtml());
    }

    public function successAction() {
        $request = $_POST['MerchantApiResponse']['PaytooRequest'];
        $hash = $_POST['MerchantApiResponse']['hash'];
        $post = $this->getRequest()->getPost();
        
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($post['order_ref']);
        Mage::getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();
        $order = Mage::getModel('sales/order');
        $order->loadByIncrementId($session->getLastRealOrderId());
        
        if (empty($request) || empty($hash) || empty($request['status'])) {
            $this->_redirect('checkout/onepage/success');
            $order->addStatusHistoryComment('Missing parameters. Order cannot be verified.');
            $order->save();
            return ;
        }
        
        $hashKey = Mage::getStoreConfig('payment/gopaytoo/secret_word');
        $myhash = md5($request['request_id'].$request['amount'].$request['currency'].$request['status'].$hashKey);
                
        if ($myhash == $hash) {
            //TODO: check currency
            $expected_amount = $order->getBaseGrandTotal();
            if ($request['status']!='completed' && $request['status']!='accepted') {
                $this->rejectAction();
            } elseif ($expected_amount==$request['amount']) {
                $this->_redirect('checkout/onepage/success');
                $order->setData('ext_order_id', $request['request_id']);
                $order->save();
                /*
                 * IPN will do that
                 * */
                /* $state = $order->getState();
                if ($state!=Mage_Sales_Model_Order::STATE_PROCESSING) {
                    $payment = $order->getPayment();
                    $payment->setTransactionId($request['request_id'])
                        ->setPreparedMessage($request['status_infos'])
                        ->setParentTransactionId(null)
                        ->setShouldCloseParentTransaction(true)
                        ->setIsTransactionClosed(0)
                        ->registerCaptureNotification($request['amount']);
                    $order->save();
                    
                    $order->sendNewOrderEmail();
                    $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true)->save();
                } */
            } else {
                $this->_redirect('checkout/onepage/success');
                $order->addStatusHistoryComment($request['amount']);
                $order->addStatusHistoryComment('Received amount differ from requested amount.');
                $order->save();
            }
        } else {
            $this->_redirect('checkout/onepage/success');
            $order->addStatusHistoryComment($hashTotal);
            $order->addStatusHistoryComment('Hash did not match, check secret word.');
            $order->save();
        }
    }

    /**
     * When a customer cancel payment from PayToo.
     */
    public function cancelAction() {
        $post = $this->getRequest()->getPost();
        
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($post['order_ref']);
		
	$lastQuoteId = $session->getLastQuoteId();
	$lastOrderId = $session->getLastOrderId();
		
        if($lastQuoteId && $lastOrderId) {
            $orderModel = Mage::getModel('sales/order')->load($lastOrderId);
            if($orderModel->canCancel()) {
                $quote = Mage::getModel('sales/quote')->load($lastQuoteId);
                $quote->setIsActive(true)->save();
                $orderModel->cancel();
                $orderModel->setStatus('canceled');
                $orderModel->addStatusHistoryComment('Order cancelled on PayToo Gateway.');
                $orderModel->save();
                Mage::getSingleton('core/session')->setFailureMsg('order_failed');
                Mage::getSingleton('checkout/session')->setFirstTimeChk('0');
            }
        }
		
        $this->_redirect('checkout/cart');
        return;
    }

    /**
     * When a payment is rejected from PayToo.
     */
    public function rejectAction() {
        $post = $this->getRequest()->getPost();
        
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($post['order_ref']);
		
	$lastQuoteId = $session->getLastQuoteId();
	$lastOrderId = $session->getLastOrderId();
	
        if($lastQuoteId && $lastOrderId) {
            $orderModel = Mage::getModel('sales/order')->load($lastOrderId);
            if($orderModel->canCancel()) {
                $quote = Mage::getModel('sales/quote')->load($lastQuoteId);
                $quote->setIsActive(true)->save();
                $orderModel->cancel();
                $orderModel->setStatus('closed');
                $orderModel->addStatusHistoryComment('Payment rejected on PayToo Gateway.');
                $orderModel->save();
                Mage::getSingleton('core/session')->setFailureMsg('order_failed');
                Mage::getSingleton('checkout/session')->setFirstTimeChk('0');
            }
        }

        $this->_redirect('checkout/cart');
        return;
    }

    /**
     * When a payment is pending for signature from PayToo.
     */
    public function esignAction() {
        $post = $this->getRequest()->getPost();
        $request = $_POST['MerchantApiResponse']['PaytooRequest'];
        $hash = $_POST['MerchantApiResponse']['hash'];
        
        $session = Mage::getSingleton('checkout/session');
        $session->setQuoteId($post['order_ref']);
		
	$lastQuoteId = $session->getLastQuoteId();
	$lastOrderId = $session->getLastOrderId();
	
        if($lastQuoteId && $lastOrderId && ($request['status']=='accepted' || $request['status']=='completed')) {
            $orderModel = Mage::getModel('sales/order')->load($lastOrderId);
            
            $hashKey = Mage::getStoreConfig('payment/gopaytoo/secret_word');
            $myhash = md5($request['request_id'].$request['amount'].$request['currency'].$request['status'].$hashKey);
                    
            if ($myhash == $hash) {
                if($orderModel->canHold()) {
                    $quote = Mage::getModel('sales/quote')->load($lastQuoteId);
                    $quote->setIsActive(true)->save();
                    $orderModel->hold();
                    $orderModel->setStatus('holded');
                    $orderModel->addStatusHistoryComment('Payment on hold for signature.');
                    $orderModel->save();
                }
            } else {
                $this->_redirect('checkout/onepage/success');
                $order->addStatusHistoryComment($hashTotal);
                $order->addStatusHistoryComment('Hash did not match, check secret word.');
                $order->save();
            }
        }

        $this->_redirect('checkout/onepage/success');
        return;
    }
    
}

?>