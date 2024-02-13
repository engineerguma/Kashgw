<?php

class Merchantpayment extends Controller {

    function __construct() {
        parent::__construct();
    }

    function Index() {
      $general=array('status'=>403,
                     'message'=>'Forbidden');
          header('Content-Type: application/json;charset=utf-8"');
          echo json_encode($general);
    }


    function MakeDebitRequest() {
        $jsonrequest = file_get_contents('php://input');
 	   if(json_decode($jsonrequest) != NULL){
        $postdata = json_decode($jsonrequest,true);
        $postdata['request']='debit';
        $worker = "Thread_ID_".getmypid().rand(10000 ,99999)."::";
        $log_file_name = $this->model->log->LogRequest('req_from_merchant',$worker ."Starting Debit". var_export($jsonrequest,true),1);
        $this->model->ProcessMerchantDebitRequest($postdata, 'req_from_merchant',$worker);
	     }else{
    $general=array('status'=>400,
                   'message'=>'Bad Request');
      echo json_encode($general);
         }
        }



            function USSDDebitRequest() {
                $jsonrequest = file_get_contents('php://input');
         	   if(json_decode($jsonrequest) != NULL){
                $postdata = json_decode($jsonrequest,true);
                $postdata['request']='debit';
                $worker = "Thread_ID_".getmypid().rand(10000 ,99999)."::";
                $log_file_name = $this->model->log->LogRequest('req_from_ussd',$worker ."Starting Debit". var_export($jsonrequest,true),1);
                $this->model->ProcessUSSDDebitRequest($postdata, 'req_from_ussd',$worker);
        	     }else{
            $general=array('status'=>400,
                           'message'=>'Bad Request');
              echo json_encode($general);
                 }
                }


    function MakeCreditRequest() {
        $jsonrequest = file_get_contents('php://input');
 	   if(json_decode($jsonrequest) != NULL){
        $postdata = json_decode($jsonrequest,true);
        $postdata['request']='credit';
        $worker = "Thread_ID_".getmypid().rand(10000 ,99999)."::";
        $log_file_name = $this->model->log->LogRequest('req_from_merchant',$worker ."Starting Credit". var_export($jsonrequest,true),1);
        $this->model->ProcessMerchantCreditRequest($postdata,'req_from_merchant',$worker);
	   }else{
    $general=array('status'=>400,
                   'message'=>'Bad Request');
      echo json_encode($general);
         }
    }


        function AccountInformation() {
            $jsonrequest = file_get_contents('php://input');
     	   if(json_decode($jsonrequest) != NULL){
            $postdata = json_decode($jsonrequest,true);
            $postdata['request']='kyc';
            $worker = "Thread_ID_".getmypid().rand(10000 ,99999)."::";
            $log_file_name = $this->model->log->LogRequest('req_from_merchant', $worker."Starting Credit". var_export($jsonrequest,true),1);
            $this->model->ProcessAccountInformationRequest($postdata,'req_from_merchant',$worker);
    	   }else{
        $general=array('status'=>400,
                       'message'=>'Bad Request');
          echo json_encode($general);
        }
        }


        function checkTransactionStatus(){
          $jsonrequest = file_get_contents('php://input');
   	     if(json_decode($jsonrequest) != NULL){
          $json_data = json_decode($jsonrequest,true);
            $worker = "Thread_ID_".getmypid().rand(10000 ,99999)."::";
         //$this->model->log->LogRequest('req_from_merchant',$jsonrequest,1);
          $response=$this->model->ProcessCheckStatusRequest($json_data, 'req_from_merchant',$worker);

          $json_resp=json_encode($response);
          $this->model->log->LogRequest('req_from_merchant', $worker."Merchant::  checkTransactionStatus  Response to Mrchant ". var_export($response,true),3);

         header('Content-Type: application/json');
         echo $json_resp;
           exit();
       }else{
      header('Content-Type: application/json');
      $general=array('status'=>400,
                     'message'=>'Bad Request');
        echo json_encode($general);
          exit();
           }

        }



}
