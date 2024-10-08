<?php

class Bootstrap {

    private $_url = null;
    private $_controller = null;
    private $_errorID = null;
    private $_errorMsgDetail = null;

    function __construct() {

        $this->_GetURL();
        $this->_loadController();
        if (isset($this->_url[1])) {
            $this->_loadMethod();
        }
    }

    private function _getURL() {
        $url = isset($_GET['url']) ? $_GET['url'] : null;
        $url = rtrim($url, '/');
        $url = filter_var($url, FILTER_SANITIZE_URL);
        $this->_url = explode('/', $url);
    }

    private function _loadController() {
        if (empty($this->_url[0])) {
            require 'controllers/index.php';
            $this->_controller = new Index();
            $this->_controller->LoadModel('index');
            $this->_controller->index();
            return false;
        } else {
            $file = 'controllers/' . $this->_url[0] . '.php';
            if (file_exists($file)) {
                require $file;
                $this->_controller = new $this->_url[0];
                $this->_controller->LoadModel($this->_url[0]);
                if(empty($this->_url[1])){
                    $this->_controller->index();
                }
            } else {
                $this->_errorID = 1;
                $this->_errorMsgDetail = 'The File '.$file.' does not exist';
                $this->_error();
                return false;
            }
        }
    }

    private function _loadMethod() {
        if (isset($this->_url[2])) {
            if (method_exists($this->_controller, $this->_url[1])) {
                $this->_controller->{$this->_url[1]}($this->_url[2], $this->_url[3], $this->_url[4]);
            } else {
                $this->_errorID = 2;
                $this->_errorMsgDetail = 'The Method '.$this->_url[1].' does not exist in '.$this->_url[0];
                $this->_error();
            }
        } else {
            if (isset($this->_url[1])) {
                if (method_exists($this->_controller, $this->_url[1])) {
                    $this->_controller->{$this->_url[1]}();
                } else {
                    $this->_errorID = 3;
                    $this->_errorMsgDetail = 'The Method '.$this->_url[1].' does not exist in '.$this->_url[0];
                    $this->_error();
                }
            }
        }
    }

    private function _error() {
        require 'controllers/errors.php';
        $this->_controller = new Errors();
        $this->_controller->index($this->_errorID, $this->_errorMsgDetail);
        return false;
    }

}