<?php

class Index_Model extends Model {

    function __construct() {
        parent::__construct();
    }


   function ProcessStatus(){


     $routing = $this->GetOperatorRouting(1,'status');

       $certificate = $this->GetSSLInfo(1);
     $header=['Authorization: Basic '.base64_encode($routing[0]['req_username'].":".$routing[0]['req_password']),
    'Content-Type: application/xml',
    'Accept: application/xml'];
    $xml = '<?xml version="1.0" encoding="UTF-8"?><ns2:gettransactionstatusrequest xmlns:ns2="http://www.ericsson.com/em/emm/financial/v1_0"><referenceid>37</referenceid>
</ns2:gettransactionstatusrequest>';


 return $this->SendTestByCURL($routing[0]['routing_url'],$header,$xml,$certificate[0]);

   }




                 function SendTestByCURL($url,$header,$xml,$cert=false) {
                  //   print_r($header);die();

                   $momo_genID = date("ymdhis");
                   /*  $content= '<?xml version="1.0" encoding="UTF-8"?> <ns0:debitresponse xmlns:ns0="http://www.ericsson.com/em/emm/financial/v1_0"><transactionid>'.$momo_genID.'</transactionid><status>PENDING</status></ns0:debitresponse>';
                        return $content;  */
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
                  // $this->log->LogRequest($log_name,$content,2);
                     }
                     curl_close($ch);
                     //$this->log->LogRequest($log_name,$content,2);
                     //print_r($content);die();
                   return $content;
                   }




}
