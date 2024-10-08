<?php

class Mapping {

    public $_match_up_array = array(

        'token' => 'encryption_token',
        'request' => 'transaction_type',
        'payment_operator' => 'operator',
        'transaction_amount' => 'transaction_amount',
        'account_number' => 'account_number',
        'transaction_account' => 'transaction_account',
        'transaction_reference_number' => 'transaction_reference_number',
        'merchant_account' => 'merchant_account',
        'transaction_source' => 'transaction_source',
        'transaction_destination' => 'transaction_destination',
        'transaction_reason' => 'transaction_reason',
        'currency' => 'transaction_currency',
        'apikey' => 'api_key',
        'first_name' => 'first_name',
        'last_name' => 'last_name',
        'full_name' => 'full_name',
        'cvv' => 'cvv',
        'email' => 'email',
        'expiry_month' => 'expiry_month',
        'expiry_year' => 'expiry_year',
        'exp' => 'exp', //for JWT
    );

    public $_match_up_operator_params = array(

        'transactionid' => 'operator_reference',
        'externaltransactionid' => 'transaction_reference_number',
        'status' => 'operator_status',
        'fri' => 'fri',
        'errorcode' => 'operator_status',
        'code' => 'status_code',
        'name' => 'error_name',
        'value' => 'error_value',
        ##from Airtel
        'command' => 'command',
        'txnstatus' => 'operator_status',
        'success' => 'success',
        'message' => 'status_description',
        'result_code' => 'result_code',
        'response_code' => 'response_code',
        'txnid' => 'operator_reference',
        'airtel_money_id' => 'operator_reference',
        'reference_id' => 'reference_id',
        'exttrid' => 'transaction_reference_number',
        'id' => 'transaction_reference_number',
        'status_code' => 'operator_status',
        'extra' => 'extra',
        'type' => 'type',
    );

    function __construct() {

    }


        function ParseJSONParameters($data_array)
        {
            $request_array = $this->ArrayFlattener($data_array);
            return $request_array;
        }


        function StandardizeOperatorParams($data_array) {
                    //Convert to Single
                    $result_array = array();
                    foreach ($data_array as $key => $value) {
                        $standard_key = $this->_match_up_operator_params[strtolower($key)];
                    // print_r($value);die();
                        if (!empty($standard_key)) {
                            $result_array[$standard_key] = $value;
                        }
                    }
                    return $result_array;
          }



              function FormatXMLTOArray($xml){
                  $doc = new DOMDocument();
                  libxml_use_internal_errors(true);
                  $doc->loadHTML($xml);
                  libxml_clear_errors();
                  $xmln = $doc->saveXML($doc->documentElement);
                  $object = simplexml_load_string($xmln);
                  $array = $this->ObjectToArray($object);
                   $f_array = $this->ArrayFlattener($array);
                  $stan=$this->StandardizeOperatorParams($f_array);
               return $stan;
              }


              function FormatJSONtoArray($json_data){
                    $array= json_decode($json_data,true);
                    if(isset($array['data']['transaction'])){
                    //unset($array['status']);
                    } //only for Airtel
                   $f_array = $this->ArrayFlattener($array);
                  //  print_r($f_array);die();
                  $stan=$this->StandardizeOperatorParams($f_array);
               return $stan;
              }



    function Standardize($data_array) {
        //Convert to Single
        $errors = array();
             //$errors[$key] = 'Missing Parameter '.$key;
         $result_array = array();
         foreach ($data_array as $key => $value) {
            $standard_key = $this->_match_up_array[$key];
            if($standard_key==''){
              $errors['error'] = 'Un known Parameter '.$key;
             return $errors;
            }
            if (!empty($standard_key)) {
                $result_array[$standard_key] = $value;
            }
        }
        return $result_array;
    }

    function ObjectToArray($obj) {
        if (!is_array($obj) && !is_object($obj))
            return $obj;
        if (is_object($obj))
            $obj = get_object_vars($obj);
        return array_map(__METHOD__, $obj);
    }

    function ArrayFlattener($array) {
        if (!is_array($array)) {
            return FALSE;
        }
        $result = array();
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $result = array_merge($result, $this->ArrayFlattener($value));
            } else {
                $result[$key] = $value;

            }
        }

        return $result;
    }

}
