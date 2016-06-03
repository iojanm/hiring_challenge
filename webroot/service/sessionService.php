<?php

class sessionService {
    
    const REDIS_NOT_CONNECTED_ERROR_CODE = 403;
    const REDIS_NOT_CONNECTED_ERROR_MESSAGE = 'REDIS_NOT_CONNECTED_ERROR_MESSAGE';
    
    const COOKIE_SESSION_NOT_FOUND_ERROR_CODE = 403;
    const COOKIE_SESSION_NOT_FOUND_ERROR_MESSAGE = 'Not a valid session.';
    
    const SESSION_NOT_FOUND_ERROR_CODE = 403;
    const SESSION_NOT_FOUND_ERROR_MESSAGE = 'Not a valid session.';
    
    const PHPREDIS_SESSION_KEY = 'PHPREDIS_SESSION';
    
    const SESION_COOKIE_KEY = 'app';
    
    protected $_redisClient;
    
    protected $_errors = array();
    
    protected $_errorHook;
    
    protected $_session;
    
    function __construct(redisFactory $redisFactory, $onErrorHook = null) {
        $this->setErrorHook($onErrorHook);
        
        $this->setHash($_COOKIE[self::SESION_COOKIE_KEY]);
        
        $this->setRedisClient($redisFactory);
        
        $this->getSessionData();

    }
    
    protected function setRedisClient($redisClient) {
        try {
            if ($redisClient->isConnected()) {
                $this->_redisClient = $redisClient;
            } else {
                $this->setErrors(self::REDIS_NOT_CONNECTED_ERROR_CODE, self::REDIS_NOT_CONNECTED_ERROR_MESSAGE, true);
            }
        } catch (Exception $e) {
             if (is_callable($this->getErrorHook())) {
                return call_user_func_array($this->getErrorHook(), array($this->getErrors()));
            }
        }
    }
    
    
    protected function setErrorHook($hook) {
        $this->_errorHook = $hook;
    }
    
    protected function getErrorHook() {
        return $this->_errorHook;
    }
    
    protected function setSession($session) {
        $this->_session = $session;
    }
    
    protected function getRedisClient() {
        return $this->_redisClient;
    }
    
    protected function getSessionData() {
        $redisFactory = $this->getRedisClient();
        $userSession = $redisFactory->doGet(self::PHPREDIS_SESSION_KEY . ':' . $this->getHash());
        try {
            if (!empty($userSession)) {
                $this->setSession($userSession);
            } else {
                $this->setErrors(self::SESSION_NOT_FOUND_ERROR_CODE, self::SESSION_NOT_FOUND_ERROR_MESSAGE, true);
            }
        } catch (Exception $e) {
             if (is_callable($this->getErrorHook())) {
                return call_user_func_array($this->getErrorHook(), array($this->getErrors()));
            }
        }
    }
    
    protected function setErrors($errorCode, $errorMessage, $throwException) {
        $this->_errors[] = array ('code' => $errorCode, 'message' => $errorMessage);
        if ($throwException) {
            throw new Exception($errorCode);
        }
    }
    
    

    protected function setHash ($hash = null) {
       try {
            if (!empty($hash)) {
                $this->_hash = $hash;
            } else {
                $this->setErrors(self::COOKIE_SESSION_NOT_FOUND_ERROR_CODE, self::COOKIE_SESSION_NOT_FOUND_ERROR_MESSAGE, true);
            }
        } catch (Exception $e) {
             if (is_callable($this->getErrorHook())) {
                return call_user_func_array($this->getErrorHook(), array($this->getErrors()));
            }
        }
    }
    
    protected function getHash() {
         return $this->_hash;
    }
    
    public function getErrors() {
        return $this->_errors;
    }
    
    public function getSession() {
        return $this->_session;
    }
}
