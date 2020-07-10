<?php

$mtn_credit_request = '<?xml version="1.0" encoding="UTF-8"?><ns2:sptransferrequest xmlns:ns2="http://www.ericsson.com/em/emm/serviceprovider/v1_0/backend">
<sendingfri>FRI:'.$trans_data['routing']['req_username'].'/USER</sendingfri>
<receivingfri>FRI:'.$trans_data['transaction_account'].'/MSISDN</receivingfri>
<amount>
<amount>'.$trans_data['transaction_amount'].'</amount>
<currency>'.$trans_data['currency'].'</currency>
</amount>
<providertransactionid>'.$trans_data['transaction_reference_number'].'</providertransactionid>
<name>
<firstname>'.$trans_data['first_name'].'</firstname>
<lastname>'.$trans_data['last_name'].'</lastname>
</name>
<sendernote>'.$trans_data['transaction_reason'].'</sendernote>
<receivermessage/>
<referenceid>'.$trans_data['transaction_id'].'</referenceid>
</ns2:sptransferrequest>';
