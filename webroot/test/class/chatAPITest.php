<?php
include ("test/baseTestClass.php");
include ("class/chatApi.php");

/**
 * Is used as the base class for Zend_Controller test-cases.
 */
class chatAPITest extends Workana_PHPUnit_Base_Framework {
    /**
     * @var array List of all instantiated controller action helpers.
     */
    static $_redisClientMock;

    /**
     * @var array List of all instantiated controller action helpers.
     */
    static $_sessionServiceMock;

    static $_chatAPIMock;
    
    protected $_clasName = 'chatAPI';

    /**
     * test setup
     */
    function setUp() {

        self::$_redisClientMock = $this->getMockBuilder('redisFactory')
            ->setMethods(
                array(
                    'isConnected',
                    'doGet',
                    'doMget'
                )
            )
            ->getMock();

        self::$_sessionServiceMock = $this->getMockBuilder('sessionService')
            ->setMethods(array('getSession'))
            ->getMock();

    }

    /**
     * Testing the contructor of chatAPI Class
     */
    public function testchatAPIConstruct() {
        $redisClientMock = self::$_redisClientMock;
        $sessionServiceMock = self::$_sessionServiceMock;

        $classMock = $this->getMockBuilder($this->_clasName)
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    'setErrorHook',
                    'setSession',
                    'setRedisClient',
                    'getUserFriendListFromService'
                    )
                )
            ->getMock();
            
        $classMock
            ->expects($this->once())
            ->method('setErrorHook');
        
        $classMock
            ->expects($this->once())
            ->method('setSession');
            
        $classMock
            ->expects($this->once())
            ->method('setRedisClient');
            
        $classMock
            ->expects($this->once())
            ->method('getUserFriendListFromService');
        
        $this->callMethod($classMock, '__construct', array($redisClientMock, $sessionServiceMock));
        

    }

    /**
     * Testing the contructor of chatAPI Class
     * @param  boolean  $redisStatus Status of Redis Connection
     * 
     * @dataProvider testSetRedisClientDataProvider
     */
     public function testSetRedisClient($redisStatus = true) {
        $redisClientMock = self::$_redisClientMock;
        
        $redisClientMock->expects($this->once())
            ->method('isConnected')
            ->will($this->returnValue($redisStatus));
       
        $classMock = $this->getMockBuilder($this->_clasName)
            ->disableOriginalConstructor()
            ->setMethods(array('setErrors'))
            ->getMock();
            
        
        if ($redisStatus) {
            $this->callMethod($classMock, 'setRedisClient', array($redisClientMock));
            $redisProperty = $this->getProperty($classMock, '_redisClient');
            $this->assertEquals($redisProperty, $redisClientMock);
        } else {
            $classMock
                ->expects($this->once())
                ->method('setErrors');
            $this->callMethod($classMock, 'setRedisClient', array($redisClientMock));
            $redisProperty = $this->getProperty($classMock, '_redisClient');        
            $this->assertEquals($redisProperty, NULL);
        }
    }

    /**
     * Testing setErrorHook method
     * 
    */
    public function testSetErrorHook() {

        $expected = function(){};

        $classMock = $this->getMockBuilder($this->_clasName)
            ->disableOriginalConstructor()
            ->getMock();
            
        $this->callMethod($classMock, 'setErrorHook', array($expected));
        $property = $this->getProperty($classMock, '_errorHook');
        $this->assertEquals($property, $expected);

    }

    /**
     * Testing getErrorHook method
     * 
    */
    public function testGetErrorHook() {

        $classMock = $this->getMockBuilder($this->_clasName)
            ->disableOriginalConstructor()
            ->getMock();

        $this->setProperty($classMock, '_errorHook', 'TEST');
        $property = $this->callMethod($classMock, 'getErrorHook');
        $this->assertEquals($property, 'TEST');

    }
    
    /**
     * Testing setSession method
     * 
    */
    public function testSetSession() {

        $expected = new stdClass();

        $classMock = $this->getMockBuilder($this->_clasName)
            ->disableOriginalConstructor()
            ->getMock();
            
        $this->callMethod($classMock, 'setSession', array($expected));
        $property = $this->getProperty($classMock, '_session');
        $this->assertEquals($property, $expected);

    }
    
    /**
     * Testing getSession method
     * 
    */
    public function testGetSession() {
        
        $redisClientMock = self::$_redisClientMock;
        $sessionServiceMock = self::$_sessionServiceMock;

        $chatAPI = new chatAPI($redisClientMock, $sessionServiceMock);

        $this->setProperty($chatAPI, '_session', 'TEST');
        $property = $this->callMethod($chatAPI, 'getSession');
        $this->assertEquals($property, 'TEST');

    }
    
    /**
     * Testing setUserFriendsList method
     * 
    */
    public function testSetUserFriendsList() {

        $expected = new stdClass();

        $classMock = $this->getMockBuilder($this->_clasName)
            ->disableOriginalConstructor()
            ->getMock();
            
        $this->callMethod($classMock, 'setUserFriendsList', array($expected));
        $property = $this->getProperty($classMock, '_friendList');
        $this->assertEquals($property, $expected);

    }
    
    /**
     * Testing getUserFriendsList method
     * 
    */
    public function testGetUserFriendsList() {

        $classMock = $this->getMockBuilder($this->_clasName)
            ->disableOriginalConstructor()
            ->getMock();

        $this->setProperty($classMock, '_friendList', 'TEST');
        $property = $this->callMethod($classMock, 'getUserFriendsList');
        $this->assertEquals($property, 'TEST');

    }

    /**
     * Testing getUserFriendListFromService method
     * @param  array  $session Session data
     * 
     * @dataProvider testGetUserFriendListFromServiceDataProvider
    */
    public function testGetUserFriendListFromService($session = null) {
        
        $redisClientMock = self::$_redisClientMock;

        $classMock = $this->getMockBuilder($this->_clasName)
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    'getSession',
                    'getRedisClient',
                    'formatKey',
                    'setUserFriendsList',
                    'setErrors'
                )
            )
            ->getMock();

        $classMock
                ->expects($this->once())
                ->method('getSession')
                ->will($this->returnValue($session));
        
        if (!empty($session['default']['id'])) {
            
            $redisClientMock
                ->expects($this->once())
                ->method('doGet');
                
            $classMock
                ->expects($this->once())
                ->method('getRedisClient')
                ->will($this->returnValue($redisClientMock));
            
            $classMock
                ->expects($this->once())
                ->method('formatKey');
            
            $classMock
                ->expects($this->once())
                ->method('setUserFriendsList');
        } else {
            $classMock
                ->expects($this->once())
                ->method('setErrors');
        }

        $this->callMethod($classMock, 'getUserFriendListFromService');

    }

    /**
     * Testing getUserFriendListFromService method
     * @param  array  $friendUserIds Users ids
     * @param  array  $expected Expected Response
     *
     * @dataProvider testGetFriendsOnlineDataProvider
    */
    public function testGetFriendsOnline($friendUserIds = array(), $expected = array()) {

        $onlineUsers = array( 0 => true);
        
        

        $redisClientMock = self::$_redisClientMock;

        $classMock = $this->getMockBuilder($this->_clasName)
            ->disableOriginalConstructor()
            ->setMethods(
                array(
                    'getUserFriendsList',
                    'getUserIds',
                    'formatKey',
                    'getRedisClient'
                )
            )
            ->getMock();

        $friendsList =  $this->getMockBuilder('Object')
            ->setMethods(
                array(
                    'getUserIds',
                    'setOnline',
                    'toArray'
                )
            )
            ->getMock();
            
       $friendsList
            ->expects($this->once())
            ->method('getUserIds')
            ->will($this->returnValue($friendUserIds));
        
        $classMock
            ->expects($this->once())
            ->method('getUserFriendsList')
            ->will($this->returnValue($friendsList));
                
       if (!empty($friendUserIds)) {

            $redisClientMock
                ->expects($this->once())
                ->method('doMget')
                ->will($this->returnValue($onlineUsers));

           $classMock
                    ->expects($this->once())
                    ->method('getRedisClient')
                    ->will($this->returnValue($redisClientMock));

           $classMock
                    ->expects($this->exactly(count($friendUserIds)))
                    ->method('formatKey');
           
           $friendsList
                    ->expects($this->once())
                    ->method('setOnline');
           
           $friendsList
                    ->expects($this->once())
                    ->method('toArray')
                    ->will($this->returnValue($expected));
                    
           $result = $this->callMethod($classMock, 'getFriendsOnline');
           
           $this->assertEquals($result, json_encode($expected));
        } else {
            $result = $this->callMethod($classMock, 'getFriendsOnline');
            $this->assertEquals($result, json_encode($expected));
        }

    }

    function testSetRedisClientDataProvider() {
         return array(            
            array('redisStatus' => false),
            array('redisStatus' => true)
            );
    }
    
    function testGetUserFriendListFromServiceDataProvider() {
         $session = array('default' => array('id' => '---ID---'));
         return array(            
            array('session' => $session),
            array('session' => null)
            );
    }
    
    function testGetFriendsOnlineDataProvider() {
         $friendUserIds = array('176733');
         return array(            
            array('friendUserIds' => $friendUserIds, 'expected' => array('---ARRAY---')),
            array('friendUserIds' => null)
            );
    }

}
