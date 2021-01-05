<?php

$airtel_credit_request = '<COMMAND>
<AMOUNT>'.$trans_data['transaction_amount'].'</AMOUNT>
<MSISDN2>'.$trans_data['routing']['registered_msisdn'].'</MSISDN2>
<MSISDN>'.substr($trans_data['transaction_account'], 3).'</MSISDN>
<serviceType>MERCHCASHIN</serviceType>
<REFERENCE_NO>'.$trans_data['transaction_id'].'</REFERENCE_NO>
<EXTTRID>'.$trans_data['transaction_reference_number'].'</EXTTRID>
<PIN>'.$trans_data['routing']['req_password'].'</PIN>
<interfaceId>PALMKASH</interfaceId>
</COMMAND>';
