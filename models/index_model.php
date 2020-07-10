<?php

class Index_Model extends Model {

    function __construct() {
        parent::__construct();
    }
    
    function TestAccounting(){
        $vndr = 'MVend';
        $transaction = $this->db->SelectData("SELECT * FROM mvd_payment_transactions WHERE transaction_id=14122600001");
        $this->AccountDayBookPosting($vndr, $transaction[0]);
    }
	
	
	
	
    function ProcessTransactionStatus(){
		
       $today=date('Y-m-d');
       $transaction = $this->db->SelectData("SELECT * FROM mvd_payment_transactions WHERE DATE(transaction_date)='".$today."'  AND transaction_status='Pending'");
      if(count($transaction)>0){
		 foreach ($transaction as $key => $value) {
         $timediff=date()-$transaction[$key]['transaction_date'];
		 print_r($timediff);die();
		 
		 //if(){
		$this->processPaymentCompleted();	 
			 
		// }
        }  
		
		
	  }

    }

	function processPaymentCompleted($data){
	$xml='<?xml version="1.0" encoding="utf-8" ?>
<soapenv:Envelope xmlns:soapenv="http://schemas.xmlsoap.org/soap/envelope/" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
<soapenv:Header>
<ns1:NotifySOAPHeader xmlns:ns1="http://www.huawei.com.cn/schema/common/v2_1">
<ns2:traceUniqueID xmlns:ns2="http://www.csapi.org/schema/momopayment/local/v1_0">504021503411410281818220013006</ns2:traceUniqueID>
</ns1:NotifySOAPHeader>
</soapenv:Header>
<soapenv:Body>
<ns3:requestPaymentCompleted xmlns:ns3="http://www.csapi.org/schema/momopayment/local/v1_0">
<ns3:ProcessingNumber>'.$data['referenceid'].'</ns3:ProcessingNumber>
<ns3:MOMTransactionID>'.$data['transactionid'].'</ns3:MOMTransactionID>
<ns3:StatusCode>'.$data['code'].'</ns3:StatusCode>
<ns3:StatusDesc>This is a respone Message!</ns3:StatusDesc>
<ns3:ThirdPartyAcctRef>250786474859</ns3:ThirdPartyAcctRef>
</ns3:requestPaymentCompleted>
</soapenv:Body>
</soapenv:Envelope>';	
		
	 $url ='http://192.168.4.3:7473/testbed/mvendtest/sendpay/paymentcompletedrequest/'; 
$this->SendByCurl($url,$xml);	 
	}
   public function SendByCurl($url,$xml) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS,$xml);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 120);
        
        $content = curl_exec($ch);

        if (curl_errno($ch)) {
            echo 'Curl error: ' . curl_error($ch);
        }

        curl_close($ch);
        return $content;
    }	
	
}

