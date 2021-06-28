<?php

class OperatorRequests extends Model {

    function __construct() {
        parent::__construct();
    }


    function ProcessMTNRequests($transaction,$routing,$log_name) {

    //  $this->log->LogRequest($log_name,"OperatorRequests:  ProcessMTNRequests  ". var_export($transaction,true),2);
        $xml = $this->WriteGeneralXMLFile($routing, $transaction,$log_name);

       $header =$this->PrepareBasicAuthHeader($routing);
    //   $this->log->LogRequest($log_name,"OperatorRequests:  ProcessMTNRequests Header  ". var_export($header,true),2);
      // $array =array('cert_key'=>$routing['cert_key'],'ca_authority'=>$routing['ca_authority']);
        $certificate = $this->GetSSLInfo($transaction['operator_id']);
       $result = $this->SendXMLByCURL($routing['routing_url'],$header,$xml,$log_name,$certificate[0]);
    //   $this->log->LogRequest($log_name,"OperatorRequests:  ProcessMTNRequests  SendXMLByCURL response ". var_export($result,true),2);

        $array= $this->map->FormatXMLTOArray($result);
         $response =$this->HandleOperatorResponse($transaction,$array);
        return $response;
    }


    function ProcessAirtelRequests($transaction,$routing,$log_name) {

    //  $this->log->LogRequest($log_name,"OperatorRequests:  ProcessAirtelRequests  ". var_export($transaction,true),2);
        $xml = $this->WriteGeneralXMLFile($routing, $transaction,$log_name);

       $this->Airtel = new AirtelMoney();
       $header=['Content-Type: application/xml','Accept: application/xml'];
       $token = $this->Airtel->GenerateJWToken($xml);
       $auth_header=['Authorization: Bearer '.$token];
       $header = array_merge($header,$auth_header);
      // $this->log->LogRequest($log_name,"OperatorRequests:  ProcessAirtelRequests Header  ". var_export($header,true),2);

       $result = $this->SendXMLByCURL($routing['routing_url'],$header,$xml,$log_name);
       //$this->log->LogRequest($log_name,"OperatorRequests:  ProcessAirtelRequests   SendXMLByCURL response ". var_export($result,true),2);

        $array= $this->map->FormatXMLTOArray($result);

      //  $this->log->LogRequest($log_name,"OperatorRequests:  ProcessAirtelRequests  Converted Arry ". var_export($array,true),2);

      //  print_r($array);die();
        $this->Airtel = new AirtelMoney();
        $response=  $this->Airtel->HandleOperatorResponse($transaction,$array,$log_name);

        return $response;
      }


     function PrepareBasicAuthHeader($credentials){

       $header=['Authorization: Basic '.base64_encode($credentials['req_username'].":".$credentials['req_password']),
      'Content-Type: application/xml',
      'Accept: application/xml'];
       return $header;
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



              function SendXMLByCURL($url,$header,$xml,$log_name,$cert=false) {
                  //print_r($cert);die();
                $momo_genID = date("ymdhis");
                /*  $content= '<?xml version="1.0" encoding="UTF-8"?> <ns0:debitresponse xmlns:ns0="http://www.ericsson.com/em/emm/financial/v1_0"><transactionid>'.$momo_genID.'</transactionid><status>PENDING</status></ns0:debitresponse>';
                     return $content;  */
                $this->log->LogRequest($log_name,"OperatorRequests:  SendByCURL  beginning url ".$url." Xml". var_export($xml,true),2);
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
                curl_setopt($ch, CURLOPT_URL,$url);
                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
                if($cert!=false){
                curl_setopt($ch, CURLOPT_FOLLOWLOCATION,true);
                curl_setopt($ch, CURLOPT_SSLVERSION, 6);
                curl_setopt($ch, CURLOPT_SSLCERT, CERT_PATH.$cert['cert_key'].".crt");
                curl_setopt($ch, CURLOPT_SSLKEY, CERT_PATH.$cert['cert_key'].".key" );
                curl_setopt($ch, CURLOPT_CAINFO, CERT_PATH.$cert['ca_authority'].".crt");
                }
                $content = curl_exec($ch);
                if (curl_errno($ch) > 0) {
                $content= curl_error($ch);
                $this->log->LogRequest($log_name,$content,2);
                  }
                  curl_close($ch);
                  //$this->log->LogRequest($log_name,$content,2);
                  //print_r($content);die();
                return $content;
                }




}
