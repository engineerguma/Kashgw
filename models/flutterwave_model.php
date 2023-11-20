<?php

class Flutterwave_Model extends Model {

    function __construct() {
        parent::__construct();
    }






function processPayment($req_data){

//5531886652142950
$pay_array = $this->PrepareRequest($req_data);
//print_r($pay_array);die();
$validate=$this->validate->ValidateCardPayment($pay_array);

$card=$this->ReduceCardNumber($pay_array['transaction_account']);
print_r($card);die();
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
$this->log->LogRequest($log_name,$worker."MerchantModel:  ProcessMerchantDebitRequest". var_export($prefix,true),2);
   $rout_extension='debit';
  $this->MerchantHandler($stan_array,$rout_extension,$log_name,$worker);
  /* }else{
   $this->RespondError("operator Account ".$stan_array['operator']." was not found",401,$log_name,$worker);
  } */

    }else{
   $this->RespondError("Operator not supported for ".$stan_array['transaction_account']." ",401,$log_name,$worker);
    }

    }else{
   $this->RespondError("Mechant Account ".$stan_array['merchant_account']." was not found",401,$log_name,$worker);
    }

      }else{
  $this->RespondError($stan_array['error'],401,$log_name,$worker);
      }

      }else{

  $this->RespondError("Compromised requst data",400,$log_name,$worker);
      }


    }else{
        $this->RespondError($validate,400,$log_name,$worker);
    }

/* "card_number": "5531886652142950",
"cvv": "564",
"expiry_month": "09",
"expiry_year": "32",
"currency": "RWF",
"amount": "1000",
"email": "bguma@palmkash.com",
"fullname": "Baguma Test",
"tx_ref": "TX-32345",
"redirect_url":"https://your-awesome.app/payment-redirect"
}*/

}



function processPinAuth(array $pindata){

  $pindata =array("authorization"=> [
     "mode"=> "pin",
      "pin"=> "3310"]);

}




function process3DSRedirect(){

  $authUrl = $response['meta']['authorization']['redirect'];
  return redirect($authUrl);
}



function processAVSAuth(array $avs_data){

  $avs_data =array("authorization"=>[
"mode" =>"avs_noauth",
"city" =>  "San Francisco",
"address" =>  "69 Fremont Street",
 "state"=>  "CA",
 "country"=>  "US",
"zipcode"=>  "94105"]
);

}


function ReduceCardNumber($acco_numm) {
  $last_four =substr($acco_numm, -4);
  $to_remove =substr($acco_numm,0, -4);

  //print_r($to_remove);die();
  $text='';
  for($i=0; $i< strlen($to_remove); $i++){
  $text  .="X";
  }
  $text = $text.$last_four;
  return $text;
}


function PrepareRequest($paydata) {
  $map_data =$this->map->Standardize($paydata);
    return $map_data;
}



    function RespondError($error,$error_code,$log_name,$worker){
      $response=array();
      $response["transaction_status"]='failed';
      $response["status_code"]=$error_code;
      $response["description"]=$error;
      $json_resp =json_encode($response);
      $this->log->LogRequest($log_name,$worker."MerchantModel:  RespondError message ". var_export($json_resp,true),3);

      header('Content-Type: application/json');
      echo $json_resp;
      exit();
       }

}
