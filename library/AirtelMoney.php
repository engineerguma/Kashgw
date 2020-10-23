<?php

class AirtelMoney extends Model {

    function __construct() {
        parent::__construct();
    }


    function HandleOperatorResponse($transaction, $operator_resp,$log_name) {

      if(isset($operator_resp['operator_status'])&&$operator_resp['operator_status']==200&&$transaction['transaction_type']=='debit'){
         $operator_resp['operator_status']='pending';
         unset($operator_resp['operator_reference']);
      }
      if(isset($operator_resp['operator_reference'])&&$transaction['transaction_type']=='credit'&&$operator_resp['operator_reference']!=''){
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

}
