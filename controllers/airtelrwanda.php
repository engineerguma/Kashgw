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
         /*
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
        ob_end_flush();
        ob_flush();
        flush();

          if (is_callable('fastcgi_finish_request')) {
        // This works in Nginx but the next approach not
            fastcgi_finish_request();// important when using php-fpm!
            }
  // Close current session (if it exists).
          if (session_id()) {
              session_write_close();
          }*/

        $req =$this->model->ProcessDebitCompletedRequest($xml_request,'req_from_airtel');

    }


    function ProcessPendingTransactions(){
        $this->model->ProcessPendingTransactions(1);
    }


}
