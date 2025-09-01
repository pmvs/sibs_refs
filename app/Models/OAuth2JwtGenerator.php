<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use DateTimeImmutable;
use Illuminate\Support\Facades\Http;

class OAuth2JwtGenerator extends Model
{

    protected $connection = "mysql";
    protected $logchannel = 'proxylookup';
    protected $isConnProduction = false;
    
    /*
    |--------------------------------------------------------------------------
    | CONSTRUTORES
    |--------------------------------------------------------------------------
    */
    public function __construct() 
    {
        $this->logchannel = 'proxylookup';
        $this->isConnProduction = config('app.connection_prod');
        $get_arguments       = func_get_args();
        $number_of_arguments = func_num_args();
        if (method_exists($this, $method_name = '__construct_'.$number_of_arguments)) {
            call_user_func_array(array($this, $method_name), $get_arguments);
        }
        \Log::channel($this->logchannel)->info('__construct OAuth2JwtGenerator');
        \Log::channel($this->logchannel)->info('Connection PROD ? ' . $this->isConnProduction );
    }
    
    public function __construct_1($logchannel) 
    {
        $this->logchannel = $logchannel;
        \Log::channel($this->logchannel)->info('__construct_1 OAuth2JwtGenerator');
    }

    public function __construct_2($logchannel, $isConnProduction ) 
    {
        $this->logchannel = $logchannel;
        $this->isConnProduction = $isConnProduction;
        \Log::channel($this->logchannel)->info('__construct_2 OAuth2JwtGenerator');
    }

    /*
    |--------------------------------------------------------------------------
    | SETTERS
    |--------------------------------------------------------------------------
    */

    public function setIsConnectionProd ( $isConnProduction )
    {
        $this->isConnProduction = $isConnProduction;
    }

    /*
    |--------------------------------------------------------------------------
    | PUBLIC METHODS
    |--------------------------------------------------------------------------
    */
    public function generateJwtToken( $audience, $issuer, $certificate )
    {
        try{

            \Log::channel($this->logchannel)->info('OAuth2JwtGenerator: generateJwtToken--------');
            \Log::channel($this->logchannel)->info('audience: ' . $audience);
            \Log::channel($this->logchannel)->info('issuer: ' . $issuer);

            //chave privada 
            $privateKey = $certificate['pkey'];

            //public key
            $publicKey = openssl_pkey_get_public(  openssl_x509_read( $certificate['cert'] )   );
            $keyData = openssl_pkey_get_details($publicKey);
            //\Log::channel($this->logchannel)->info( print_r($keyData,true) );
          
            // Access the public key
            $publicKeyString = $keyData['key'];

            $sha1_hash = openssl_x509_fingerprint($certificate['cert'], 'sha1'); // sha1 hash (x5t parameter)
            //\Log::channel($this->logchannel)->info('sha1_hash: ' . $sha1_hash);
        
            $arr2 = str_split($sha1_hash, 2);
            //\Log::channel($this->logchannel)->info('sha1_hash: ' . print_r($arr2, true));
            $straux = '';
            foreach( $arr2  as $arr ) {
                $straux .= strtoupper($arr) . ':';
            }
            $straux = rtrim($straux, ':');
            //\Log::channel($this->logchannel)->info('sha1_hash: ' . $straux);

           // $aux = '5D:D0:7C:9A:C0:B4:BD:0F:86:4B:9B:17:48:B0:35:10:09:4C:84:DD';
            $encoded_fingerprint4 = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($straux));
           //openssl x509 -noout -in bpnetsvc-ccmaf-cert.bportugal.pt.pfx -fingerprint
           // \Log::channel($this->logchannel)->info('SHA1 Fingerprint: ' . $aux);
            // \Log::channel($this->logchannel)->info('encoded_fingerprint : ' . $encoded_fingerprint4);

            //create jwt token header
            //\Log::channel($this->logchannel)->info('------header--------');
            $header = json_encode([
                'typ' => 'JWT',
                'alg' => 'RS256',
                'xt5' => $encoded_fingerprint4,
                'kid' =>  strtoupper($sha1_hash),
            ],JSON_UNESCAPED_SLASHES);
            \Log::channel($this->logchannel)->info(print_r($header, true));
 
            $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
            // \Log::channel($this->logchannel)->info('------base64UrlHeader--------');
            // \Log::channel($this->logchannel)->info( $base64UrlHeader );

            //create jwt token claims
            $date = new DateTimeImmutable();
            $claims = json_encode([
                'sub'   => $issuer,
                'jti'   => uuid_create(),
                'iat'   => $date->getTimestamp(),
                'iss'   => $issuer,
                'nbf'   => $date->getTimestamp(),
                'exp'   => $date->modify('+15 minutes')->getTimestamp(),
                'aud'   => $audience
            ], JSON_UNESCAPED_SLASHES );
            \Log::channel($this->logchannel)->info(print_r($claims, true));

            $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($claims));
            // \Log::channel($this->logchannel)->info('------base64UrlPayload--------');
            // \Log::channel($this->logchannel)->info( $base64UrlPayload);

