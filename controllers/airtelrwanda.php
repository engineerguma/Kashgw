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

        $request = file_get_contents('php://input');
        if(!empty($request)){
        $worker = "Thread_ID_".getmypid()."::";
        $this->model->log->LogRequest('req_from_airtel',$worker."Airtelrwanda:  DebitCompleted data ". var_export($request,true),1);
        $req =$this->model->ProcessDebitCompletedRequest($request,'req_from_airtel',$worker);
      }else{
        $general=array('status'=>403,
                       'message'=>'Seems you are not authorized');
          header('Content-Type: application/json;charset=utf-8"');
          echo json_encode($general,true);
          exit();
      }
     }


    function ProcessPendingTransactions(){
      $general=array('status'=>404,
                     'message'=>'Requested resource is nolonger available');
        header('Content-Type: application/json;charset=utf-8"');
        echo json_encode($general,true);
        exit();
    }


}
