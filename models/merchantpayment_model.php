<?php

class Merchantpayment_Model extends GeneralMerchant {

    function __construct() {
        parent::__construct();
    }

    /*
     * Core Merchant Functions
     */

    function ProcessMerchantDebitRequest($req_data,$log_name) {
	    //print_r($req_data);die();
    $pay_array = $this->PrepareRequest($req_data);
    $validate=$this->validate->ValidateDebit($pay_array);
       if(empty($validate)){
   //$verify =$this->ValidateToken($req_data);
    $verify =1;
    if($verify==1){

   $stan_array=$this->map->Standardize($pay_array);
      if(isset($stan_array['error'])==false){

      $merchant =$this->FindMerchant($stan_array);
      if(empty($merchant)==false){
      $stan_array['merchant_id']=$merchant[0]['merchant_id'];;

      $prefix =$this->GetOperatorByPrefix($stan_array['transaction_account']);
      if(empty($prefix)==false){
        $stan_array['operator_id']=$prefix[0]['operator_id'];
      //  $operator =$this->FindOPerator($stan_array);
      //  if(empty($operator)==false){
      //  $stan_array['operator_id']=$operator[0]['operator_id'];
    $this->log->LogRequest($log_name,"MerchantModel:  ProcessMerchantDebitRequest". var_export($prefix,true),2);
       $rout_extension='debit';
      $this->MerchantHandler($stan_array,$rout_extension,$log_name);
      /* }else{
       $this->RespondError("operator Account ".$stan_array['operator']." was not found",401,$log_name);
      } */

        }else{
       $this->RespondError("Operator not supported for ".$stan_array['transaction_account']." ",401,$log_name);
        }

        }else{
       $this->RespondError("Mechant Account ".$stan_array['merchant_account']." was not found",401,$log_name);
        }

          }else{
      $this->RespondError($stan_array['error'],401,$log_name);
          }

          }else{

      $this->RespondError("Compromised requst data",400,$log_name);
          }


        }else{
            $this->RespondError($validate,400,$log_name);
        }

    }

    function ProcessUSSDDebitRequest($req_data,$log_name) {
	    //print_r($req_data);die()
    $pay_array = $this->PrepareRequest($req_data);
    $validate=$this->validate->ValidateUSSDDebit($pay_array);
       if(empty($validate)){
   //$verify =$this->ValidateToken($req_data);
    $verify =1;
    if($verify==1){

   $stan_array=$this->map->Standardize($pay_array);
      if(isset($stan_array['error'])==false){

        $merchant =$this->FindMerchant($stan_array);
        if(empty($merchant)==false){
        $stan_array['merchant_id']=$merchant[0]['merchant_id'];;
        $operator =$this->GetOperatorByPrefix($stan_array['transaction_account']);
     if(empty($operator)==false){
        $stan_array['operator_id']=$operator[0]['operator_id'];
          //  print_r($stan_array);die();
    $this->log->LogRequest($log_name,"MerchantModel:  ProcessUSSDDebitRequest". var_export($merchant,true),2);
       $rout_extension='debit';
      $this->MerchantHandler($stan_array,$rout_extension,$log_name);
        }else{
       $this->RespondError("operator Account ".$stan_array['operator']." was not found",401,$log_name);
        }

        }else{
       $this->RespondError("Mechant Account ".$stan_array['merchant_account']." was not found",401,$log_name);
        }

          }else{
      $this->RespondError($stan_array['error'],401,$log_name);
          }

          }else{

                 $this->RespondError("Compromised requst data",400,$log_name);
          }

        }else{
            $this->RespondError($validate,400,$log_name);
        }


    }

    function ProcessMerchantCreditRequest($req_data,$log_name) {
	//print_r($xmlp);
    $pay_array = $this->PrepareRequest($req_data);
   $validate=$this->validate->ValidateCredit($pay_array);
   if(empty($validate)){
    //$verify =$this->ValidateToken($req_data);
    $verify=1;
    if($verify==1){

   $stan_array=$this->map->Standardize($pay_array);
   if(isset($stan_array['error'])==false){

   $merchant =$this->FindMerchant($stan_array);
   if(empty($merchant)==false){
   $stan_array['merchant_id']=$merchant[0]['merchant_id'];;
   $prefix =$this->GetOperatorByPrefix($stan_array['transaction_account']);
   if(empty($prefix)==false){

   $stan_array['operator_id']=$prefix[0]['operator_id'];
  $this->log->LogRequest($log_name,"MerchantModel:  ProcessMerchantCreditRequest ". var_export($merchant,true),2);
       $rout_extension='credit';
      $this->MerchantHandler($stan_array,$rout_extension,$log_name);

        }else{
       $this->RespondError("Operator not supported for ".$stan_array['transaction_account'],401,$log_name);
        }

        }else{
       $this->RespondError("Mechant Account ".$stan_array['merchant_account']." was not found",401,$log_name);
        }

       }else{
            $this->RespondError($stan_array['error'],401,$log_name);
      }

      }else{

           $this->RespondError("Compromised requst data",400,$log_name);
      }

       }else{
            $this->RespondError($validate,400,$log_name);
        }


    }


