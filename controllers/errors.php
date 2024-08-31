<?php

class Errors extends Controller {

    function __construct() {
        parent::__construct();
    }

    function Index(){
	    $general=array('status'=>403,
                'message'=>'You seem to be lost');
                header("HTTP/1.1 403 Forbidden", true, 403); 
                 exit();
    }


}
