<?php
/**
 * @category    Ulula
 * @package     Ulula_Wipei
 * @copyright   Copyright (c) 2019 Ulula IT (http://ulula.net)
 * @author    	Gaston De Marsico <gdemarsico@ulula.net>
 */

class Ulula_Wipei_Block_Standard_Pay extends Mage_Core_Block_Template
{
	protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('wipei/standard/pay.phtml');
    }
}