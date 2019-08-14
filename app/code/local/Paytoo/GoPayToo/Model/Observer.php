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

class Paytoo_GoPayToo_Model_Observer {

    public function issue_creditmemo_refund(Varien_Object $payment) {

        $refund = Mage::getStoreConfig('payment/gopaytoo/refund');

        if ($refund == '1') {
            $sandbox = Mage::getStoreConfig('payment/gopaytoo/demo');
            $url = ($sandbox=='1') ? 'https://www.paytoo.info/api/merchant?wsdl' : 'https://www.gopaytoo.com/api/merchant?wsdl';
        
            $creditmemo = $payment->getCreditmemo()->getOrder()->getData();
            $creditmemo_amount = $payment->getCreditmemo()->getData();
            $creditmemo_comment = $payment->getCreditmemo()->getCommentsCollection()->toArray();
    
            if (empty($creditmemo['ext_order_id'])) {                
                return ;
            }

            if (isset($creditmemo_comment['items'][0]['comment'])){
                $reason = $creditmemo_comment['items'][0]['comment'];
            } else {
                $reason = 'Refund issued by seller';
            }

            $merchant_id = Mage::getStoreConfig('payment/gopaytoo/sid');
            $password = Mage::getStoreConfig('payment/gopaytoo/password');
            $amount = $creditmemo_amount['grand_total'];
            $request_id = $creditmemo['ext_order_id'];

            $soap = new SoapClient($url);
            try {
                // Authentification
                $response = $soap->auth($merchant_id, $password);
                if($response->status=='OK') {
                    // Refund
                    // Refund($request_id=null, $tr_id=null, $amount=null, $reason=null)
                    $response = $soap->Refund($request_id, null, $amount, $reason);
                    if ($response->status=='OK') {
                        $log = "Transaction refunded on GoPayToo";
                    } elseif($response->status=='ERROR') {
                        $log = $response->msg;
                    }
                } else {
                    $log = $response->msg;
                }
            } catch (Exception $e) {
                $log = $e->getMessage();
            }
            
            if (!empty($log)) {
                $order = $payment->getCreditmemo()->getOrder();
                $order->addStatusHistoryComment($log);
                $order->save();
            }
        }
        
    }
}
?>
