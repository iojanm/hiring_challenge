<?php
/*
 * Response Handler
 */
class responseHandler {
    
    const BAD_CONFIGURATION_ERROR_CODE = 500;
    const BAD_CONFIGURATION_MESSAGE = 'Server error, invalid configuration.';
    const INALID_ORIGIN_ERROR_CODE = 500;
    const INALID_ORIGIN_MESSAGE = 'Not a valid origin.';
    
    protected $_headers;
    
    protected $_body;
    
    protected $_allowedDomains;
    
    protected $_allowBlankReferrer;
    
    protected $_httpOrigin;
    
    protected $_errors;
    
    function __construct() {
        $this->setHeader('Content-Type: application/json; charset=utf-8');
        $this->setHttpOrigin($_SERVER['HTTP_ORIGIN']);
        
    }
    
    protected function setErrors($error) {
        $this->_errors[] = $error;
    }
    
    public function getErrors() {
        $this->_errors;
    }
    
    protected function setHttpOrigin($origin = null) {
        $this->_httpOrigin = $origin;
    }
    
    protected function getHttpOrigin() {
        return $this->_httpOrigin;
    }
    
    function setAllowedDomains ($domains) {
        $result = array_map('trim', $domains);
        $this->_allowedDomains = $result;
    }
    
    protected function getAllowedDomains () {
        return $this->_allowedDomains;
    }
    
    public function setHeader ($header) {
        $this->_headers[] = $header;
    }
    
    protected function getHeaders () {
        return $this->_headers;
    }
    
    public function setBody ($body) {
        $this->_body = $body;
    }

    public function setAllowBlankReferer ($allowBlankReferer) {
        $this->_allowBlankReferrer = $allowBlankReferer;
    }
    
    protected function getAllowBlankReferer () {
        return $this->_allowBlankReferrer;
    }
    
    public function setHttpResponseCode($code) {
        http_response_code($code);
    }

    protected function validateValidRequest() {
        if (!$this->getAllowBlankReferer()) {
            $this->validateAllowedDomains();
        }
        $this->setHttpOriginHeader();
        $this->setAllowBlankRefererHeader();
    }
    
    protected function validateAllowedDomains() {
        if (empty($this->getAllowedDomains()) || !is_array($this->getAllowedDomains())) {
            $this->populateErrorResponse(self::BAD_CONFIGURATION_MESSAGE);
            $this->setHttpResponseCode(500);
            $this->setErrors(self::BAD_CONFIGURATION_ERROR_CODE);
        }
        if (!in_array($this->getHttpOrigin(), $this->getAllowedDomains()) && !is_array($this->getErrors())) {
            $this->populateErrorResponse(self::INALID_ORIGIN_MESSAGE);
            $this->setHttpResponseCode(403);
            $this->setErrors(self::INALID_ORIGIN_ERROR_CODE);
        } 
    }
    
    public function setAllowBlankRefererHeader() {
        if ($this->getAllowBlankReferer()) {
            $this->setHeader('Access-Control-Allow-Credentials: true');
        }
    }
    
    protected function setHttpOriginHeader() {
        if ($this->getHttpOrigin()) {
            $this->setHeader("Access-Control-Allow-Origin: ".$this->getHttpOrigin());
            $this->setHeader('Access-Control-Allow-Credentials: true');
        }
    }
    
    protected function populateErrorResponse($message) {
        $errorArray = array('error' => true, 'message' => $message);
        $this->setBody(json_encode($errorArray));
    }
    
    public function dispatchError($code, $message) {
        $this->setHttpResponseCode($code);
        $this->populateErrorResponse($message);
        $this->dispatch();
    }
    
    public function dispatch () {
        $this->validateValidRequest();
        $headers = $this->getHeaders();
        foreach($this->getHeaders() as $header) {
            header($header);
        }
        echo $this->_body;
        exit();
    }

}
