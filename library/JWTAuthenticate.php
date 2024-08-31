<?php

class  JWTAuthenticate{

    function __construct() {
        $this->log = new Logs();
      }


function IsTokenValid(){

   $jwt = preg_split('/ /', $_SERVER['HTTP_AUTHORIZATION'])[1];
  $is_jwt_valid = is_jwt_valid('eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6IjEyMzQ1Njc4OTAiLCJuYW1lIjoiSm9obiBEb2UiLCJhZG1pbiI6dHJ1ZSwiZXhwIjoxNTgyNjE2MDA1fQ.umEYVDP_kZJGCI3tkU9dmq7CIumEU8Zvftc-klp-334');

echo nl2br("\n");

if($is_jwt_valid === TRUE) {
echo 'JWT is valid';
} else {
echo 'JWT is invalid';
}

}

function ValidateToken($log_name,$token,$secret,$worker){
//https://roytuts.com/how-to-generate-and-validate-jwt-using-php-without-using-third-party-api/
  //$secretKey  = 'bGS6lzFqvvSQ8ALbOxatm7/Vk7mLQyzqaS34Q4oR1ew=';

  // split the jwt
	$tokenParts = explode('.', $token);
  if(count($tokenParts)<3){

    $this->log->TraceLog($log_name,$worker.'JWTauth:  ValidateToken Incomplete token  '. var_export($tokenParts,true),2);

 return 'FALSE';
  }  
	$header = base64_decode($tokenParts[0]);
	$payload = base64_decode($tokenParts[1]);
	$signature_provided = $tokenParts[2];

$this->log->TraceLog($log_name,$worker.'JWTauth:  ValidateToken  signature provided  '. var_export($signature_provided,true),2);

	// check the expiration time - note this will cause an error if there is no 'exp' claim in the jwt
	$expiration = json_decode($payload)->exp;
 //print_r($secret);die();
	$is_token_expired = ($expiration - time()) < 0;

  $this->log->TraceLog($log_name,$worker.'JWTauth:  ValidateToken is token expired  '. var_export($is_token_expired,true),2);

  //print_r($is_token_expired);die();
	// build a signature based on the header and payload using the secret
	$base64_url_header = $this->base64url_encode($header);
//  $header = json_decode($base64_url_header,true);
	$base64_url_payload = $this->base64url_encode($payload);


  $this->log->TraceLog($log_name,$worker.'JWTauth:  ValidateToken  header provided  '. var_export($header,true),2);
  $this->log->TraceLog($log_name,$worker.'JWTauth:  ValidateToken  payload provided  '. var_export($payload,true),2);

//	$signature = hash_hmac('SHA512', "$base64_url_header.$base64_url_payload", $this->base64url_decode($secret), true);
	$signature = hash_hmac(JWT_ALGO, "$base64_url_header.$base64_url_payload", base64_decode($secret), true);

  $this->log->TraceLog($log_name,$worker.'JWTauth:  Signature before encoding '. var_export($signature,true),2);

	$base64_url_signature = $this->base64url_encode($signature);
//  print_r($base64_url_signature);die();
  $this->log->TraceLog($log_name,$worker.'JWTauth:  ValidateToken  signature generated  '. var_export($base64_url_signature,true),2);
	// verify it matches the signature provided in the jwt
	$is_signature_valid = ($base64_url_signature === $signature_provided);

   if ($is_token_expired || !$is_signature_valid) {
  //if (!$is_signature_valid) {
      return 'FALSE';
    } else {
      return 'TRUE';
    }

}


function base64url_decode($data) {
    return base64_decode(str_replace(array('-', '_'), array('+', '/'), $data));
}


    function GenerateToken($headers,$payload,$algo,$secret){

        $headers_encoded = $this->base64url_encode(json_encode($headers));

      	$payload_encoded = $this->base64url_encode(json_encode($payload));

      	$signature = hash_hmac($algo, "$headers_encoded.$payload_encoded", $secret, true);
      	$signature_encoded = $this->base64url_encode($signature);

      	$jwt = "$headers_encoded.$payload_encoded.$signature_encoded";

      	return $jwt;
    }


              function base64url_encode($text)
              {
                return \str_replace('=', '', \strtr(\base64_encode($text), '+/', '-_'));
              }
              




}
