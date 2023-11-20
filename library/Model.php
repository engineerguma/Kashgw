<?php

class Model {

    function __construct() {
        $this->db = new Databaseconfig();
        $this->log = new Logs();
        $this->validate = new Validator();
        $this->map = new Mapping();

    }


    function ParseRequest($xml_post,$level=false) {
        $standard_array = $this->stan->ParseXMLRequest($xml_post,$level);
        return $standard_array;
    }


    function PrepareToSaveMerchData($data) {
            $now = date('Y-m-d H:i:s');

            $postData = array();
   //print_r($data);die();
            $postData['transaction_date'] = $now;
            $postData['transaction_type'] = $data['transaction_type'];
            $postData['transaction_amount'] = $data['transaction_amount'];
            $postData['account_number'] = $data['account_number'];
            $postData['transaction_account'] = $data['transaction_account'];
            $postData['transaction_reference_number'] = $data['transaction_reference_number'];
            if(isset($data['merchant_id'])){
              $postData['merchant_id'] = $data['merchant_id'];
            }
            if(isset($data['operator_id'])){
            $postData['operator_id'] = $data['operator_id'];
            }

            $postData['transaction_source'] = $data['transaction_source'];
            $postData['transaction_reason'] = $data['transaction_reason'];
            $postData['currency'] = $data['transaction_currency'];
            $postData['transaction_status'] = 'pending';

          //   print_r($postData);die();
        return $postData;
    }

   function SaveTransactionRecord($data){
           //print_r($data);die();
     $trans_id = $this->db->InsertData("transaction_histories", $data, 'transaction_id');
     return $trans_id;
   }

    function VerifyOperatorReference($reference) {
        $res = $this->db->SelectData("SELECT transaction_id,merchant_id,operator_reference,transaction_type FROM transaction_histories WHERE operator_reference=:of", array('of' => $reference));
        return $res;
    }

    function GetOperatorByID($op_id) {
        $res = $this->db->SelectData("SELECT operator_name FROM payment_operators WHERE operator_id=:op", array('op' => $op_id));
        return $res;
    }


    function VerifyMerchantReference($reference,$merchant_id) {
      //  $res = $this->db->SelectData("SELECT transaction_id,merchant_id,transaction_account  FROM transaction_histories WHERE merchant_trans_ref=:mr", array('mr' => $reference));
        $res = $this->db->SelectData("SELECT transaction_id,merchant_id,transaction_account,transaction_type,transaction_status  FROM transaction_histories WHERE transaction_reference_number=:tr and merchant_id=:merchant_id ", array('tr' => $reference,'merchant_id'=>$merchant_id));
        return $res;
    }

    function getMerchantReference($reference) {
      //  $res = $this->db->SelectData("SELECT transaction_id,merchant_id,transaction_account  FROM transaction_histories WHERE merchant_trans_ref=:mr", array('mr' => $reference));
        $res = $this->db->SelectData("SELECT transaction_id,merchant_id,transaction_account,transaction_type,transaction_status  FROM transaction_histories WHERE transaction_reference_number=:tr", array('tr' => $reference));
        return $res;
    }



      function GetSSLInfo($opid) {
            return $this->db->SelectData("SELECT * FROM ssl_connections WHERE operator_id=:opid", array('opid' => $opid));
        }



      function GetTransaction($tid) {
            return $this->db->SelectData("SELECT * FROM transaction_histories WHERE transaction_id=:tid", array('tid' => $tid));
        }


   function GetOperatorRouting($opid, $rt) {

        $result = $this->db->SelectData("SELECT * FROM payment_operator_routings WHERE operator_id=:opID AND routing_type=:rt AND routing_status=:status" ,
                array('opID' => $opid, 'rt' => $rt, 'status' =>'active'));

        return $result;
    }


   function GetMerchantRoutingPermissions($opid, $merc_id,$rt) {

        $result = $this->db->SelectData("SELECT * FROM merchant_operator_routings WHERE operator_id=:opID AND merchant_id=:MerchId AND routing_type=:rt AND routing_status=:status" ,
                array('opID' => $opid, 'MerchId' => $merc_id, 'rt' => $rt, 'status' =>'active'));

        return $result;
    }


   function GetMerchantRouting($merc_id, $rt) {

        $result = $this->db->SelectData("SELECT * FROM merchant_routings WHERE merchant_id=:mID AND routing_type=:rt" ,
                array('mID' => $merc_id, 'rt' => $rt));

        return $result;
    }


   function GetOperatorByPrefix($msisdn) {
       $prefix = substr($msisdn, -(strlen($msisdn)), 5);
        $result = $this->db->SelectData("SELECT * FROM operator_prefixes p JOIN payment_operators o ON p.operator_id=o.operator_id WHERE prefix='".$prefix."'");

        return $result;
    }



