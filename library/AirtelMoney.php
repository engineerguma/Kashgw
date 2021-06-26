<?php

require __DIR__ . '/../vendor/autoload.php';
use \Firebase\JWT\JWT;
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
      if(isset($operator_resp['operator_status'])&&$operator_resp['operator_status']==200&&$transaction['transaction_type']=='debit'&&isset($operator_resp['type'])){
         $operator_resp['operator_status']='pending';
      }
      /*if(isset($operator_resp['operator_reference'])&&$transaction['transaction_type']=='credit'&&$operator_resp['operator_reference']!=''){
         $operator_resp['operator_status']='successful';
      }*/
      if(isset($operator_resp['operator_status'])&&$operator_resp['operator_status']==200&&isset($operator_resp['operator_reference'])){
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
