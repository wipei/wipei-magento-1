<?php
/**
 * @category    Ulula
 * @package     Ulula_Wipei
 * @copyright   Copyright (c) 2019 Ulula IT (http://ulula.net)
 * @author    	Gaston De Marsico <gdemarsico@ulula.net>
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
		
		// $block = Mage::app()->getLayout()->createBlock('wipei/standard_pay');
		// $block->assign($array_assign);
		// $this->getLayout()->getBlock('content')->append($block);
		// $this->renderLayout();
	}

	public function notifyAction() 
	{
		$helper = Mage::helper('wipei');
		$params = Mage::app()->getRequest()->getParams();
		$helper->log('Notification: '.json_encode($params));
		if (empty($params['id'])) {

		}
	}
}