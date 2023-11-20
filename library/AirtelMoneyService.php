<?php

// require __DIR__ . '/../vendor/autoload.php';
// use \Firebase\JWT\JWT;

class AirtelMoneyService extends Model {

    function __construct() {
        parent::__construct();
    }


    function HandleOperatorResponse($transaction, $operator_resp,$log_name,$worker) {

      if(isset($operator_resp['operator_reference'])&&strtolower($operator_resp['operator_reference'])=='$txnid'){
        unset($operator_resp['operator_reference']);
      }
      if(isset($operator_resp['transaction_reference_number'])&&strtolower($operator_resp['transaction_reference_number'])=='$exttrid'){
        unset($operator_resp['transaction_reference_number']);
      }
      if(isset($operator_resp['operator_status'])&&strtolower($operator_resp['operator_status'])=='success.'&&$transaction['transaction_type']=='debit'){
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

      if(isset($operator_resp['operator_status'])){
        $error_codes=$this->MatchAirtelRespcodes($operator_resp['operator_status']);
        $response =array_merge($operator_resp,$error_codes);
      }else{
        $this->log->LogRequest($log_name,$worker."HandleOperatorResponse::AirtelMoneyService:  No Operator Status captured ". var_export($request,true),2);
        $response = $operator_resp;

        }

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

    function Debit($transaction,$header,$log_name,$worker){ //CollectionORDebitRequest
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
      $request = $this->SendByCURL(AM_BASE_URL.'/merchant/v1/payments/',$header,$post_data,$log_name,$worker);
      $this->log->LogRequest($log_name,$worker."AirtelMoneyService:  Debit::Response  ". var_export($request,true),2);
      return $request;
    }

    function DebitStatus($transaction,$header,$log_name,$worker){ //DisbursementRequests

     $request = $this->SendByGetCURL(AM_BASE_URL.'/standard/v1/payments/'.$transaction['transaction_reference_number'],$header,$log_name,$worker);
     $this->log->LogRequest($log_name,$worker."AirtelMoneyService:  DebitStatus response ". var_export($request,true),2);

      return $request;
    }

    function Credit($transaction,$header,$log_name,$worker){ //DisbursementRequests
      $hashed_pin= $this->hash_pin($log_name,$worker);
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
     $request = $this->SendByCURL(AM_BASE_URL.'/standard/v1/disbursements/',$header,$post_data,$log_name,$worker);

     $this->log->LogRequest($log_name,$worker."AirtelMoneyService:  Credit::Response  ". var_export($request,true),2);

      return $request;
    }


    function CreditStatus($transaction,$header,$log_name,$worker){ //DisbursementRequests

     $request = $this->SendByGetCURL(AM_BASE_URL.'/standard/v1/disbursements/'.$transaction['transaction_reference_number'],$header,$log_name,$worker);

      return $request;
    }


    function hash_pin($log_name,$worker){

    $sensitiveData= AM_WD_PIN;
      $publicKey = openssl_pkey_get_public(file_get_contents('airtel_public.pem'));
    if (!$publicKey) {
       //echo "Public key NOT Correct  ";
       $this->log->LogRequest($log_name,$worker."AirtelMoneyService::hash_pin  Public key NOT Correct ",2);

    }
    if (!openssl_public_encrypt($sensitiveData, $encryptedWithPublic, $publicKey)) {
       //echo "Error encrypting with public key";
       $this->log->LogRequest($log_name,$worker."AirtelMoneyService::hash_pin Error encrypting with public key",2);
    }
    return base64_encode($encryptedWithPublic);
    }

   function getToken($header,$log_name,$worker){

    $data= array();
    $data['client_id']=AM_CLIENT_ID;
    $data['client_secret']=AM_SECRET;
    $data['grant_type']=AM_GRANT_TYPE;

  $post_data= json_encode($data);

   $request = $this->SendByCURL(AM_BASE_URL.'/auth/oauth2/token',$header,$post_data,$log_name,$worker);
     return $request;
   }




       function GenerateJWToken($request){

               //build the payload
               $issuedAt = time();
               $payload =  [
                "id" =>$this->gen_uuid(), //   .setId(UUID.randomUUID().toString())
                "iat"=> $issuedAt,  //issued at
                "sub"=> SUBJECT,
                "iss"=> ISSUER,  //issuer
                "exp"=> $issuedAt+3600,
                "PAYLOAD"=> $request,  //request
                        ];

               $jwt = JWT::encode($payload,base64_decode(AM_KEY),ALGO);

               return $jwt;
              }


            function base64url_encode($text)
              {
                  return str_replace(
                      ['+', '/', '='],
                      ['-', '_', ''],
                      base64_encode($text)
                  );
              }

       function gen_uuid() {
           return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
               // 32 bits for "time_low"
               mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

               // 16 bits for "time_mid"
               mt_rand( 0, 0xffff ),

               // 16 bits for "time_hi_and_version",
               // four most significant bits holds version number 4
               mt_rand( 0, 0x0fff ) | 0x4000,

               // 16 bits, 8 bits for "clk_seq_hi_res",
               // 8 bits for "clk_seq_low",
               // two most significant bits holds zero and one for variant DCE1.1
               mt_rand( 0, 0x3fff ) | 0x8000,

               // 48 bits for "node"
               mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
           );
       }


}
