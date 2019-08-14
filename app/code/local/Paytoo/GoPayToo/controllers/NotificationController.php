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

class Paytoo_GoPayToo_NotificationController extends Mage_Core_Controller_Front_Action {

    public function indexAction() {
        $request = $_POST['MerchantApiResponse']['PaytooRequest'];
	$account = $_POST['MerchantApiResponse']['PaytooAccount'];
	$hash = $_POST['MerchantApiResponse']['hash'];

	/*
IPN: array (
  'request_id' => '10187',
  'tr_id' => '21310',
  'order_ref' => '100000014',
  'w_number' => '',
  'phone_number' => '',
  'tr_status' => 'completed',
  'tr_amount' => '18.5000',
  'tr_currency' => 'USD',
  'tr_requested_amount' => '18.5000',
  'tr_requested_currency' => 'USD',
  'tr_change_rate' => '',
  'hash' => '70e0e8afc5cf6e5db0fb0af518774577',
  'MerchantApiResponse' =>
  array (
    'status' => 'OK',
    'request_id' => '10187',
    'request_status' => 'completed',
    'tr_id' => '21310',
    'sub_account_id' => NULL,
    'ref_id' => '100000014',
    'msg' => NULL,
    'info' => NULL,
    'phone_number' => NULL,
    'w_number' => NULL,
    'PaytooRequest' =>
    array (
      'request_id' => '10187',
      'tr_id' => '21310',
      'user_id' => '10343',
      'ref_id' => '100000014',
      'sub_account_id' => NULL,
      'date' => '2013-04-11 10:10:13',
      'refund_date' => NULL,
      'expiration' => '2013-04-13 10:10:13',
      'statement_id' => NULL,
      'refund_statement_id' => NULL,
      'method' => 'gateway',
      'type' => 'creditcard',
      'is_pre_auth' => NULL,
      'is_recurring' => NULL,
      'is_a_cycle' => NULL,
      'recurring_id' => NULL,
      'currency' => 'USD',
      'amount' => '18.50',
      'description' => 'Order #100000014 on PayToo Demo Store',
      'addinfo' => NULL,
      'status' => 'completed',
      'status_infos' => NULL,
      'recurring_amount' => NULL,
      'recurring_cycles' => NULL,
      'recurring_period' => NULL,
      'recurring_start' => NULL,
      'recurring_end' => NULL,
      'recurring_status' => NULL,
      'recurring_info' => NULL,
      'transaction' => NULL,
      'card_present' => NULL,
      'employee_id' => NULL,
      'location_id' => NULL,
      'firstname' => NULL,
      'lastname' => NULL,
      'email' => NULL,
    ),
    'PaytooTransaction' =>
    array (
      'tr_id' => '21310',
      'tr_type' => 'creditcard2merchant',
      'tr_from_type' => 'creditcard',
      'tr_from_id' => '241',
      'tr_from_currency' => 'USD',
      'tr_to_type' => 'merchant',
      'tr_to_id' => '12345678',
      'tr_to_currency' => 'USD',
      'tr_requested_original' => '18.5000',
      'tr_requested_currency' => 'USD',
      'tr_amount_requested' => '18.5000',
      'tr_amount_transfered' => '18.5000',
      'tr_amount_total_cost' => '18.5000',
      'tr_amount_refunded' => NULL,
      'tr_change_rate' => NULL,
      'tr_fees' => '0.0000',
      'tr_fees_currency' => 'USD',
      'tr_fees_type' => 'fixed',
      'tr_fees_rate_fixed' => '0.0000',
      'tr_fees_rate_percent' => '0.0000',
      'tr_fees_level' => '0',
      'tr_date_created' => '2013-04-11 10:10:13',
      'tr_date_updated' => '2013-04-11 10:10:13',
      'tr_date_completed' => '2013-04-11 10:10:13',
      'tr_date_refunded' => NULL,
      'tr_notif_sender' => 'email',
      'tr_notif_receiver' => 'none',
      'tr_status' => 'completed',
      'tr_status_msg' => NULL,
      'pay_infos' => '13962045',
    ),
    'PaytooAccount' =>
    array (
      'user_id' => '10343',
      'wallet' => '02416039',
      'currency' => 'USD',
      'balance' => NULL,
      'registered_phone' => false,
      'max_pin' => false,
      'sim_phonenumber' => false,
      'prepaidcard' => false,
      'email' => 'cedric@mayol.biz',
      'password' => NULL,
      'gender' => 'm',
      'firstname' => 'C̩dric',
      'middlename' => '',
      'lastname' => 'Mayol',
      'address' => '13 rue des ch̻nes ',
      'city' => 'Cr̩py en Valois',
      'zipcode' => '60800',
      'country' => 'FR',
      'state' => '',
      'phone' => '33679555985',
      'birthday' => '1979-10-02',
      'security_code' => NULL,
      'level' => NULL,
      'question1' => '1',
      'answer1' => 'Campione',
      'question2' => '2',
      'answer2' => 'Bozo',
      'question3' => '3',
      'answer3' => 'Buzz',
      'citizenship' => '',
      'id_type' => 'Passport',
      'id_issued_by_country' => 'FR',
      'id_issued_by_state' => '',
      'id_number' => '012345678',
      'id_expiration' => '2013-03-07',
      'dist_id' => NULL,
      'res_id' => NULL,
      'document1' => NULL,
      'document2' => NULL,
      'document3' => NULL,
      'custom_field1' => NULL,
      'custom_field2' => NULL,
      'custom_field3' => NULL,
      'custom_field4' => NULL,
      'custom_field5' => NULL,
    ),
    'hash' => '763e67ba7807f9d0a0732c01f446dc71',
  ),
)
	*/

	if (empty($request) || empty($request['request_id']) || empty($request['ref_id']) || empty($request['amount']) || empty($request['status']) || empty($hash)) {
	    die("Missing parameters");
	    return ;
	}

        $order = Mage::getModel('sales/order');
        $order->loadByIncrementId($request['ref_id']);
        $invoice_on_fraud = Mage::getStoreConfig('payment/gopaytoo/invoice_on_fraud');
        $invoice_on_order = Mage::getStoreConfig('payment/gopaytoo/invoice_on_order');

        $hashKey = Mage::getStoreConfig('payment/gopaytoo/secret_word');
        $myhash = md5($request['request_id'].$request['amount'].$request['currency'].$request['status'].$hashKey);

	if ($myhash != $hash) {
	    $order->addStatusHistoryComment('Hash did not match! Expected: '.$myhash.' Received: '.$hash);
            $order->save();
	    return ;
	}

	// Set external customer ID and request ID for reference
	$order->setData('ext_order_id', $request['request_id'] );
	$order->setData('ext_customer_id', $account['user_id']);
	$order->save();

	if ($request['status']=='cancelled' || $request['status']=='rejected' || $request['status']=='error' || $request['status']=='expired') {
	    $order->setState(Mage_Sales_Model_Order::STATE_CANCELED, true)->addStatusHistoryComment('Payment rejected or cancelled.')->save();
	    if (!empty($request['status_infos']))
		$order->addStatusHistoryComment($request['status_infos']);
	    $order->save();
	    return ;
	}

	if ($request['status']=='accepted') {
	    // Pending for signature
	    if($order->canHold()) {
		$order->hold();
		$order->setStatus('holded');
		$order->addStatusHistoryComment('Payment on hold for signature.');
		$order->save();
	    }
	    return ;
	}

	if ($request['status']=='chargeback' || $request['status']=='unpaid') {
	    // Chargeback or Unpaid notification
	    // TODO: do something automatically
	    $order->addStatusHistoryComment("Payment has been unpaid (".$request['status'].") - no action taken");
	    if (!empty($request['status_infos']))
		$order->addStatusHistoryComment($request['status_infos']);
	    $order->save();
	    return ;
	}

	if ($request['status']=='refunded') {
	    // Refund notification
	    // TODO: do something automatically
	    $order->addStatusHistoryComment("Payment has been refunded - no action taken");
	    if (!empty($request['status_infos']))
		$order->addStatusHistoryComment($request['status_infos']);
	    $order->save();
	}

	if ($request['status']!='completed') {
	    $order->addStatusHistoryComment("Invalid IPN status: ".$request['status']." - not treated");
	    if (!empty($request['status_infos']))
		$order->addStatusHistoryComment($request['status_infos']);
	    $order->save();
	    return ;
	}

	// Below this it is only 'completed' payment notification
	$order->setData('ext_order_id', $request['request_id']);
	$order->save();

	$expected_amount = $order->getBaseGrandTotal();
	if ($expected_amount != $request['amount']) {
	    //TODO: check currency
	    $order->addStatusHistoryComment('Amount received is not the one expected! Expected: '.$expected_amount.' Received: '.$request['amount']);
            $order->save();
	    return ;
	}

	$payment = $order->getPayment();

        $payment->setTransactionId($request['request_id'])
            ->setPreparedMessage($request['status_infos'])
            ->setParentTransactionId(null)
            ->setShouldCloseParentTransaction(true)
            ->setIsTransactionClosed(0)
            ->registerCaptureNotification($request['amount']);

        $order->save();

	$order->sendNewOrderEmail();

	$state = $order->getState();
	if ($state!=Mage_Sales_Model_Order::STATE_PROCESSING) {
            $order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true)->save();
	}

	/*
	if ($invoice_on_order == '1') {
	    try {
		if(!$order->canInvoice()) {
		    Mage::throwException(Mage::helper('core')->__('Cannot create an invoice.'));
		}
		$invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
		if (!$invoice->getTotalQty()) {
		    Mage::throwException(Mage::helper('core')->__('Cannot create an invoice without products.'));
		}

		$invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
		$invoice->register();
		$transactionSave = Mage::getModel('core/resource_transaction')
		    ->addObject($invoice)
		    ->addObject($invoice->getOrder());
		$transactionSave->save();
	    } catch (Mage_Core_Exception $e) {
		echo $e;
	    }
	}
	*/

    }
}

?>