   function GetOperatorPendingTransactions($op_id) {

        $results = $this->db->SelectData("SELECT * FROM transaction_histories WHERE operator_id=:id AND transaction_status=:status" ,
                array('id' => $op_id, 'status'=>'pending'));

        return $results;
    }



        function CloseTransaction($log_name,$worker,$transaction,$update_data) {

          $this->log->LogRequest($log_name,$worker."Model:  CloseTransaction ". var_export($update_data,true),2);
          $postData =array();

          $postData['transaction_status']=$update_data['transaction_status'];
          $postData['status_code']=$update_data['status_code'];
          if(isset($update_data['operator_status'])){
            $postData['operator_status']=$update_data['operator_status'];
          }
          if(isset($update_data['status_description'])){
            $postData['transaction_description']=$update_data['status_description'];
          }
          if(isset($update_data['operator_reference'])){
            $postData['operator_reference']=$update_data['operator_reference'];
          }
          //print_r($postData);die();
        try{

      //    $this->log->LogRequest($log_name,$worker."Model:  CloseTransaction Data to update for ID ".$transaction['transaction_id']." Data ". var_export($postData,true),2);

        $return = $this->db->UpdateData('transaction_histories', $postData, "transaction_id = {$transaction['transaction_id']}");

        }catch(Exception $e){
          $this->log->LogRequest($log_name,$worker."Model:  CloseTransaction Exception error  ". var_export($e,true),2);

        }

          }


        function SendMerchantCompletedRequest($transaction,$log_name,$worker){
            $extra_headers= [];
          $this->log->LogRequest($log_name,$worker."Model:  SendMerchantCompletedRequest transaction data ". var_export($transaction,true),2);

            $routing = $this->GetMerchantRouting($transaction['merchant_id'],$transaction['transaction_type'].'_callback');

            $this->log->LogRequest($log_name,$worker."Model:  SendMerchantCompletedRequest Routing data ". var_export($routing,true),2);
           $post =array();
             if(isset($transaction['operator_reference'])&&$transaction['operator_reference']!=''){
            $post["operator_reference"]=$transaction['operator_reference'];
             }
             if(isset($transaction['transaction_reference_number'])&&$transaction['transaction_reference_number']!=''){
            $post["transaction_reference_number"]=$transaction['transaction_reference_number'];
             }
             $operator = $this->GetOperatorByID($transaction['operator_id']);
             $post["operator_name"]=$operator[0]['operator_name'];
             $post["account_number"]=$transaction['account_number'];
             $post["gateway_reference"]=$transaction['transaction_id'];
             $post["transaction_amount"]=$transaction['transaction_amount'];
             $post["transaction_status"]=$transaction['transaction_status'];
             if(isset($transaction['transaction_description'])&&$transaction['transaction_description']!=''){
            $post["transaction_description"]=$transaction['transaction_description'];
             }
             $post["transaction_account"]=$transaction['transaction_account'];
             $post["status_code"]=$transaction['status_code'];
             if(isset($routing[0]['token'])&&$routing[0]['token']!=''){
              if($routing[0]['auth_type']=='bearer'){
                $extra_headers= ['Authorization: Bearer '.$routing[0]['token']];
              }else if($routing[0]['auth_type']=='basic'){
                $extra_headers= ['Authorization: Basic '.$routing[0]['token']];
              }else{
              $post["token"]=$routing[0]['token'];
               }
             }

           $this->log->LogRequest($log_name,$worker."Model:  SendMerchantCompletedRequest ". var_export($post,true),2);

              $header= [
                'Content-Type: application/json',
                'Accept: application/json',
                'cache-control: no-cache',
                'Content-Length: ' . strlen(json_encode($post)),
                      ];
                        $header = array_merge($header,$extra_headers);
            // print_r($header);die();
            $response_xml = $this->SendMerchByCURL($routing[0]['routing_url'],json_encode($post),$header,$log_name,$worker);
             echo $response_xml;
             $this->log->LogRequest($log_name,$worker."Model:  SendMerchantCompletedRequest  Exited Initial Request",3);

        }



            function SendMerchByCURL($url, $post_data,$header,$log_name,$worker) {

              $this->log->LogRequest($log_name,$worker."Model:  SendMerchByCURL to .".$url."  data to send". var_export($post_data,true),2);

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST,"POST");
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

