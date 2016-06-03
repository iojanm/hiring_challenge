<?php

/**
 * Implements chatAPI 
 */
class chatAPI {
    
    const FRIENDS_CACHE_PREFIX_KEY = 'chat:friends:{:userId}';
    const ONLINE_CACHE_PREFIX_KEY = 'chat:online:{:userId}';
    
    const REDIS_NOT_CONNECTED_ERROR_CODE = 403;
    const REDIS_NOT_CONNECTED_ERROR_MESSAGE = 'REDIS_NOT_CONNECTED_ERROR_MESSAGE';
    
    const USER_ID_NOT_FOUND_ERROR_CODE = 404;
    const USER_ID_NOT_FOUND_ERROR_MESSAGE = 'Friends list not available.';

    protected $_errors = array();
    
    protected $_errorHook;
    
    protected $_session;
    
    protected $_redisClient;
    
    protected $_friendList;
    
    /**
     * chatAPI Contructor
     * @param redisFactory $redisClient redis server client
     * @param sessionService $sessionService session service
     * @param function $onErrorHook Error callback clousure function
     */    
    function __construct(redisFactory $redisClient, sessionService $sessionService, $onErrorHook = null) {
        
        $this->setErrorHook($onErrorHook);
        
        $this->setSession($sessionService->getSession());
        
        $this->setRedisClient($redisClient);
        
        $this->getUserFriendListFromService();

    }

    /**
     * Set redis server client
     * @param redisFactory $redisClient redis server client
     */  
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
    
     /**
     * Set _errorHook property
     * 
     * @param function $hook Error callback clousure function
     */
    protected function setErrorHook($hook) {
        $this->_errorHook = $hook;
    }
    
    /**
     * Get _errorHook property
     */
    protected function getErrorHook() {
        return $this->_errorHook;
    }
    
    /**
     * Set _session property
     * @param sessionService $sessionService session service
     */
    protected function setSession($session) {
        $this->_session = $session;
    }
    
     /**
     * Get _session property
     */
    protected function getRedisClient() {
        return $this->_redisClient;
    }
    
    /**
     * Set _friendList property
     * @param friendList $friendList 
     */
    protected function setUserFriendsList($friendList) {
        $this->_friendList = $friendList;
    }
    
    /**
     * Get _friendList property
     */
    protected function getUserFriendsList() {
        return $this->_friendList;
    }
    
    
    /**
     * Get friend from service for loggedIn User
     */
    protected function getUserFriendListFromService() {
        
       $session = $this->getSession();
       try {
            if (!empty($session['default']['id'])) {
                $redisClient = $this->getRedisClient();
                $key = $this->formatKey('{:userId}', $session['default']['id'], self::FRIENDS_CACHE_PREFIX_KEY);
                $this->setUserFriendsList($redisClient->doGet($key));
            } else {                
                $this->setErrors(self::USER_ID_NOT_FOUND_ERROR_CODE, self::USER_ID_NOT_FOUND_ERROR_MESSAGE, true);
            }
            
            //return $friendsList;
        } catch (Exception $e) {
             if (is_callable($this->getErrorHook())) {
                return call_user_func_array($this->getErrorHook(), array($this->getErrors()));
            }
        }
    }
    
    /**
     * Get friends online 
     * @return json 
     */
    public function getFriendsOnline() {
        $friendsList = $this->getUserFriendsList();
        $friendUserIds = $friendsList->getUserIds();
        $result = array();

        if (!empty($friendUserIds)) {
            
            $keys = array_map(
                function ($userId) {
                    return $this->formatKey('{:userId}', $userId, self::ONLINE_CACHE_PREFIX_KEY);
                }, 
                $friendUserIds
            );
            
            $redisFactory = $this->getRedisClient();
            $onlineFriends = $redisFactory->doMget($keys);

            $onlineUsers = array_filter(
                array_combine(
                    $friendUserIds,
                    $onlineFriends
                )
            );

            if ($onlineUsers) {
                $friendsList->setOnline($onlineUsers);
            }
            
            $result = $friendsList->toArray();
        }

        return json_encode($result);

    }
   /*
    * Set an error and thrown a new Exception if it is required
    * @param string $errorCode Exception Error Code
    * @param string $errorMessage Exception Error Message
    * @param bool $thrownException Thrown an Exception if true
    */
    protected function setErrors($errorCode, $errorMessage, $throwException) {
        $this->_errors[] = array ('code' => $errorCode, 'message' => $errorMessage);
        if ($throwException) {
            throw new Exception($errorCode);
        }
    }
   
   /*
    * Get User session
    */
    protected function getSession() {
        return $this->_session;
    }
    
   /*
    * Format redis Key ussing a defined pattern 
    * @return string
    */
    protected function formatKey($token, $value, $pattern) {
        return str_replace($token, $value, $pattern);
    } 
    
   /*
    * Get Errors 
    * @return array
    */
    public function getErrors() {
        return $this->_errors;
    }
    
}
