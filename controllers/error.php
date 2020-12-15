<?php

class Error extends Controller {

    function __construct() {
        parent::__construct();
    }

    function Index(){
	    $general=array('status'=>404,
                'message'=>'End point is not found on the server');
        echo json_encode($general);
    }


}