                $content = curl_exec($ch);
                if (curl_errno($ch) > 0) {
                 $error= curl_error($ch);
                 $this->log->LogRequest($log_name,$worker."Model: SendMerchByCURL CURL ERROR  ". var_export($error,true),2);
                 }
        		    curl_close($ch);
                $this->log->LogRequest($log_name,$worker."Model:  Response Data  ". var_export($content,true),2);

                return $content;
            }


            function WriteGeneralXMLFile($routing, $trans_data,$log_name,$worker) {
        	     $template = $routing['request_template'];
                   $trans_data['routing']=$routing;

                $req_template = 'templates/operators/' . $template . '.php';
                require($req_template);
                $filled_xml = ${$template};

                $this->log->LogRequest($log_name,$worker."Model:  WriteGeneralXMLFile  ". var_export($filled_xml,true),2);
//print_r($filled_xml);die();
                return $filled_xml;
            }


            function SendByGetCURL($url, $header,$log_name,$worker) {


                      $this->log->LogRequest($log_name,$worker."Model:  SendByGetCURL  beginning url ".$url,2);
                      $ch = curl_init();
                      curl_setopt($ch, CURLOPT_HEADER, 0);
                      curl_setopt($ch, CURLOPT_VERBOSE, 0);
                      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                      curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
                      curl_setopt($ch, CURLOPT_URL,$url);
                      curl_setopt($ch, CURLOPT_FOLLOWLOCATION,true);
                      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
                      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
                      curl_setopt($ch, CURLOPT_SSLVERSION, 6);
                      $content = curl_exec($ch);
                        if (curl_errno($ch) > 0) {
                         $content= curl_error($ch);
                         $this->log->LogRequest($log_name,$worker.$content,2);
                         }
                		    curl_close($ch);

                        return $content;
          }



            function SendByCURL($url, $header,$request,$log_name,$worker) {

                  //  $momo_genID = date("ymdhis");

                      $this->log->LogRequest($log_name,$worker."Model:  SendByCURL  beginning url ".$url." Xml". var_export($request,true),2);
                      $ch = curl_init();
                      curl_setopt($ch, CURLOPT_HEADER, 0);
                      curl_setopt($ch, CURLOPT_VERBOSE, 0);
                      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                      curl_setopt($ch, CURLOPT_HTTPHEADER,$header);
                      curl_setopt($ch, CURLOPT_URL,$url);
                      curl_setopt($ch, CURLOPT_POST, 1);
                      curl_setopt($ch, CURLOPT_POSTFIELDS, $request);
                      curl_setopt($ch, CURLOPT_FOLLOWLOCATION,true);
                      curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
                      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
                      curl_setopt($ch, CURLOPT_SSLVERSION, 6);
                      $content = curl_exec($ch);
                        if (curl_errno($ch) > 0) {
                         $content= curl_error($ch);
                         $this->log->LogRequest($log_name,$worker.$content,2);
                         }
                		    curl_close($ch);

                        return $content;
          }


        function MatchOPeratorRespcodes($status){
         $statuscode =array();
    		if(strtolower($status)=='successful'){
    		$statuscode['transaction_status']='completed';
    		$statuscode['status_code']=200;
      }else if(strtolower($status)=='failed'){
    		$statuscode['transaction_status']='failed';
    		$statuscode['status_code']='general_error';
       }else if(strtolower($status)=='pending'){
          $statuscode['transaction_status']='pending';
      		$statuscode['status_code']=202;
    		}else if(strtolower($status)=='target_authorization_error'){
          $statuscode['transaction_status']='failed';
      		$statuscode['status_code']='balance_insufficient';
    		}else if(strtolower($status)=='not_enough_funds'){
          $statuscode['transaction_status']='failed';
      		$statuscode['status_code']='not_enough_funds';
    		}else if(strtolower($status)=='authorization_sender_account_not_active'){
          $statuscode['transaction_status']='failed';
      		$statuscode['status_code']='account_not_active';
        }else if(strtolower($status)=='accountholder_with_fri_not_found'){
          $statuscode['transaction_status']='failed';
      		$statuscode['status_code']='account_not_found';
    		}else if(strtolower($status)=='reference_id_already_in_use'){
          $statuscode['transaction_status']='failed';
      		$statuscode['status_code']='duplicate_transactiom_reference_number';
    		}else if(strtolower($status)=='not_found'){
      		$statuscode['status_code']='transactiom_reference_not_foound';
    		}else{
          $statuscode['transaction_status']='failed';
      		$statuscode['status_code']='general_error';
    		}

    		return $statuscode;
    		}




            function encrypt(array $payload)
            {
                $encrypted = openssl_encrypt(json_encode($payload), 'DES-EDE3', FLUTTER_ENCRYPT_KEY, OPENSSL_RAW_DATA);
                return base64_encode($encrypted);
             }


      }
