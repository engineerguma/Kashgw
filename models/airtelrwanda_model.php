<?php

class Airtelrwanda_Model extends GeneralOperator {

    function __construct() {
        parent::__construct();
    }


    function ProcessDebitCompletedRequest($req_data,$log_name){

            $req_array = $this->map->FormatXMLTOArray($req_data);
            //print_r($req_array);die();
            $this->log->LogRequest($log_name,"AirtelrwandaModel:  ProcessDebitCompletedRequest data ". var_export($req_array,true),2);

      $transaction = $this->VerifyMerchantReference($req_array['transaction_reference_number']);
          if(!empty($transaction)&&$transaction[0]['transaction_status']=='pending'){
            //release client
            header('Content-Type: text/xml');

            while(ob_get_level())ob_end_clean();
            ignore_user_abort();
            ob_start();
            // Send the response
          $get_http_response_code ='<COMMAND>
    <TYPE>CALLBCKRESP</TYPE>
    <TXNID>'.$req_array['operator_reference'].'</TXNID>
    <EXTTRID>'.$req_array['transaction_reference_number'].'</EXTTRID>
    <TXNSTATUS>200</TXNSTATUS>
    <MESSAGE>Airtel transaction Id: '.$req_array['operator_reference'].' has been
    processed successfully.</MESSAGE>
    </COMMAND>';
            echo $get_http_response_code;
            $size = ob_get_length();
            // Disable compression (in case content length is compressed).
            header("Content-Encoding: none");
            header("Content-Length:".$size);
            // Close the connection.
            header("Connection: close");
            // Flush all output.
            ob_end_flush();
            ob_flush();
            flush();

              if (is_callable('fastcgi_finish_request')) {
            // This works in Nginx but the next approach not
                fastcgi_finish_request();// important when using php-fpm!
                }


                if($req_array['operator_status']==200){
                $req_array['operator_status']='successful';
                }
      $error_codes=$this->MatchOPeratorRespcodes($req_array['operator_status']);
      $combined =array_merge($req_array,$error_codes);
     $this->log->LogRequest($log_name,"AirtelrwandaModel:  ProcessDebitCompletedRequest log merged data". var_export($combined,true),2);
       $this->OperatorHandler($combined,$transaction,$log_name);
                }else{

               echo '<COMMAND><TYPE>CALLBCKRESP</TYPE>
         <TXNID>'.$req_array['transaction_reference_number'].'</TXNID>
         <EXTTRID>null</EXTTRID>
         <TXNSTATUS>404</TXNSTATUS>
         <MESSAGE>Airtel transaction Id: '.$req_array['operator_reference'].' has not been
         processed successfully.</MESSAGE>
         </COMMAND>';

        $this->log->LogRequest($log_name,"AirtelrwandaModel:  ProcessDebitCompletedRequest exited reference not found ",3);
                die();
                   }

	     }



}
