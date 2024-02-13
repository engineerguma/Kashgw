<?php


class Mtnrwanda extends Controller {

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

        $worker = "Thread_ID_".getmypid().rand(10000 ,99999)."::";
        $xml_request = file_get_contents('php://input');
        if(!empty($xml_request)){

        $this->model->log->LogRequest('req_from_mtn',$worker."Mtnrwanda:  DebitCompleted data ". var_export($xml_request,true),1);

        //release client
        header('Content-Type: text/xml');

        while(ob_get_level())ob_end_clean();
        ignore_user_abort();
        ob_start();
        // Send the response
      $get_http_response_code ='<?xml version="1.0" encoding="UTF-8"?>
      <debitcompletedresponse xmlns="http://www.ericsson.com/em/emm"/>';
        echo $get_http_response_code;
        $size = ob_get_length();
        // Disable compression (in case content length is compressed).
        header("Content-Encoding: none");
        header("Content-Length:".$size);
        // Close the connection.
        header("Connection: close");
        // Flush all output.
        ob_flush();
        flush();
        ob_end_flush();
          if (is_callable('fastcgi_finish_request')) {
        // This works in Nginx but the next approach not
            fastcgi_finish_request();// important when using php-fpm!
            }
  // Close current session (if it exists).
          if (session_id()) {
              session_write_close();
          }

        $req =$this->model->ProcessDebitCompletedRequest($xml_request,'req_from_mtn',$worker);
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
