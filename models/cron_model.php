<?php

class Cron_Model extends GeneralMerchant {

    function __construct() {
        parent::__construct();
    }






            function ProcessCheckStatusRequest($log_name){
        $transactions =$this->GetPendingTransactions();
           if(empty($transactions)==false){
             foreach ($transactions as $key => $value) {
               // code...
               $routing = $this->GetOperatorRouting($value['operator_id'],'status');
               $operator_response= $this->ProcessCallOperator($value,$routing[0],$log_name);
              //close transaction
               if(isset($operator_response['transaction_status'])){
               $this->CloseTransaction($log_name,$value,$operator_response);
                }
               print_r($operator_response);
               //die();


              }

           }

            //return $response;
            }


            function GetPendingTransactions() {
              //$minutes  =30;
              $status_minutes  =STATUS_MINUTES;

              $time_now = date("Y-m-d H:i:s");
              //print_r("SELECT * FROM  `transaction_history` WHERE TIMESTAMPDIFF(MINUTE,`transaction_date` ,'".$time_now."')>='".$status_minutes."' AND transaction_status='pending' LIMIT 10");die();
             return $this->db->SelectData("SELECT * FROM  `transaction_history` WHERE TIMESTAMPDIFF(MINUTE,`transaction_date` ,'".$time_now."')>='".$status_minutes."' AND transaction_status='pending' LIMIT 10");
            }



}
