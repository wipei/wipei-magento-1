<?php
/**
 * @category    Ulula
 * @package     Ulula_Wipei
 * @copyright   Copyright (c) 2019 Ulula IT (http://ulula.net)
 * @author        Gaston De Marsico <gdemarsico@ulula.net>
 */

class Ulula_Wipei_Helper_Data extends Mage_Core_Helper_Abstract
{

    const XML_PATH_CLIENT_ID = 'payment/wipei_standard/client_id';
    const XML_PATH_CLIENT_SECRET = 'payment/wipei_standard/client_secret';

    /**
     * Api log file
     */
    const API_LOG_FILE = 'wipei_api.log';

    public function getClientId()
    {
        return (string)Mage::getStoreConfig(self::XML_PATH_CLIENT_ID);
    }

    public function getClientSecret()
    {
        return (string)Mage::getStoreConfig(self::XML_PATH_CLIENT_SECRET);
    }

    public function debugMode()
    {
        return true;
    }

    public function log($message='')
    {
        if (!empty($message)) {
            Mage::log($message, null, self::API_LOG_FILE, $this->debugMode());
        }
    }
}