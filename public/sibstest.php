<?php


$respHeaders = [];
$base='https://api.qly.spn.sibsapimarket.com'; $ver='v1';
$entity='21561'; $reference='420729673'; $clientId='771c5566-2d4c-449c-86f8-38564d080b44';
$clientSecret='aI2tK1vL3fG4gB5rV7qY4pI4wH2fY8mA8hP2nJ2hC1yM5kB8rY';
$paths=["/sibs/$ver/payment-owners/$entity/$reference"];

echo "INIT:\n"; 


foreach($paths as $p){
  $url=$base.$p; echo "\n== $url ==\n";
  $h=[
    "Accept: application/json",
    "x-ibm-client-id: $clientId",
    "X-Message-ID: ".substr(strtoupper(bin2hex(random_bytes(8))),0,14),
  ];


  $ch=curl_init($url);
  curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_HEADER=>true,CURLOPT_HTTPHEADER=>$h,  CURLOPT_HEADERFUNCTION => function($ch, $header) use (&$respHeaders) {
        $len = strlen($header);
        $parts = explode(':', $header, 2);
        if (count($parts) == 2) {
            $respHeaders[strtolower(trim($parts[0]))] = trim($parts[1]);
        }
        return $len;
    },CURLOPT_TIMEOUT=>8,CURLOPT_CONNECTTIMEOUT=>3]);
  $resp=curl_exec($ch); $status=curl_getinfo($ch,CURLINFO_RESPONSE_CODE); $hs=curl_getinfo($ch,CURLINFO_HEADER_SIZE);
  curl_close($ch); echo "STATUS: $status\n"; echo substr($resp,0,$hs),"\n",substr($resp,$hs),"\n";

  print_r($respHeaders);  // array com os headers
  
//   $h=[
//     "Accept: application/json",
//     "x-ibm-client-id: $clientId",
//     "x-ibm-client-secret: $clientSecret",
//     "X-Message-ID: ".substr(strtoupper(bin2hex(random_bytes(8))),0,14),
//   ];


//   $ch=curl_init($url);
//   curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_HEADER=>true,CURLOPT_HTTPHEADER=>$h,CURLOPT_TIMEOUT=>8,CURLOPT_CONNECTTIMEOUT=>3]);
//   $resp=curl_exec($ch); $status=curl_getinfo($ch,CURLINFO_RESPONSE_CODE); $hs=curl_getinfo($ch,CURLINFO_HEADER_SIZE);
//   curl_close($ch); echo "STATUS: $status\n"; echo substr($resp,0,$hs),"\n",substr($resp,$hs),"\n";


//     $basic = base64_encode(  $clientId . ':' . $clientSecret);
//     $h=[
//     "Accept: application/json",
//     "x-ibm-client-id: $clientId",
//     "X-Message-ID: ".substr(strtoupper(bin2hex(random_bytes(8))),0,14),
//   ];
//     $h['Authorization'] = 'Basic ' . $basic;


//  $ch=curl_init($url);
//   curl_setopt_array($ch,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_HEADER=>true,CURLOPT_HTTPHEADER=>$h,CURLOPT_TIMEOUT=>8,CURLOPT_CONNECTTIMEOUT=>3]);
//   $resp=curl_exec($ch); $status=curl_getinfo($ch,CURLINFO_RESPONSE_CODE); $hs=curl_getinfo($ch,CURLINFO_HEADER_SIZE);
//   curl_close($ch); echo "STATUS: $status\n"; echo substr($resp,0,$hs),"\n",substr($resp,$hs),"\n";



}
