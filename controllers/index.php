<?php

class Index extends Controller {

    function __construct() {
        parent::__construct();
    }

    function Index(){
	    $general=array('status'=>403,
                     'message'=>'Palmkash GW Forbidden Access');
        echo json_encode($general);
    }


        function status(){
          $general=array('status'=>403,
                    'message'=>'Palmkash GW Forbidden Access');
     print_r(json_encode($general));die();
       echo $this->model->ProcessStatus();

        }
}
