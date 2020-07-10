<?php

class Index extends Controller {

    function __construct() {
        parent::__construct();
    }

    function Index(){
	    $general=array('status'=>403,
                     'message'=>'Forbidden');
        echo json_encode($general);
    }
}
