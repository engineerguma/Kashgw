<?php


class Airtelrwanda extends Controller {

    function __construct() {
        parent::__construct();

    }

    function Index(){
      $general=array('status'=>403,
                     'message'=>'Forbidden');
        header('Content-Type: application/json;charset=utf-8"');
        echo json_encode($general,true);
        exit();
    }



    function DebitCompleted($req=false){

        $xml_request = file_get_contents('php://input');
        $log_file_name = $this->model->log->LogRequest('req_from_airtel',$xml_request,1);
        $req =$this->model->ProcessDebitCompletedRequest($xml_request,'req_from_airtel');

    }


    function ProcessPendingTransactions(){
        $this->model->ProcessPendingTransactions(1);
    }


}
