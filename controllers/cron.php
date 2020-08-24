<?php

class Cron extends Controller {

    function __construct() {
        parent::__construct();
    }

    function Index() {
      $general=array('status'=>403,
                     'message'=>'Forbidden');
          header('Content-Type: application/json;charset=utf-8"');
          echo json_encode($general);
    }




        function checkTransactionStatus(){
     $response=$this->model->ProcessCheckStatusRequest('status_check');

        }



}
