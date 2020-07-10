<?php

$mtn_debit_request = '<?xml version="1.0" encoding="UTF-8"?><ns2:debitrequest xmlns:ns2="http://www.ericsson.com/em/emm/financial/v1_0">
   <fromfri>FRI:'.$trans_data['transaction_account'].'/MSISDN</fromfri>
   <tofri>FRI:'.$trans_data['routing']['req_username'].'/USER</tofri>
   <amount>
    <amount>'.$trans_data['transaction_amount'].'</amount>
   <currency>'.$trans_data['currency'].'</currency>
   </amount>
   <externaltransactionid>'.$trans_data['transaction_reference_number'].'</externaltransactionid>
   <referenceid>'.$trans_data['transaction_id'].'</referenceid>
</ns2:debitrequest>';
