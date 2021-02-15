<?php

class Validator{


 function ValidateUSSDDebit($data){
   $required = array();
   $required['token'] = 'required';
   $required['transaction_amount'] = 'required';
   $required['account_number'] = 'required';
   $required['transaction_account'] = 'required';
   $required['transaction_reference_number'] = 'required';
   $required['merchant_account'] = 'required';
   $required['currency'] = 'required';

   $errors=$this->loopData($data,$required);
  return $errors;
 }

 function ValidateDebit($data){
   $required = array();
   $required['token'] = 'required';
  // $required['payment_operator'] = 'required';
   $required['transaction_amount'] = 'required';
   $required['account_number'] = 'required';
   $required['transaction_account'] = 'required';
   $required['transaction_reference_number'] = 'required';
   $required['merchant_account'] = 'required';
   $required['transaction_reason'] = 'required';
   $required['currency'] = 'required';

   $errors=$this->loopData($data,$required);
  return $errors;
 }

 function ValidateCredit($data){
   $required = array();
   $required['token'] = 'required';
  // $required['payment_operator'] = 'required';
   $required['transaction_amount'] = 'required';
   $required['account_number'] = 'required';
   $required['transaction_account'] = 'required';
   $required['transaction_reference_number'] = 'required';
   $required['merchant_account'] = 'required';
   $required['transaction_reason'] = 'required';
   $required['currency'] = 'required';
   $required['first_name'] = 'required';
   $required['last_name'] = 'required';

   $errors=$this->loopData($data,$required);
  return $errors;
 }

 function ValidateCheckStatus($data){
   $required = array();
   $required['token'] = 'required';
   $required['transaction_account'] = 'required';
   $required['transaction_reference_number'] = 'required';
   $required['merchant_account'] = 'required';

   $errors=$this->loopData($data,$required);
  return $errors;
 }




 function loopData($data,$required){

 $errors = array();

   foreach($required as $key => $value) {

      if(!array_key_exists($key, $data)) {
			//$errors[$key] = 'Missing Parameter '.$key;
			$errors['Missing Parameter'] = $key;
       }
    }

   foreach($data as $key => $value) {

      if(array_key_exists($key, $required)) {
         if(trim($value) === '') {
            // $errors[$key] = $key.'must not be empty';
             $errors[$key] ='must not be empty';
          }
       }

    }

	return $errors;
 }




}
