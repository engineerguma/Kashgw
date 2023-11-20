<?php

class Mtnrwanda_Model extends GeneralOperator {

    function __construct() {
        parent::__construct();
    }


    function ProcessDebitCompletedRequest($req_data,$log_name,$worker){

            $req_array = $this->map->FormatXMLTOArray($req_data);
            $this->log->LogRequest($log_name,$worker."MtnrwandaModel:  ProcessDebitCompletedRequest data ". var_export($req_array,true),2);

      $transaction = $this->getMerchantReference($req_array['transaction_reference_number']);

               if(count($transaction)>0){
      $error_codes=$this->MatchOPeratorRespcodes($req_array['operator_status']);
      $combined =array_merge($req_array,$error_codes);
     $this->log->LogRequest($log_name,$worker."MtnrwandaModel:  ProcessDebitCompletedRequest log merged data". var_export($combined,true),2);
       $this->OperatorHandler($combined,$transaction,$log_name,$worker);
                }else{
        $this->log->LogRequest($log_name,$worker."MtnrwandaModel:  ProcessDebitCompletedRequest exited reference not found ",2);

                   }

	     }



}
