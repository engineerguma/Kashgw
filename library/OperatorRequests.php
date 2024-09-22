<?php

class OperatorRequests extends Model {

    function __construct() {
        parent::__construct();
    }


    function ProcessMTNRequests($transaction,$routing,$log_name,$worker) {
        $response = array();
    //  $this->log->LogRequest($log_name,$worker."OperatorRequests:  ProcessMTNRequests  ". var_export($transaction,true),2);
        $xml = $this->WriteGeneralXMLFile($routing, $transaction,$log_name,$worker);

       $header =$this->PrepareBasicAuthHeader($routing);
    //   $this->log->LogRequest($log_name,$worker."OperatorRequests:  ProcessMTNRequests Header  ". var_export($header,true),2);
      // $array =array('cert_key'=>$routing['cert_key'],'ca_authority'=>$routing['ca_authority']);
        $certificate = $this->GetSSLInfo($transaction['operator_id']);
       $result = $this->SendXMLByCURL($routing['routing_url'],$header,$xml,$log_name,$worker,$certificate[0]);
       $this->log->LogRequest($log_name,$worker."OperatorRequests:  ProcessMTNRequests  SendXMLByCURL response ". var_export($result,true),2);
        $validatexml = $this->isXml($result,$log_name,$worker);
        //print_r($validatexml);die();
        if($validatexml==true){
        $array= $this->map->FormatXMLTOArray($result);
         $response =$this->HandleOperatorResponse($transaction,$array,$log_name,$worker);
       }else{
         //unknown_status
         $response['status_code'] = 'network_error';
         $response['transaction_status']='failed';
         $response['status_description'] = "Communication failure to payment operator";

       }
        return $response;
    }


    function ProcessAirtelRequests($transaction,$routing,$log_name,$worker) {

      //$this->log->LogRequest($log_name,$worker."OperatorRequests:  ProcessAirtelRequests  ". var_export($transaction,true),2);

       $this->Airtel = new AirtelMoneyService();
       $header=['Content-Type: application/json',
       'Accept: application/json',
        ];

       $token = $this->Airtel->getToken($header,$log_name,$worker);
       $token = json_decode($token,true);

        if(isset($token['access_token'])){
       $auth_header=['X-Country: '.COUNTRY,
         'X-Currency: '.CURRENCY,
         'Authorization: Bearer '.$token['access_token']];
       $header = array_merge($header,$auth_header);

      // $this->log->LogRequest($log_name,$worker."OperatorRequests:  ProcessAirtelRequests Header  ". var_export($header,true),2);
       if(isset($routing['status'])&&$routing['status']==1){
      $req_data = $this->Airtel->{ucfirst($transaction['transaction_type']).'Status'}($transaction,$header,$log_name,$worker);
        }else{
      $req_data = $this->Airtel->{$transaction['transaction_type']}($transaction,$header,$log_name,$worker);

        }

      // $this->log->LogRequest($log_name,$worker."OperatorRequests:  ProcessAirtelRequests   SendXMLByCURL response ". var_export($result,true),2);
        $array= $this->map->FormatJSONtoArray($req_data);
    //    print_r($array);die();

      //  $this->log->LogRequest($log_name,$worker."OperatorRequests:  ProcessAirtelRequests  Converted Arry ". var_export($array,true),2);
      $response=  $this->Airtel->HandleOperatorResponse($transaction,$array,$log_name,$worker);


    }else{  //token request failed

      if(isset($routing['status'])&&$routing['status']==1){
        $response=0; // for failed token status
       }else{

     $array['operator_status']='token_error';
     $response=  $this->Airtel->HandleOperatorResponse($transaction,$array,$log_name,$worker);

       }

    }
  $this->log->LogRequest($log_name,$worker."HandleOperatorResponse:  ProcessAirtelRequests   ". var_export($response,true),2);

        return $response;
      }


     function PrepareBasicAuthHeader($credentials){

       $header=['Authorization: Basic '.base64_encode($credentials['req_username'].":".$credentials['req_password']),
      'Content-Type: application/xml',
      'Accept: application/xml'];
       return $header;
     }


    function HandleOperatorResponse($transaction, $operator_resp,$log_name,$worker) {

              if(isset($operator_resp['operator_reference'])&&$transaction['transaction_type']=='credit'&&$operator_resp['operator_reference']!=''){
                 $operator_resp['operator_status']='successful';
              }

          if(isset($operator_resp['operator_status'])){
            $error_codes=$this->MatchOPeratorRespcodes($operator_resp['operator_status']);
            $combined =array_merge($operator_resp,$error_codes);
          }else{
    $this->log->LogRequest($log_name,$worker."OperatorRequests::HandleOperatorResponse:  No operator status  ". var_export($operator_resp,true),2);
           $combined = $operator_resp;

            }

          //print_r($combined);die();
        return $combined;
        }

        function isXml(string $value,$log_name,$worker): bool
        {
            $prev = libxml_use_internal_errors(true);

            $doc = simplexml_load_string($value);
            $errors = libxml_get_errors();
       //     $this->log->LogRequest($log_name,$worker."OperatorRequests::isXml: simplexml_load_string ". var_export($doc,true),2);
          
       //     $this->log->LogRequest($log_name,$worker."OperatorRequests::isXml: libxml_get_errors ". var_export($errors,true),2);

            libxml_clear_errors();
            libxml_use_internal_errors($prev);
                                                                                                                                                                 
            return false !== $doc && empty($errors);
          }

              function SendXMLByCURL($url,$header,$xml,$log_name,$worker,$cert=false) {
                  //print_r($cert);die();
                $momo_genID = date("ymdhis");
                /*  $content= '<?xml version="1.0" encoding="UTF-8"?> <ns0:debitresponse xmlns:ns0="http://www.ericsson.com/em/emm/financial/v1_0"><transactionid>'.$momo_genID.'</transactionid><status>PENDING</status></ns0:debitresponse>';
                     return $content;  */
                $this->log->LogRequest($log_name,$worker."OperatorRequests:  SendByCURL  beginning url ".$url." Xml". var_export($xml,true),2);
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
                $this->log->LogRequest($log_name,$worker,$content,2);
                  }
                  curl_close($ch);
                  //$this->log->LogRequest($log_name,$worker,$content,2);
                  //print_r($content);die();
                return $content;
                }




}
