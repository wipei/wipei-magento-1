<?php
/**
 * @category    Ulula
 * @package     Ulula_Wipei
 * @copyright   Copyright (c) 2019 Ulula IT (http://ulula.net)
 * @author        Gaston De Marsico <gdemarsico@ulula.net>
 */

class Ulula_Wipei_StandardController extends Mage_Core_Controller_Front_Action
{
    public function redirectAction()
    {
        $standard = Mage::getModel('wipei/standard');
        $response = $standard->submitPayment();
        if (!$response) {
            $this->_redirect('checkout/onepage/failure');
        } else {
            Mage::app()->getResponse()->setRedirect($response->init_point) ->sendResponse();
        }
    }

    public function notifyAction() 
    {
        $helper = Mage::helper('wipei');
        $params = Mage::app()->getRequest()->getParams();
        $helper->log('Notification: '.json_encode($params));
        if (empty($params['id'])) {
            $helper->log('Order not found');
        } else {
            $data = $this->_getFormattedPaymentData($params['id']);
            $order = Mage::getModel('sales/order')->loadByIncrementId($data->external_reference);
            if ($order->getId()) {
                switch ($data->status) {
                    case 'approved':
                        $this->createInvoice($order);
                        $status = 'processing';
                        break;
                    case 'cancelled':
                        $order->cancel();
                        $status = 'cancelled';
                        break;
                    default:
                        $status = $order->getStatus();
                        break;
                }

                $order->addStatusToHistory($status, 'Status: '.$data->status);
                $order->save();
            }
        }
    }

    protected function createInvoice($order)
    {
        if ($order->canInvoice()) {
            $capture = Mage_Sales_Model_Order_Invoice::CAPTURE_ONLINE;
            $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
            $invoice->setRequestedCaptureCase($capture);
            $invoice->register();
            $transaction = Mage::getModel('core/resource_transaction')
                ->addObject($invoice)
                ->addObject($invoice->getOrder());

            $transaction->save();
        }
    }

    protected function _getFormattedPaymentData($paymentId, $data = array())
    {
        $helperApi = Mage::helper('wipei/api');
        return $helperApi->getPayment($paymentId);
    }
}