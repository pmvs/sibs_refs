<?php

namespace App\Http\Middleware;

use Log;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use App\Services\BancoPortugal\BancoPortugalService;

class JWTMiddleware
{
    private $logchannel = 'jwt';
   
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {

            \Log::channel($this->logchannel)->info( '-----------------BEGIN JWT------------------------');
            \Log::channel($this->logchannel)->info( 'REQUEST : ' . print_r( $request->all(), true)); 
            \Log::channel($this->logchannel)->info( 'HEADERS : ' . print_r( $request->header(), true));

            $token = $request->bearerToken();
            \Log::channel($this->logchannel)->info( 'TOKEN : ' . $token); 
            if (  trim($token) == '' ) {
                \Log::channel($this->logchannel)->error( '!!!!!TOKEN INEXISTENTE!!!! Vai devolver 401' ); 
                $json = ['message' => 'Unauthorized'];
                return response()->json($json, 401);
            }

            // split the token
            \Log::channel($this->logchannel)->info( 'Check format..'); 
            $tokenParts = explode('.', $token);
            if ( count( $tokenParts ) != 3) {
                \Log::channel($this->logchannel)->error( 'Format..NOT OK. Return 401');
                $json = ['message' => 'Unauthorized'];
                return response()->json($json, 401);
            }
            \Log::channel($this->logchannel)->info( 'Format..OK'); 

            $header = base64_decode($tokenParts[0]);
            $payload = base64_decode($tokenParts[1]);
            $signatureProvided = $tokenParts[2];
            \Log::channel($this->logchannel)->info( 'TOKEN header: ' . $header); 
            \Log::channel($this->logchannel)->info( 'TOKEN payload: ' . $payload);
            \Log::channel($this->logchannel)->info( 'TOKEN signatureProvided: ' . $signatureProvided); 
            $headerarray = json_decode($header, true);
            \Log::channel($this->logchannel)->info( 'TOKEN header: ' . print_r($headerarray, true));
            $payloadarray = json_decode($payload, true);
            \Log::channel($this->logchannel)->info( 'TOKEN payload: ' . print_r( $payloadarray , true));
          
            //verify it matches the audience provided in the token
            \Log::channel($this->logchannel)->info( 'Check audience..'); 
            $audience = json_decode($payload)->aud;
            \Log::channel($this->logchannel)->info( 'Audience: ' . $audience); 
            switch( trim($audience)){
                case config('app.copendpoint'):
                case config('app.plendpoint'):
                    \Log::channel($this->logchannel)->info( 'Audience..OK'); 
                    break;
                default: 
                    \Log::channel($this->logchannel)->error( 'Audience..NOT OK. Return 401');
                    $json = ['message' => 'Unauthorized'];
                    return response()->json($json, 401);
                    break;
            }
    
            // check the expiration time - note this will cause an error if there is no 'exp' claim in the token
            \Log::channel($this->logchannel)->info( 'Check expiration date...'); 
            $expiration = Carbon::createFromTimestamp(json_decode($payload)->exp);
            $tokenExpired = (Carbon::now()->diffInSeconds($expiration, false) < 0);
            if ($tokenExpired) {
                \Log::channel($this->logchannel)->error( 'Token has expired. Return 401'); 
                $json = ['message' => 'Unauthorized'];
                return response()->json($json, 401);
            } else {
                \Log::channel($this->logchannel)->info( 'Token has not expired yet.'); 
            }

            //verify it matches the issuer provided in the token
            \Log::channel($this->logchannel)->info( 'Check app id...'); 
            $appid_trusted = config('app.oauth2appid');
            $appid_received = json_decode($payload)->appid;
            $appidValid = ($appid_trusted === $appid_received);
            \Log::channel($this->logchannel)->info( 'APPID trusted: ' . $appid_trusted); 
            \Log::channel($this->logchannel)->info( 'APPID payload: ' . $appid_received);
            // // \Log::channel($this->logchannel)->info( 'APPID valid? ' . $appidValid);
            if ($appidValid) {
                \Log::channel($this->logchannel)->info( 'AppID is trusted.'); 
            } else {
                \Log::channel($this->logchannel)->warning( 'AppID NOT trusted. Return 401'); 
                $json = ['message' => 'Unauthorized'];
                return response()->json($json, 401);
            }

            \Log::channel($this->logchannel)->info( 'Check signature...'); 
            $bpService = new BancoPortugalService($this->logchannel);
            try {

                if ( ! file_exists('c:\certs\publicBdp.pem') ) {
                    \Log::channel($this->logchannel)->info( 'Read public Key from ADFS'); 
                    $publicKey = $bpService->readPublicKeyfromADFS() ;
                    \Log::channel($this->logchannel)->info( 'Public key from ADFS: ' . $publicKey); 
                    \Log::channel($this->logchannel)->info( 'Save Public key from ADFS into file'); 
                    file_put_contents('c:\certs\publicBdp.pem', $publicKey);
                    \Log::channel($this->logchannel)->info( 'Public key saved'); 
                }

                \Log::channel($this->logchannel)->info( 'Read public Key from FILE'); 
                $data = file_get_contents( 'c:\certs\publicBdp.pem' );
                //\Log::channel($this->logchannel)->info( 'Public key from fiel: ' . $data); 

                $publicKey = openssl_get_publickey($data);

                if ($publicKey == 0){
                    $result = "Bad key zero.";
                    \Log::channel($this->logchannel)->warning( 'Public key from FILE: ' . $result); 
                   
                    $json = ['message' => 'Unauthorized'];
                    return response()->json($json, 401);

                }elseif ($publicKey == false)
                {
                    $result = "Bad key false.";
                    \Log::channel($this->logchannel)->warning( 'Public key from FILE: ' . $result); 
                  
                    $json = ['message' => 'Unauthorized'];
                    return response()->json($json, 401);
                    
                }

                //Verify the signature
                $dataToVerify = $tokenParts[0] . '.' . $tokenParts[1];

                //signatureProvided
                $signatureProvided = base64_decode(strtr($signatureProvided, '-_', '+/'));

                $result = openssl_verify($dataToVerify, $signatureProvided, $publicKey, OPENSSL_ALGO_SHA256);
                if ($result !== 1) {
                    \Log::channel($this->logchannel)->warning( 'Token is Invalid. Return 401'); 
                    $json = ['message' => 'Unauthorized'];
                    return response()->json($json, 401);
                }else {
                    \Log::channel($this->logchannel)->info( 'Token is Valid.'); 
                }
        
            }catch(\Exception $e) {
                \Log::channel($this->logchannel)->error(__FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage());
                $json = ['message' => 'Unauthorized'];
                return response()->json($json, 401);
            }
           

        } catch (JWTException $e) {
            \Log::channel($this->logchannel)->error( 'JWTException: ' . $e->getMessage() );
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__  );
            return response()->json([
                'message' => 'Request unsuccessful. The following errors were found.',
                'errors' => [
                    'code' => 'E001',
                    'value' => 'Service unavailable'
                ],
            ], 503);
        
        }catch(\Exception $e) {
            \Log::channel($this->logchannel)->error( 'JWTException: ' . $e->getMessage() );
            \Log::channel($this->logchannel)->error( __FILE__ . ' ' . __LINE__  );
            return response()->json([
                'message' => 'Request unsuccessful. The following errors were found.',
                'errors' => [
                    'code' => 'E001',
                    'value' => 'Service unavailable'
                ],
            ], 503);
        }

        return $next($request);
    }
}
