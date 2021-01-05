<?php

$airtel_debit_request = '<COMMAND>
<TYPE>MERCHANTPAYMENT</TYPE>
<interfaceId>PALMKASH</interfaceId>
<MSISDN>'.substr($trans_data['transaction_account'], 3).'</MSISDN>
<MSISDN2>'.$trans_data['routing']['registered_msisdn'].'</MSISDN2>
<AMOUNT>'.$trans_data['transaction_amount'].'</AMOUNT>
<EXTTRID>'.$trans_data['transaction_reference_number'].'</EXTTRID>
<REFERENCE>'.$trans_data['transaction_id'].'</REFERENCE>
<BILLERID>'.$trans_data['routing']['registered_msisdn'].'</BILLERID>
<MEMO>Airtel</MEMO>
<serviceType>MERCHANTPAYMENT</serviceType>
<USERNAME>'.$trans_data['routing']['req_username'].'</USERNAME>
<PASSWORD>'.$trans_data['routing']['req_password'].'</PASSWORD>
</COMMAND>';