    function RespondError($error,$error_code,$log_name){
      $response=array();
      $response["transaction_status"]='failed';
      $response["status_code"]=$error_code;
      $response["description"]=$error;
      $json_resp =json_encode($response);
      $this->log->LogRequest($log_name,"MerchantModel:  RespondError message ". var_export($json_resp,true),3);

      header('Content-Type: application/json');
      echo $json_resp;
      exit();
       }

    function PrepareRequest($paydata) {
      $map_data =$this->map->ParseJSONParameters($paydata);
        return $map_data;
    }


          function ProcessCheckStatusRequest($req_data,$log_name){

              $pay_array = $this->PrepareRequest($req_data);
              $validate=$this->validate->ValidateCheckStatus($pay_array);
               if(empty($validate)){
              //$verify =$this->ValidateToken($req_data);
              $verify=1;
              if($verify==1){

               $stan_array=$this->map->Standardize($pay_array);

                $merchant =$this->FindMerchant($stan_array);
             /////
                  if(empty($merchant)==false){
                 $res = $this->VerifyMerchantReference($stan_array['transaction_reference_number'],$merchant[0]['merchant_id']);
                  $response =array();
                    if(empty($res)==false){
                   $transaction = $this->GetTransaction($res[0]['transaction_id']);

                   $post =array();
                     if($transaction[0]['operator_reference']!=''){
                    $response["operator_reference"]=$transaction[0]['operator_reference'];
                     }
                     $response["transaction_reference_number"]=$transaction[0]['transaction_reference_number'];
                     $response["gateway_reference"]=$transaction[0]['transaction_id'];
                     $response["transaction_status"]=$transaction[0]['transaction_status'];
                     $response["status_code"]=$transaction[0]['status_code'];
                   }else{
                     $response["status_code"]=401;
                     $response["description"]="transaction_reference_number ".$stan_array['transaction_reference_number']." was not found";
                   }

                   }else{
                     $response["status_code"]=401;
                     $response["description"]="Mechant Account ".$stan_array['merchant_account']." was not found";
                   }
                //print_r($response);die();

                    }else{

                      $this->RespondError("Compromised requst data",400,$log_name);
                    }

                   }else{
                     $response["status_code"]=400;
                     $response["description"]=$validate;
                   }

               $this->log->LogRequest($log_name,"Merchant Payment Model:  ProcessCheckStatusRequest  Response". var_export($response,true),2);

            return $response;
            }

            function ProcessAccountInformationRequest($req_data,$log_name){

              $pay_array = $this->PrepareRequest($req_data);
               $validate=$this->validate->ValidateAccountInformation($pay_array);
               if(empty($validate)){
              //$verify =$this->ValidateToken($req_data);
              $verify=1;
              if($verify==1){

               $stan_array=$this->map->Standardize($pay_array);

                  $merchant =$this->FindMerchant($stan_array);
                  if(empty($merchant)==false){
                  $stan_array['merchant_id']=$merchant[0]['merchant_id'];;
                  $this->log->LogRequest($log_name,"MerchantModel:  ProcessAccountInformationRequest Merchant ". var_export($prefix,true),2);

                  $prefix =$this->GetOperatorByPrefix($stan_array['transaction_account']);
                  if(empty($prefix)==false){

                $res = $this->VerifyMerchantReference($stan_array['transaction_reference_number'],$stan_array['merchant_id']);
                  $response =array();
                    if(empty($res)==false){
                   $transaction = $this->GetTransaction($res[0]['transaction_id']);

                   $post =array();
                     if($transaction[0]['operator_reference']!=''){
                    $response["operator_reference"]=$transaction[0]['operator_reference'];
                     }
                     $response["transaction_reference_number"]=$transaction[0]['transaction_reference_number'];
                     $response["gateway_reference"]=$transaction[0]['transaction_id'];
                     $response["transaction_status"]=$transaction[0]['transaction_status'];
                     $response["status_code"]=$transaction[0]['status_code'];
                   }else{
                     $response["status_code"]=401;
                     $response["description"]="transaction_reference_number ".$stan_array['transaction_reference_number']." was not found";
                   }

                   }else{
                     $response["status_code"]=401;
                     $response["description"]="Payment Operator not supported ".$stan_array['merchant_account']." was not found";
                   }

                   }else{
                     $response["status_code"]=401;
                     $response["description"]="Mechant Account ".$stan_array['merchant_account']." was not found";
                   }

                }else{

                    $this->RespondError("Compromised requst data",400,$log_name);
                }

                }else{
                     $response["status_code"]=400;
                     $response["description"]=$validate;
                }


               $this->log->LogRequest($log_name,"Merchant Payment Model:  ProcessAccountInformationRequest  Response". var_export($response,true),2);

            return $response;
            }

    function FindMerchant($pay_array) {

       $merchant=$pay_array['merchant_account'];
        $res = $this->db->SelectData("SELECT * FROM merchant_accounts WHERE merchant_code ='".$merchant."'");

      //  $merc_id = $res[0]['merchant_id'];
        return $res;
    }


    function FindOPerator($pay_array) {
       $operator=$pay_array['operator'];
        $res = $this->db->SelectData("SELECT * FROM payment_operators WHERE operator_code ='".$operator."'");

      //  $op_id = $res[0]['operator_id'];
        return $res;
    }


}
