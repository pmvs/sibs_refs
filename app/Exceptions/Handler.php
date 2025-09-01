<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Debug\Exception\FatalErrorException;
use Symfony\Component\ErrorHandler\Error\FatalError;
use Illuminate\Http\Request;
use Symfony\Component\ErrorHandler\ErrorRenderer\HtmlErrorRenderer;
use Symfony\Component\ErrorHandler\Exception\FlattenException;
use App\Mail\ExceptionOccured;
use Mail;



class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    private $logchannel = 'exceptions';
    private $responseApiCodeE001 = [
        'message' => 'Request unsuccessful. The following errors were found.',
        'errors' => [
            [
                'code' => 'E001',
                'value' => 'Service unavailable'
            ]
        ]
    ];


    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {

        $this->reportable(function (Throwable $e) {


            \Log::channel($this->logchannel)->error('reportable************************************');
          
            $exception_class = get_class($e);
            \Log::channel($this->logchannel)->error('Class: ' . $exception_class);
            $exception_code = $e->getCode();
            \Log::channel($this->logchannel)->error('Code: ' . $exception_code);

            //  $this->sendEmail2($e); // sends an email
            //    if($e instanceof \Symfony\Component\Debug\Exception\FatalErrorException) {
            //         \Log::channel($this->logchannel)->error('reportable***********FatalErrorException****************');
            //        // return parent::render($request, $e);
            //     }
            return false;
        });

       
        $this->renderable(function (FatalError $e, Request $request) {
           
            \Log::channel($this->logchannel)->error('');
            \Log::channel($this->logchannel)->error('');
            \Log::channel($this->logchannel)->error('');
            \Log::channel($this->logchannel)->error('renderable*******************!!FatalError!!*****************');
          
          
            $this->logGeneralException($e);

            $this->logAddress();

            $this->logGeneralRequest($request);

            if ($request->is('api/*')) {

                \Log::channel($this->logchannel)->error('Request from API....');

                \Log::channel($this->logchannel)->error('Response JSON 503 : ' .json_encode($this->responseApiCodeE001) );
               
                return response()->json($this->responseApiCodeE001 , 503);

            }

            \Log::channel($this->logchannel)->error('Request from WEB....');
           
            \Log::channel($this->logchannel)->error('Response JSON 503 : ' .json_encode($this->responseApiCodeE001) );
               
            return response()->json($this->responseApiCodeE001 , 503);

        });

        $this->renderable(function (FatalErrorException $e, Request $request) {
           
            \Log::channel($this->logchannel)->error('');
            \Log::channel($this->logchannel)->error('');
            \Log::channel($this->logchannel)->error('');
            \Log::channel($this->logchannel)->error('renderable*******************!!FatalErrorException!!*****************');
          
            $this->logGeneralException($e);

            $this->logAddress();

            $this->logGeneralRequest($request);

            if ($request->is('api/*')) {

                \Log::channel($this->logchannel)->error('Request from API....');
           
                \Log::channel($this->logchannel)->error('Response JSON 503 : ' .json_encode($this->responseApiCodeE001) );
               
                return response()->json($this->responseApiCodeE001 , 503);

            }

            \Log::channel($this->logchannel)->error('Request from WEB....');
           
            \Log::channel($this->logchannel)->error('Response JSON 503 : ' .json_encode($this->responseApiCodeE001) );
            
            return response()->json($this->responseApiCodeE001 , 503);

        });
       
        $this->renderable(function (MethodNotAllowedHttpException $e, Request $request) {
           
            \Log::channel($this->logchannel)->error('');
            \Log::channel($this->logchannel)->error('');
            \Log::channel($this->logchannel)->error('');
            \Log::channel($this->logchannel)->error('renderable*******************!!MethodNotAllowedHttpException!!*****************');
           
            $this->logGeneralException($e);

            $this->logAddress();

            $this->logGeneralRequest($request);

            if ($request->is('api/*')) {

                \Log::channel($this->logchannel)->error('Request from API....');
           
                \Log::channel($this->logchannel)->error('Response JSON 503 : ' .json_encode($this->responseApiCodeE001) );
               
                return response()->json($this->responseApiCodeE001 , 503);

            }

            \Log::channel($this->logchannel)->error('Request from WEB....');
           
            \Log::channel($this->logchannel)->error('Response JSON 503 : ' .json_encode($this->responseApiCodeE001) );
           
            return response()->json($this->responseApiCodeE001 , 503);

        });

        $this->renderable(function (NotFoundHttpException $e, Request $request) {
           
            \Log::channel($this->logchannel)->error('');
            \Log::channel($this->logchannel)->error('');
            \Log::channel($this->logchannel)->error('');
            \Log::channel($this->logchannel)->error('renderable*******************!!NotFoundHttpException!!*****************');
     
            $this->logGeneralException($e);

            $this->logAddress();

            $this->logGeneralRequest($request);

            if ($request->is('api/*')) {

                \Log::channel($this->logchannel)->error('Request from API....');
           
                \Log::channel($this->logchannel)->error('Response JSON 503 : ' .json_encode($this->responseApiCodeE001) );
               
                // \Log::channel($this->logchannel)->error('Sleep 30s');
                // sleep(30);
                // \Log::channel($this->logchannel)->error('Responde!');

                return response()->json($this->responseApiCodeE001 , 503);


                // return response()->json([
                //     'message' => 'Request unsuccessful. The following errors were found.',
                //     'errors' => [
                //         [
                //             'code' => 'E001',
                //             'value' => 'Service unavailable'
                //         ]
                //     ]
                // ], 503);


                // $myObj  = new \stdClass();
                // $myObj->{'message'} = 'Request unsuccessful. The following errors were found.';
                // $myObj->{'errors'} = (object) [];
                // $list = [];
                // $item = [
                //     'code' => 'E001',
                //     'value' => 'Service unavailable'
                // ];
                // array_push($list, $item);
                // $myObj->{'errors'} = $list;


                // $myJSON = json_encode( $myObj, JSON_PRETTY_PRINT| JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES );
              
                
                // $errorArray[] = [
                //     'code' => 'E001',
                //     'value' => 'Service unavailable'
                // ];

               // return response($myJSON ,503 );

                //return response()->json($myObj, 503);

               // return response()->json($myObj, 503);

                // return response()->json([
                //     'message' => 'Request unsuccessful. The following errors were found.',
                //     'errors' =>  $errorArray
                // ], 503);

                // $responseData = [
                //     'message' => 'Request unsuccessful. The following errors were found.',
                //     'errors' => [
                //         [
                //             'code' => 'E001',
                //             'value' => 'Service unavailable'
                //         ]
                //     ]
                // ];

                //  $response = response()->json($responseData, 503);
                // $response->setData($responseData); // Manually encoding the array
                // return $response;
                
                // $responseData = [
                //     'message' => 'Request unsuccessful. The following errors were found.',
                //     'errors' => [
                //             'code' => 'E001',
                //             'value' => 'Service unavailable'
                //     ]
                // ];

                // $jsonencode = json_encode($responseData);

                // \Log::channel($this->logchannel)->error('Response 503 : ' . $jsonencode );
         
                // return response()->json($responseData,503);

                // return response()->json([
                //     'message' => 'Request unsuccessful. The following errors were found.',
                //     'errors' => [
                //         [
                //             'code' => 'E001',
                //             'value' => 'Service unavailable'
                //         ],
                //     ],
                // ], 503);

                //return response()->json($responseData,503);

                // return response()->api([
                //     'message' => 'Request unsuccessful. The following errors were found.',
                //     'errors' => $list
                // ], 503);

               // return response()->json($myJSON);
                // return response()->json($myJSON, 503);
                // return response()->json([
                //     'message' => 'Request unsuccessful. The following errors were found.',
                //     'errors' =>  [$list]
                // ], 503);

                // return response()->json([
                //     $myJSON
                // ], 503);

                // \Log::channel($this->logchannel)->error('Response 503 : ' . print_r([
                //     'message' => 'Request unsuccessful. The following errors were found.',
                //     'errors' => [
                //         [
                //             'code' => 'E001',
                //             'value' => 'Service unavailable'
                //         ],
                //     ],
                //     ], true) );
         
                    

                // return response()->json([
                //     'message' => 'Request unsuccessful. The following errors were found.',
                //     'errors' => [
                //         [
                //             'code' => 'E001',
                //             'value' => 'Service unavailable'
                //         ],
                //     ],
                // ], 503);

                // return response()->json([
                //     'message' => 'Method not found.'
                // ], 404);
            }
            \Log::channel($this->logchannel)->error('Request from WEB....');
           
            \Log::channel($this->logchannel)->error('Response JSON 503 : ' .json_encode($this->responseApiCodeE001) );
               
            return response()->json($this->responseApiCodeE001 , 503);

        });

        $this->renderable(function (Throwable $e,Request $request) {


            \Log::channel($this->logchannel)->error('renderable*******************!!Throwable!!*****************');
            
            $this->logGeneralException($e);

            $this->logAddress();

            $this->logGeneralRequest($request);
            
            if ($request->is('api/*')) {

                \Log::channel($this->logchannel)->error('Request from API....');
           
                \Log::channel($this->logchannel)->error('Response JSON 503 : ' .json_encode($this->responseApiCodeE001) );
               
                return response()->json($this->responseApiCodeE001 , 503);

            }
            
            \Log::channel($this->logchannel)->error('Request from WEB....');
           
            \Log::channel($this->logchannel)->error('Response JSON 503 : ' .json_encode($this->responseApiCodeE001) );
               
            return response()->json($this->responseApiCodeE001 , 503);

            // return response()->json([
            //     'message' => 'Errors found.'
            // ], 500);
        });


    }

    private function logAddress()
    {
        try {

            $remotehost = 0;
            if (isset($_SERVER['REMOTE_HOST'])) {
                $remotehost = $_SERVER['REMOTE_HOST'];
            }
            $remoteaddress = 0;
            if (isset($_SERVER['REMOTE_ADDR'])) {
                $remoteaddress = $_SERVER['REMOTE_ADDR'];
            }
            $localaddress = 0;
            if (isset($_SERVER['LOCAL_ADDR'])) {
                $localaddress = $_SERVER['LOCAL_ADDR'];
            }
            \Log::channel($this->logchannel)->error('REMOTE_HOST : ' . $remotehost);
            \Log::channel($this->logchannel)->error('REMOTE_ADDRESS : ' . $remoteaddress);
            \Log::channel($this->logchannel)->error('LOCAL_ADDRESS : ' . $localaddress);

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' ' . __LINE__ . ' ' . $e->getMessage());
        }
    }

    private function logGeneralRequest(Request $request)
    {
        try {

            \Log::channel($this->logchannel)->error( 'Url : ' .  request()->url() );
            //\Log::channel($this->logchannel)->error(  request()->all() );
 
        }catch(\Exception $ex) {
            \Log::channel($this->logchannel)->error(__FILE__ . ' ' . __LINE__ . ' ' . $ex->getMessage());
        }
        
    }

    private function logGeneralException(Throwable $e)
    {
        try {

            $exception_class = get_class($e);
            \Log::channel($this->logchannel)->error('Class: ' . $exception_class);
            if (method_exists($e, 'getStatusCode')) {
                $statusCode = $e->getStatusCode();
            } else {
                $statusCode = '000';
            }
            \Log::channel($this->logchannel)->error('Status Code: ' . $statusCode);
            if (method_exists($e, 'getCode')) {
                $exception_code = $e->getCode();
            } else {
                $exception_code = '0';
            }
            \Log::channel($this->logchannel)->error('Exception Code: ' . $exception_code);
            
            \Log::channel($this->logchannel)->error( 'Erro : ' . $e->getMessage() );
            \Log::channel($this->logchannel)->error( 'Ficheiro : ' .$e->getFile() );
            \Log::channel($this->logchannel)->error( 'Linha : ' .$e->getLine() );

            // if (config('app.debug')) {
            //     \Log::channel($this->logchannel)->error( 'Trace : ' . print_r($e->getTrace(), true) );
            //     \Log::channel($this->logchannel)->error( 'Code : ' .$e->getCode() );
            // }

        }catch(\Exception $ex) {
            \Log::channel($this->logchannel)->error(__FILE__ . ' ' . __LINE__ . ' ' . $ex->getMessage());
        }
        

    }

    private function handleApiException($request, Throwable $exception)
    {
        \Log::channel($this->logchannel)->error('**********handleApiException******************');

        try {
            // $exception = $this->prepareException($exception);

            // if ($exception instanceof FatalError) {
            //     $exception = $exception->getResponse();
            // }
            // if ($exception instanceof FatalErrorException) {
            //     $exception = $exception->getResponse();
            // }
            // if ($exception instanceof MethodNotAllowedHttpException) {
            //     $exception = $exception->getResponse();
            // }
            // if ($exception instanceof NotFoundHttpException) {
            //     $exception = $exception->getResponse();
            // }
            // if ($exception instanceof Throwable) {
            //     $exception = $exception->getResponse();
            // }
    
            return $this->customApiResponse($exception);

        }catch(\Exception $e){
            \Log::channel($this->logchannel)->error(__FILE__ . ' ' .  __LINE__ . ' ' . $e->getMessage());
            return;
        }
       

    }

    private function customApiResponse($exception)
    {
        \Log::channel($this->logchannel)->error('**********customApiResponse******************');


        if (method_exists($exception, 'getStatusCode')) {
            $statusCode = $exception->getStatusCode();
        } else {
            $statusCode = 500;
        }

        $response = [];

        switch ($statusCode) {
            case 401:
                $response['message'] = 'Unauthorized';
                break;
            case 403:
                $response['message'] = 'Forbidden';
                break;
            case 404:
                $response['message'] = 'Not Found';
                break;
            case 405:
                $response['message'] = 'Method Not Allowed';
                break;
            case 422:
                $response['message'] = $exception->original['message'];
                $response['errors'] = $exception->original['errors'];
                break;
            default:
                $response['message'] = ($statusCode == 500) ? 'Whoops, looks like something went wrong' : $exception->getMessage();
                break;
        }

        if (config('app.debug')) {
            $response['trace'] = $exception->getTrace();
            $response['code'] = $exception->getCode();
        }

        $response['status'] = $statusCode;

        return response()->json($response, $statusCode);
    }

    public function sendEmail(Throwable $exception)
    {
       try {
            $e = FlattenException::create($exception);
            $handler = new HtmlErrorRenderer(true); // boolean, true raises debug flag...
            $css = $handler->getStylesheet();
            $content = $handler->getBody($e);
            \Mail::send('emails.exception', compact('css','content'), function ($message) {
                $message->to(['pmvsant@gmail.com','pmvsant@gmail.com'])
                                    ->subject('Exception: ' . \Request::fullUrl());
            });
        } catch (Exception $exception) {
            \Log::channel($this->logchannel)->error($exception);
        }
    }

    public function sendEmail2(Throwable $exception)
    {
       try {
            \Log::channel($this->logchannel)->error('Sending email...');
            $content['message'] = $exception->getMessage();
            $content['file'] = $exception->getFile();
            $content['line'] = $exception->getLine();
            $content['trace'] = $exception->getTrace();
  
            $content['url'] = request()->url();
            $content['body'] = request()->all();
            $content['ip'] = request()->ip();
   
            \Mail::send('emails.exception', compact('content'), function ($message) {
                $message->to(['pmvsant@gmail.com','pmvsant@gmail.com'])
                                    ->subject('Exception: ' . \Request::fullUrl());
            });

           // \Mail::to('pmvsant@gmail.com')->send(new \App\Mail\ExceptionOccured($content));

        } catch (Exception $exception) {
            \Log::channel($this->logchannel)->error('Excecão');
            \Log::channel($this->logchannel)->error($exception->getMessage());
   
        } catch (Throwable $exception) {
            \Log::channel($this->logchannel)->error('Throwable');
            \Log::channel($this->logchannel)->error($exception);
        }
    }
 
}
