<?php

class GeneralOperator extends Model {

    function __construct() {
        parent::__construct();
    }


    function OperatorHandler($post_data,$transaction,$log_name,$worker){
        //Check Whether the transaction Is Duplicate:

        $this->log->LogRequest($log_name,$worker."GeneralOperator:  HandleOperatorResponse ". var_export($post_data,true),2);
         unset($post_data['fri']);
         unset($post_data['transaction_reference_number']);
        //Wrap Up Transaction Processing And Prepare & Send Service Provider Response:
        $this->CloseTransaction($log_name,$worker,$transaction[0],$post_data);
        $transaction = $this->GetTransaction($transaction[0]['transaction_id']);
        if($transaction[0]['transaction_source']=='ussd'){
          if($transaction[0]['transaction_status']=='completed'){
            //change routing_type to posting
          $this->SendMerchantCompletedRequest($transaction[0],$log_name,$worker);
            }
          exit();
        }
         $this->SendMerchantCompletedRequest($transaction[0],$log_name,$worker);
    }



    function FormatXMLTOArray($xml){
        $doc = new DOMDocument();
        libxml_use_internal_errors(true);
        $doc->loadHTML($xml);
        libxml_clear_errors();
        $xmln = $doc->saveXML($doc->documentElement);
        $object = simplexml_load_string($xmln);
        $array = $this->map->ObjectToArray($object);
         $f_array = $this->map->ArrayFlattener($array);
        $stan=$this->map->StandardizeOperatorParams($f_array);
     return $stan;
    }

    function ProcessOperatorRequest($transaction,$log_name,$worker) {

      $this->log->LogRequest($log_name,$worker."GeneralOperator:  ProcessOperatorRequest  ". var_export($transaction,true),2);

        $routing = $this->GetOperatorRouting($transaction['operator_id'],$transaction['transaction_type']);
         //print_r($transaction);die();
         $this->log->LogRequest($log_name,$worker."GeneralOperator:  ProcessOperatorRequest::GetOperatorRouting  ". var_export($routing,true),2);

        $xml = $this->WriteGeneralXMLFile($routing[0], $transaction,$log_name,$worker);

        $result = $this->SendByCURL($routing[0]['routing_url'], $xml,$log_name,$worker);

        return $result;
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



}
