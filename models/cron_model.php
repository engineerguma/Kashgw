<?php

class Cron_Model extends GeneralMerchant {

    function __construct() {
        parent::__construct();
    }


            function ProcessCheckStatusRequest($log_name,$worker){
              $transactions =$this->GetPendingTransactions();
             if(empty($transactions)==false){
               foreach ($transactions as $key => $value) {
                 // code...
                 $routing = $this->GetOperatorRouting($value['operator_id'],'status');
                 if(empty($routing)==false){
                  $routing=$routing[0];
                  $routing['status']=1;
            $operator_response= $this->ProcessCallOperator($value,$routing,$log_name,$worker);

            if(isset($operator_response['status_code'])&&strtolower($operator_response['status_code'])!='network_error'){

                //close transaction
                if(isset($operator_response['operator_reference'])&&strtolower($operator_response['operator_reference'])=='na'){
                  unset($operator_response['operator_reference']);
                }
                $this->log->LogRequest($log_name,$worker."CronModel::ProcessCallOperator response ".var_export($operator_response, true), 2, 2);

                 if(isset($operator_response['transaction_status'])&&strtolower($operator_response['transaction_status'])!='pending'){
                 $this->CloseTransaction($log_name,$worker,$value,$operator_response);
                 $transact = $this->GetTransaction($value['transaction_id']);

              $this->log->LogRequest($log_name,$worker."CronModel::PrepareTOCloseTransaction closed transaction ".var_export($transact[0], true), 2, 3);

              if($transact[0]['transaction_source']=='ussd'){
                if($transact[0]['transaction_status']=='completed'){
                  //change routing_type to posting
                $this->SendMerchantCompletedRequest($transact[0],$log_name,$worker);
                  }
                //exit();
              }else{

               $this->SendMerchantCompletedRequest($transact[0],$log_name,$worker);

                }

                }

              }else{ //end of network_error vheck


              }



                }


              } //end of foreach

           }
             exit();
            //return $response;
            }


            function GetPendingTransactions() {
              //$minutes  =30;
              $status_minutes  =STATUS_MINUTES;

              $time_now = date("Y-m-d H:i:s");
            //  print_r("SELECT * FROM  `transaction_histories` WHERE TIMESTAMPDIFF(MINUTE,`transaction_date` ,'".$time_now."')>='".$status_minutes."' AND transaction_status='pending' LIMIT 10");die();
             return $this->db->SelectData("SELECT * FROM  `transaction_histories` WHERE TIMESTAMPDIFF(MINUTE,`transaction_date` ,'".$time_now."')>='".$status_minutes."' AND transaction_status='pending' LIMIT 10");
            }



}
