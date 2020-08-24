<?php

class GeneralMerchant extends Model {

    function __construct() {
        parent::__construct();
    }

     function ProcessCallOperator($transaction,$routing,$log_name){

            $this->operator = new OperatorRequests();
          return  $this->operator->{$routing['operator_process']}($transaction,$routing,$log_name);
     }

    function MerchantHandler($post_data,$log_name) {

      $this->log->LogRequest($log_name,"GeneralMerchant:  MerchantHandler ". var_export($post_data,true),2);

        //Check Whether the transaction Is Duplicate:
        $res = $this->VerifyMerchantReference($post_data['transaction_reference_number']);
      //  print_r($res);die();
        if (count($res) > 0) {
            //$this->ResendPaymentResponse($res[0]['transaction_id']);
            $response =array();
            $response["transaction_status"]='failed';
            $response["status_description"]="Duplicate transaction reference number ".$post_data['transaction_reference_number'];
            $response["status_code"]='duplicate_transactiom_reference_number';
            $json_resp =json_encode($response);
            $this->log->LogRequest($log_name,"GeneralMerchant:  Duplicate transaction reference number ". var_export($json_resp,true),3);

            header('Content-Type: application/json');
            echo $json_resp;
            exit();
        }
        //Post Transaction To DB:
        $data = $this->PrepareToSaveMerchData($post_data);

        $this->log->LogRequest($log_name,"GeneralMerchant:  PrepareToSaveMerchData  ". var_export($data,true),2);

        $trans_id = $this->SaveTransactionRecord($data);

        //Get The Transaction For Future Use:
        $transaction = $this->GetTransaction($trans_id);
        $this->log->LogRequest($log_name,"GeneralMerchant:  Transaction record  ". var_export($transaction,true),2);
     /*
      $pending_resp=$this->PrepareMerchantResponse($transaction[0]);
      while(ob_get_level())ob_end_clean();
      ignore_user_abort();
      ob_start();
      header('Content-Type: application/json');
      echo $pending_resp;
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
      //This works in Nginx but the next approach not
          fastcgi_finish_request();// important when using php-fpm!
          }
    // Close current session (if it exists).
        if (session_id()) {
            session_write_close();
        }

         */
        //Make Request To Merchant Application & Process the Merchant Results
           $trans_data=array_merge($transaction[0],$post_data);
        $operator_response = $this->ProcessOperatorRequest($trans_data,$log_name);

        $this->log->LogRequest($log_name,"GeneralMerchant:  ProcessOperatorRequest Response  ". var_export($operator_response,true),2);

        //Wrap Up Transaction Processing And Prepare & Send Service Provider Response:
        $this->CloseTransaction($log_name,$transaction[0],$operator_response);
        $transact = $this->GetTransaction($trans_id);

      //  $this->log->LogRequest($log_name,"GeneralMerchant:  HandleOperatorResponse ". var_export($trans_resp_array,true),2);

        if($transact[0]['transaction_status']=='failed'||$transact[0]['transaction_type']=='credit'){
         $this->SendMerchantCompletedRequest($transact[0],$log_name);
        }

        $this->log->LogRequest($log_name,"GeneralMerchant:  Completed Processing ",3);

       exit();
    }



    function ProcessOperatorRequest($transaction,$log_name) {
      $response =array();
      $this->log->LogRequest($log_name,"GeneralMerchant:  ProcessOperatorRequest  ". var_export($transaction,true),2);
      $routing_permissions= $this->GetMerchantRoutingPermissions($transaction['operator_id'],$transaction['merchant_id'],$transaction['transaction_type']);
      //print_r($routing_permissions);die();
      if(empty($routing_permissions)==false){
      $routing = $this->GetOperatorRouting($transaction['operator_id'],$transaction['transaction_type']);
      if(empty($routing)==false){
         //print_r($transaction);die();
         $this->log->LogRequest($log_name,"GeneralMerchant:  ProcessOperatorRequest::GetOperatorRouting  ". var_export($routing,true),2);
        $result= $this->ProcessCallOperator($transaction,$routing[0],$log_name);

        return $result;
        }else{ //Has no Routing permissions
          $response['transaction_status']='failed';
      		$response['status_code']='operator_routing_not_set';
          $response['status_description']="Operator routing for Merchant ".$transaction['merchant_account']." is not set.";
         return $response;
        }

          }else{  //Has no Routing permissions
            $response['transaction_status']='failed';
        		$response['status_code']='no_operator_routing_permissions';
            $response['status_description']="Merchant  ".$transaction['merchant_account']." has no permissions to send request to Operator";
           return $response;
            }
    }



     function PrepareBasicAuthHeader($credentials){

       $header=['Authorization: Basic '.base64_encode($processing_rules['service_user_id'].":".$processing_rules['service_api_key']),
      'Content-Type: application/xml',
      'Accept: application/xml'];
       return $header;
     }


    function HandleOperatorResponse($transaction, $operator_resp) {

          if(isset($operator_resp['operator_reference'])&&$transaction['transaction_type']=='credit'&&$operator_resp['operator_reference']!=''){
           $operator_resp['operator_status']='successful';
          }
          $error_codes=$this->MatchOPeratorRespcodes($operator_resp['operator_status']);
          $combined =array_merge($operator_resp,$error_codes);
          //print_r($combined);die();
        return $combined;
      }


    function PrepareMerchantResponse($transaction){

          $array =array(
           "transaction_reference_number"=>$transaction['transaction_reference_number'],
           "gateway_reference"=>$transaction['transaction_id'],
           "transaction_status"=>$transaction['transaction_status'],
           "status_code"=>$transaction['status_code'],
          );
      return json_encode($array);
    }


    function PrepareResponseMsg($vndr, $merc_resp, $transaction) {
    $message = $merc_resp['aggreg_resp_message'];
    if($merc_resp['aggreg_resp_code'] == 100){
        $final_text = str_replace('[PAY_REF]', $transaction[0]['transaction_id'], $message);
    }
    else
    {
        $final_text = $message;
    }
    $this->log->LogToFile($vndr, "GeneralMerchant::PrepareResponseMsg:  Returning Message " . $final_text, 2, 1);
    $merc_final = $merc_resp;
    $merc_final['aggreg_resp_message']=$final_text;

    return $merc_final;
     }



    function ValidateToken($data){

      $token =$data['token'];
       unset($data['token']);
       unset($data['type']);
      $token =base64_decode($token);
       	   //print_r($token);die();
      $privKey = openssl_pkey_get_private(file_get_contents('private.pem'));
      $decryptedData = "";
      openssl_private_decrypt($token, $decryptedData, $privKey);
      $string ='';
      foreach ($data as $key => $value) {
        $string .=$value;
      }
     if(strcmp($decryptedData,$string)){
     return 1;

     }else{
    return 0;
     }

    }

}