            //\Log::channel($this->logchannel)->info('------dados a serem assinados --------');
            $data = $base64UrlHeader . "." . $base64UrlPayload;
            //\Log::channel($this->logchannel)->info( $data );

            //\Log::channel($this->logchannel)->info('------assina com a private key os dados --------');
            openssl_sign($data, $signature, $privateKey, "sha256WithRSAEncryption");
        
            $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
            //\Log::channel($this->logchannel)->info('Encoded signature: ' . $base64UrlSignature);

            // JWT signed with the supplied private key
            $jwt = $data . "." . $base64UrlSignature;

            return $jwt;

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error( $e->getMessage());
            return '';
        }
    }

    public function generateJwtToken_old( $audience, $issuer, $certificate )
    {
        try{

            \Log::channel($this->logchannel)->info('OAuth2JwtGenerator: generateJwtToken--------');
            \Log::channel($this->logchannel)->info('audience: ' . $audience);
            \Log::channel($this->logchannel)->info('issuer: ' . $issuer);

            //chave privada 
            $privateKey = $certificate['pkey'];

            //public key
            $publicKey = openssl_pkey_get_public(  openssl_x509_read( $certificate['cert'] )   );
            $keyData = openssl_pkey_get_details($publicKey);
            \Log::channel($this->logchannel)->info( print_r($keyData,true) );
            // Access the public key
            $publicKeyString = $keyData['key'];

        //     \Log::channel($this->logchannel)->info('------assina com a pkey os dados do certificado --------');
        //    // openssl_sign($certificate['cert'], $signature, $privateKey, OPENSSL_ALGO_SHA256);
            //  openssl_sign($certificate['cert'], $signature, $privateKey,  "sha256WithRSAEncryption");

            // //openssl_free_key( $privateKey );

            //  $signingCredentials = $signature;
            
            //  $base64UrlSignatureCredentials = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signingCredentials));
            //  \Log::channel($this->logchannel)->info('base64UrlSignatureCredentials: ' . $base64UrlSignatureCredentials);
           
            
        //     $sha256_hash = openssl_x509_fingerprint($certificate['cert'] , 'sha256'); // sha256 hash (x5t#256 parameter)
        //    // $encoded_fingerprint2 = rtrim(strtr(base64_encode($sha256_hash), "+/", "-_"), '=');
        //     $encoded_fingerprint = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($sha256_hash));

        //     \Log::channel($this->logchannel)->info('sha256_hash: ' . $sha256_hash);
        //     \Log::channel($this->logchannel)->info('encoded_fingerprint : ' . $encoded_fingerprint);
      
           // $cert = openssl_x509_read($certificate);
            $sha1_hash = openssl_x509_fingerprint($certificate['cert'], 'sha1'); // sha1 hash (x5t parameter)
           \Log::channel($this->logchannel)->info('sha251_hash: ' . $sha1_hash);
           //\Log::channel($this->logchannel)->info('encoded_fingerprint : ' .  str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($sha1_hash)));
     
            // $xt5 = $sha1_hash;
            // $xt5 = rtrim(strtr(base64_encode($xt5), "+/", "-_"), '=');
            // $xt5 = 'XdB8msC0vQ-GS5sXSLA1EAlMhN0';
  
            // $fingerprint = str_replace("SHA1 Fingerprint=", '', system('openssl x509 -noout -in "c:\certs\my-cert.pem" -fingerprint'));
            // $encoded_fingerprint3 = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($fingerprint));
            // \Log::channel($this->logchannel)->info('fingerprint: ' . $fingerprint);
            // \Log::channel($this->logchannel)->info('encoded_fingerprint : ' . $encoded_fingerprint3 );
      
            $aux = '5D:D0:7C:9A:C0:B4:BD:0F:86:4B:9B:17:48:B0:35:10:09:4C:84:DD';
            $encoded_fingerprint4 = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($aux));
            \Log::channel($this->logchannel)->info('SHA1 Fingerprint: ' . $aux);
            \Log::channel($this->logchannel)->info('encoded_fingerprint : ' . $encoded_fingerprint4);
      
            
            // $aux1 = openssl_x509_fingerprint($certificate['cert'], 'sha1');;
            // $encoded_fingerprint5 = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($aux1));
            // \Log::channel($this->logchannel)->info('aux: ' . $aux1);
            // \Log::channel($this->logchannel)->info('encoded_fingerprint : ' . $encoded_fingerprint5);
      
           // $xt5 = $encoded_fingerprint4;
          

            //\Log::channel($this->logchannel)->info('X5T : ' . $xt5 );
      
            //create jwt token header
            \Log::channel($this->logchannel)->info('------header--------');
            $header = json_encode([
                'typ' => 'JWT',
                'alg' => 'RS256',
                'xt5' => $encoded_fingerprint4,
                'kid' =>  strtoupper($sha1_hash),
            ],JSON_UNESCAPED_SLASHES);
            \Log::channel($this->logchannel)->info(print_r($header, true));
            
            //Header BP: {"alg":"RS256","kid":"5DD07C9AC0B4BD0F864B9B1748B03510094C84DD","x5t":"XdB8msC0vQ-GS5sXSLA1EAlMhN0","typ":"JWT"}  

            $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
            \Log::channel($this->logchannel)->info('------base64UrlHeader--------');
            \Log::channel($this->logchannel)->info( $base64UrlHeader );

            //create jwt token claims
            \Log::channel($this->logchannel)->info('------claims--------');
            $date = new DateTimeImmutable();
            $claims = json_encode([
                'sub'   => $issuer,
                'jti'   => uuid_create(),
                'iat'   => $date->getTimestamp(),
                'iss'   => $issuer,
                'nbf'   => $date->getTimestamp(),
                'exp'   => $date->modify('+15 minutes')->getTimestamp(),
                'aud'   => $audience
            ], JSON_UNESCAPED_SLASHES );
            \Log::channel($this->logchannel)->info(print_r($claims, true));

            // //create jwt token payload
            // \Log::channel($this->logchannel)->info('------payload--------');
            // $payload = json_encode([
            //    'issuer'   => $issuer,
            //    'audience'   => $audience,
            //    'claims'   => $claims,
            //    'expires'   => $date->modify('+15 minutes')->getTimestamp(),
            //    'signingCredentials'   => $base64UrlSignatureCredentials,
            // ],JSON_UNESCAPED_SLASHES);
            // \Log::channel($this->logchannel)->info( $payload );

            $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($claims));
            \Log::channel($this->logchannel)->info('------base64UrlPayload--------');
            \Log::channel($this->logchannel)->info( $base64UrlPayload);

            \Log::channel($this->logchannel)->info('------dados a serem assinados --------');
            $data = $base64UrlHeader . "." . $base64UrlPayload;
            \Log::channel($this->logchannel)->info( $data );

            \Log::channel($this->logchannel)->info('------assina com a private key os dados --------');
            openssl_sign($data, $signature, $privateKey, "sha256WithRSAEncryption");
        
            $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
            \Log::channel($this->logchannel)->info('Encoded signature: ' . $base64UrlSignature);

         
            $certFile = $certificate['cert'];

            // Your JWT signed with the supplied private key
            $jwt = $data . "." . $base64UrlSignature;

            $tokenJSON = json_encode($jwt, JSON_UNESCAPED_SLASHES);

            $token = explode('.', $jwt);

            \Log::channel($this->logchannel)->info('JWT : ' . print_r($token, true));
            $header2 = base64_decode($token[0]);
            $payload2 = base64_decode($token[1]);
            $signature2 = base64_decode($token[2]);
      
            \Log::channel($this->logchannel)->info('Header    JWT: ' . $header2);
            \Log::channel($this->logchannel)->info('Payload   JWT: ' . $payload2);
            \Log::channel($this->logchannel)->info('Signature JWT: ' . $signature2);

            $tokenBP = 'eyJhbGciOiJSUzI1NiIsImtpZCI6IjVERDA3QzlBQzBCNEJEMEY4NjRCOUIxNzQ4QjAzNTEwMDk0Qzg0REQiLCJ4NXQiOiJYZEI4bXNDMHZRLUdTNXNYU0xBMUVBbE1oTjAiLCJ0eXAiOiJKV1QifQ.eyJzdWIiOiJodHRwczovL2JwbmV0c3ZjLWNjbWFmLWNlcnQuYnBvcnR1Z2FsLnB0LyIsImp0aSI6IjYzMTE4MDNlLTEyYmEtNDY4Mi1hZGEyLTVmOWMxMjlkZDVlNiIsImlhdCI6MTcwMTI1NTY2MCwiaXNzIjoiaHR0cHM6Ly9icG5ldHN2Yy1jY21hZi1jZXJ0LmJwb3J0dWdhbC5wdC8iLCJuYmYiOjE3MDEyNTU2NjAsImV4cCI6MTcwMTI1NjU2MCwiYXVkIjoiaHR0cHM6Ly93d3djZXJ0LmJwb3J0dWdhbC5uZXQvYWRmcy9vYXV0aDIvdG9rZW4ifQ.QCScWheQD30BODMa1ZRHh11W18CkvkHhEHWKnV6uhejNNwNwL8zdPms_5BFgC3EFeG8yeK3HotkDv2NPMkm5mP4rxMn7HoBQFBThlGLF4r5_2_D7Yfw53n18s0Hk0Db2veTdSFOSFJnYqA_ln__ACqgx020SIMPyrQ8PX0DtICoWLMNvLjnZic0Ud5YNEW6FChcMLGFC-nUXHbYobd0MB9JCSJzbc5fKI16-8DqOHvriAEX--x9kZz5IMyvOdJPLG3yuSi-HJFnpJUMxLPeJGY8pKf8HzEhMfAUpkTyigK2wbgDjvte1CEM5NMWGjzCZi1PYzO_Z18B_RGd7qdrWDQ';

      
            $token = explode('.', $tokenBP);

            \Log::channel($this->logchannel)->info('JWT BP : ' . print_r($token, true));
            $header2 = base64_decode($token[0]);
            $payload2 = base64_decode($token[1]);
            $signature2 = base64_decode($token[2]);
      
            \Log::channel($this->logchannel)->info('Header BP: ' . $header2);
            \Log::channel($this->logchannel)->info('Payload BP: ' . $payload2);
            \Log::channel($this->logchannel)->info('Signature BP: ' . $signature2);


            $header_payload = $token[0] . '.' . $token[1];

            openssl_sign($header_payload, $signatureTeste, $privateKey, "sha256WithRSAEncryption");
            $base64UrlSignatureBP = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signatureTeste));
            \Log::channel($this->logchannel)->info('Signature Teste: ' . $base64UrlSignatureBP);
            \Log::channel($this->logchannel)->info('Signature BP   : ' . $token[2]);


             // Check signature
            $ok = openssl_verify($header_payload, $signatureTeste, $publicKeyString, OPENSSL_ALGO_SHA256);
            // echo "check #1: ";
            if ($ok == 1) {
               \Log::channel($this->logchannel)->info( "signature ok (as it should be)\n");
            } elseif ($ok == 0) {
                \Log::channel($this->logchannel)->info( "bad (there's something wrong)\n");
            } else {
                \Log::channel($this->logchannel)->info("ugly, error checking signature\n");
            }

            return $jwt;

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error( $e->getMessage());
            return '';
        }
    }


    // retorna payload em formato array, ou lança um Exception
    public static function decode(string $token, string $secret): array
    {
        $token = explode('.', $token);

        $header = base64_decode_url($token[0]);
        $payload = base64_decode_url($token[1]);
        $signature = base64_decode_url($token[2]);

        $header_payload = $token[0] . '.' . $token[1];

        // // Check signature
            $ok = openssl_verify($data, $binary_signature, $public_key, OPENSSL_ALGO_SHA1);
        // echo "check #1: ";
        // if ($ok == 1) {
        //    echo "signature ok (as it should be)\n";
        // } elseif ($ok == 0) {
        //    echo "bad (there's something wrong)\n";
        // } else {
        //    echo "ugly, error checking signature\n";
        // }

        if (hash_hmac('sha256', $header_payload, $secret, true) !== $signature) {
            throw new \Exception('Invalid signature');
        }
        return json_decode($payload, true);
    }

}

