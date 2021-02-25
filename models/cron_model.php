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

               if(empty($routing)==false){

               $operator_response= $this->ProcessCallOperator($value,$routing[0],$log_name);
              //close transaction

               if(isset($operator_response['transaction_status'])){
               $this->CloseTransaction($log_name,$value,$operator_response);
               $transact = $this->GetTransaction($value['transaction_id']);

              $this->log->LogRequest($log_name, "CronModel::PrepareTOCloseTransaction closed transaction ".var_export($transact[0], true), 2, 3);


               $this->SendMerchantCompletedRequest($transact[0],$log_name);

                }

                }


              }

           }
             exit();
            //return $response;
            }


            function GetPendingTransactions() {
              //$minutes  =30;
              $status_minutes  =STATUS_MINUTES;

              $time_now = date("Y-m-d H:i:s");
              //print_r("SELECT * FROM  `transaction_histories` WHERE TIMESTAMPDIFF(MINUTE,`transaction_date` ,'".$time_now."')>='".$status_minutes."' AND transaction_status='pending' LIMIT 10");die();
             return $this->db->SelectData("SELECT * FROM  `transaction_histories` WHERE TIMESTAMPDIFF(MINUTE,`transaction_date` ,'".$time_now."')>='".$status_minutes."' AND transaction_status='pending' LIMIT 10");
            }



}
