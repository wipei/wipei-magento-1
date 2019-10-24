<?php
/**
 * @category    Ulula
 * @package     Ulula_Wipei
 * @copyright   Copyright (c) 2019 Ulula IT (http://ulula.net)
 * @author    	Gaston De Marsico <gdemarsico@ulula.net>
 */

class Ulula_Wipei_Helper_Api extends Ulula_Wipei_Helper_Data
{

	/**
     * Api version
     */
    const VERSION = "0.1.0";

    /**
     * Api url
     */
    const API_URL = 'https://api.wipei.com.ar/';

     /**
     * @var mixed
     */
    private $client_id;
    /**
     * @var mixed
     */
    private $client_secret;
    /**
     * @var mixed
     */
    private $access_token;
    /**
     * @var
     */
    private $access_data;
    /**
     * @var null
     */
    private $_platform = 'Magento';
    /**
     * @var null
     */
    private $_so = null;
    /**
     * @var null
     */
    private $_type = null;

    /**
     * Ulula_Wipei_Helper_Api constructor.
     * @throws
     */
    // public function __construct() {
    //     $i = func_num_args();
    //     if ($i > 2 || $i < 1) {
    //         throw new \Exception('Invalid arguments. Use CLIENT_ID and CLIENT SECRET, or ACCESS_TOKEN');
    //     }
    //     if ($i == 1) {
    //         $this->access_token = func_get_arg(0);
    //     }
    //     if ($i == 2) {
    //         $this->client_id = func_get_arg(0);
    //         $this->client_secret = func_get_arg(1);
    //     }
    // }

     /**
     * Get Access Token for API use
     * @throws
     */
    public function get_access_token() {
        if (isset ($this->access_token) && !is_null($this->access_token)) {
            return $this->access_token;
        }
        $params = array(
            'client_id' => $this->getClientId(),
            'client_secret' => $this->getClientSecret()
        );
        $response = $this->post('token', json_encode($params));
        $this->access_token = $response->access_token;
        return $this->access_token;
    }

    protected function getDefaultHeaders()
    {
        return array('Content-Type: application/json');
    }

    protected function getHeaders($options)
    {
        $headers = $this->getDefaultHeaders();
        foreach ($options as $option) {
            $headers[] = $option;
        }
        return $headers;
    }

    /**
     * Create a checkout preference
     * @param array $preference
     * @return array(json)
     * @throws
     */
    public function createPreference($preference)
    {
        $extra_params =  array('platform: ' . $this->_platform, 'so;', 'type: ' .  $this->_type, 'Authorization: ' . $this->get_access_token());
        $preference_result = $this->post('order', json_encode($preference), $extra_params);
        return $preference_result;
    }

    public function post($uri, $params, $headers = array())
    // {https://github.com/wipei/wipei-magento-php/blob/master/Lib/RestClient.php
    // 	https://github.com/wipei/wipei-magento-php/blob/master/Lib/Api.php
    // 	https://bitbucket.org/gdemarsico/dmg_recaptcha/src/master/src/app/code/local/Dmg/ReCaptcha/Helper/Data.php
    {
    	try{
	    	$http = new Varien_Http_Adapter_Curl();
	    	$config = array('timeout' => 10);
	    	$headers = $this->getHeaders($headers);
            $this->log('headers: '.json_encode($headers));
            $this->log('params: '.$params);
	    	$http->write(
	                Zend_Http_Client::POST, 
	                self::API_URL.$uri,
	                '1.1',
	                $headers,
	                $params
	            );
	    	$res = $http->read();
            $cod = Zend_Http_Response::extractCode($res);
            if ($cod == 200) {
                $response = Zend_Http_Response::extractBody($res);
            }
            $this->log($cod);
            $this->log($response);
            $http->close();
            return json_decode($response);
    	} catch(Exception $e) {
                $this->log($e);
        }
    }

}