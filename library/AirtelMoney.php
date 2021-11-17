<?php


class AirtelMoney extends Model {

    function __construct() {
        parent::__construct();
    }


    function HandleOperatorResponse($transaction, $operator_resp,$log_name) {

      if(isset($operator_resp['operator_reference'])&&strtolower($operator_resp['operator_reference'])=='$txnid'){
        unset($operator_resp['operator_reference']);
      }
      if(isset($operator_resp['transaction_reference_number'])&&strtolower($operator_resp['transaction_reference_number'])=='$exttrid'){
        unset($operator_resp['transaction_reference_number']);
      }
      if(isset($operator_resp['operator_status'])&&strtolower($operator_resp['operator_status'])=='success'&&$transaction['transaction_type']=='debit'){
         $operator_resp['operator_status']='pending';
      }
      if(isset($operator_resp['operator_status'])&&strtolower($operator_resp['operator_status'])=='tip'&&$transaction['transaction_type']=='debit'){
         $operator_resp['operator_status']='pending';
      }
      if(isset($operator_resp['operator_reference'])&&isset($operator_resp['reference_id'])&&$transaction['transaction_type']=='credit'&&$operator_resp['operator_reference']!=''){
         $operator_resp['operator_reference']=$operator_resp['reference_id'];
         $operator_resp['operator_status']='successful';
      }
      if(isset($operator_resp['operator_status'])&&strtolower($operator_resp['operator_status'])=='ts'&&isset($operator_resp['operator_reference'])){
         $operator_resp['operator_status']='successful';
      }
      $error_codes=$this->MatchAirtelRespcodes($operator_resp['operator_status']);

      $response =array_merge($operator_resp,$error_codes);
        return $response;
    }


    function MatchAirtelRespcodes($status){
     $statuscode =array();
    if($status=='successful'){
    $statuscode['transaction_status']='completed';
    $statuscode['status_code']=200;
    }else if($status=='pending'){
      $statuscode['transaction_status']='pending';
      $statuscode['status_code']=202;
    }else{
      $statuscode['transaction_status']='failed';
      $statuscode['status_code']='general_error';
    }

    return $statuscode;
    }

    function Debit($transaction,$header,$log){ //CollectionORDebitRequest
      $data=[
            "reference"=>'GW_REF'.$transaction['transaction_id'],
            "subscriber"=>[
              "country"=>COUNTRY,
              "currency"=>CURRENCY,
              "msisdn"=>substr($transaction['transaction_account'], 3)
            ],
            "transaction"=>[
              "amount"=>$transaction['transaction_amount'],
              "country"=>COUNTRY,
              "currency"=>CURRENCY,
              "id"=>$transaction['transaction_reference_number']
            ]
        ];

      $post_data= json_encode($data);
      $request = $this->SendByCURL(AM_BASE_URL.'/merchant/v1/payments/',$header,$post_data,$log);
      $this->log->LogRequest($log,"AirtelMoney:  Debit::Response  ". var_export($request,true),2);
      return $request;
    }

    function DebitStatus($transaction,$header,$log){ //DisbursementRequests

     $request = $this->SendByGetCURL(AM_BASE_URL.'/standard/v1/payments/'.$transaction['transaction_reference_number'],$header,$log);
     $this->log->LogRequest($log,"AirtelMoney:  DebitStatus response ". var_export($request,true),2);

      return $request;
    }

    function Credit($transaction,$header,$log){ //DisbursementRequests
      $hashed_pin= $this->hash_pin($log);
   $data=[
        "payee"=>[
          "msisdn"=> substr($transaction['transaction_account'], 3)
        ],
        "reference"=> 'PALM_'.$transaction['transaction_id'],
        "pin"=> $hashed_pin,
        "transaction"=> [
          "amount"=>  $transaction['transaction_amount'],
          "id"=> $transaction['transaction_reference_number']
        ]
      ];

      $post_data= json_encode($data);
     $request = $this->SendByCURL(AM_BASE_URL.'/standard/v1/disbursements/',$header,$post_data,$log);

     $this->log->LogRequest($log,"AirtelMoney:  Credit::Response  ". var_export($request,true),2);

      return $request;
    }


    function CreditStatus($transaction,$header,$log){ //DisbursementRequests

     $request = $this->SendByGetCURL(AM_BASE_URL.'/standard/v1/disbursements/'.$transaction['transaction_reference_number'],$header,$log);

      return $request;
    }


    function hash_pin($log){

    $sensitiveData= AM_WD_PIN;
      $publicKey = openssl_pkey_get_public(file_get_contents('airtel_public.pem'));
    if (!$publicKey) {
       echo "Public key NOT Correct  ";
    }
    if (!openssl_public_encrypt($sensitiveData, $encryptedWithPublic, $publicKey)) {
       echo "Error encrypting with public key";
    }
    return base64_encode($encryptedWithPublic);
    }

   function getToken($header,$log){

    $data= array();
    $data['client_id']=AM_CLIENT_ID;
    $data['client_secret']=AM_SECRET;
    $data['grant_type']=AM_GRANT_TYPE;

  $post_data= json_encode($data);

   $request = $this->SendByCURL(AM_BASE_URL.'/auth/oauth2/token',$header,$post_data,$log);
     return $request;
   }


}
