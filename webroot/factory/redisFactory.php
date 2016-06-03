<?php
/*
 * Redis factory Wrapper class
 */
class redisFactory {
    
    const INVALID_CONNECTION_DATA_CODE = 500;
    const INVALID_CONNECTION_DATA_MESSAGE = 'Server error, invalid configuration.';
    const CANNOT_CONNECT_CODE = 403;
    const CANNOT_CONNECT_MESSAGE = 'Server error, can\'t connect.';
    
    protected $_host;
    
    protected $_port;
    
    protected $_connection;
    
    protected $_isConnected = false;
    
    protected $_errors = array();
    
    protected $_errorHook;
    
    function __construct($host, $port, $_errorHook = null) {
        $this->setErrorHook($_errorHook);
        $this->setHost($host);
        $this->setPort($port);
        $this->doConnect();
    }
    
    function setErrorHook($hook) {
        $this->_errorHook = $hook;
    }
    
    function getErrorHook() {
        return $this->_errorHook;
    }
    function setHost($host) {
        $this->_host = $host;
    }
    
    function getHost() {
        return $this->_host;
    }
    
    function setIsConnected($value) {
        $this->_isConnected = $value;
    }
    
    function getIsConnected() {
        return $this->_isConnected;
    }
    
    function setPort($port) {
        $this->_port = $port;
    }
    
    function getPort() {
        return $this->_port;
    }
    
    function setErrors($errorCode, $errorMessage, $throwException) {
        $this->_errors[] = array ('code' => $errorCode, 'message' => $errorMessage);
        if ($throwException) {
            throw new Exception($errorCode);
        }
    }
    
    function getErrors() {
        return $this->_errors;
    }
    
   function  doConnect() {
       try {
            $host = $this->getHost();
            $port = $this->getPort();
            if ($host != null && $port != null) {
                $redis = new Redis();
                $redis->connect($host, $port);
                if ($redis->isConnected()) {
                    $this->setIsConnected(true);
                    $redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
                    $this->_connection = $redis;
                } else {
                    $this->setIsConnected(false);
                    $this->setErrors(self::CANNOT_CONNECT_CODE, self::CANNOT_CONNECT_MESSAGE, true);
                }
            } else {
                $this->setErrors(self::INVALID_CONNECTION_DATA_CODE, self::INVALID_CONNECTION_DATA_MESSAGE, true);
            }
        } catch (Exception $e) {
             if (is_callable($this->getErrorHook())) {
                return call_user_func_array($this->getErrorHook(), array($this->getErrors()));
            }
        }
    }
    
    function getConnection() {
        return $this->_connection;
    }

    function doGet ($key) {
        $client = $this->getConnection();
        return $client->get($key);
    }
    
    function doMget ($keys) {
        $client = $this->getConnection();
        return $client->mget($keys);
    }
    
    function isConnected () {
        return $this->getIsConnected();
    }
}
