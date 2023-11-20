<?php


class Flutterwave extends Controller {

    function __construct() {
        parent::__construct();

    }

    function Index(){
      $general=array('status'=>403,
                     'message'=>'Forbidden');
        header('Content-Type: application/json;charset=utf-8"');
        echo json_encode($general,true);
        exit();
    }


  function encryptPayLoad(){
  //$data =array();
$data = array("card_number" => "5531886652142950",
  "cvv" =>"564",
  "expiry_month" => "09",
  "expiry_year" =>"32",
  "currency" => "RWF",
  "amount" => "1000",
  "email" => "bguma@palmkash.com",
  "fullname" => "Baguma Test",
  "tx_ref" => "TX-32345",
  "redirect_url"=>"https://your-awesome.app/payment-redirect",
);


$post_data = $this->model->encrypt($data);

    print_r($post_data);die();
  }


   function verifyPayment(){


   }


   function MakePayment(){

     $jsonrequest =  file_get_contents('php://input');

     if(json_decode($jsonrequest) != NULL){
        $postdata = json_decode($jsonrequest,true);
        $postdata['request']='debit';
        $log_file_name = $this->model->log->LogRequest('req_from_merchant',"Card Debit". var_export($jsonrequest,true),1);
        $this->model->processPayment($postdata, 'req_from_merchant');
	     }else{
    $general=array('status'=>400,
                   'message'=>'Bad Request');
      echo json_encode($general);
        }


   }


}
