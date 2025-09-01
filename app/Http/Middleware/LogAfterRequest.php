<?php 

namespace App\Http\Middleware;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class LogAfterRequest {

	private $startTime;
	private $logger;

	public function __construct( ) {
       //Log::info('__construct LogAfterRequest');
    }

	public function handle($request, \Closure  $next)
	{
		$this->startTime = microtime(true);

		//Log::info('handle LogAfterRequest' . print_r( $request->all(), true));
		return $next($request);
		

	}

	public function terminate($request, $response)
	{
		//Log::info('terminate LogAfterRequest');
		try {
		
			//if ($request->hasSession()) {
			
				// $sessionid = strip_tags ($request->session()->getId());
				// $sessiontoken = strip_tags ($request->session()->get('_token'));
		
				$endTime = microtime(true);

				// Increment the request count in cache
				//$requestCount = Cache::increment('request_count');
				// // Log the request data
				// $requestData = [
				// 	'method' => $request->method(),
				// 	'path' => $request->path(),
				// 	'timestamp' => now()->toDateTimeString(),
				// ];
				// // Store request data in cache with a unique key
				// Cache::put('request_' . $requestCount, $requestData);

				//$requestCount = Cache::get('request_count', 0);
		
				$ua = htmlentities( $request->header('User-Agent'), ENT_QUOTES, 'UTF-8');
				// $arr_browsers = ["Opera", "Edge", "Chrome", "Safari", "Firefox", "MSIE", "Trident"];
				// $sessionid = strip_tags ($request->session()->getId());
				// $sessiontoken = strip_tags ($request->session()->get('_token'));
				// $contrato = strip_tags ( $request->session()->get('contrato') );
				// $endTime = microtime(true);
	
				// $fullurl = $request->fullUrl();
				// $requestAllJson = json_encode($request->all());
				// // Log::info( 'request->fullUrl: ' . $fullurl );
				// // Log::info( 'json_encode($request->all()) : ' .  $requestAllJson );
				// $fullurl = htmlentities(  $request->fullUrl() , ENT_QUOTES, 'UTF-8');
				// $requestAllJson = htmlentities( json_encode($request->all()) , ENT_QUOTES, 'UTF-8');
				// // Log::info( 'htmlentities request->fullUrl: ' . $fullurl );
				// // Log::info( 'htmlentities json_encode($request->all()) : ' .  $requestAllJson );

				// $responseAllJson = json_encode($response);
				
				// //$macAddres = exec('getmac');
				// $headers = collect($request->header())->transform(function ($item) {
				// 	return $item[0];
				// });



				$port = 0;
				if (isset($_SERVER['REMOTE_PORT'])) {
					$port = $_SERVER['REMOTE_PORT'];
				}
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

				$monitoring = ( str_starts_with(  trim($ua), 'Blackbox') ? 'true' : 'false' );

				if ( $monitoring === 'true' ) {

					//$requestMonitoringCount = Cache::increment('request_monitoring_count');

					//$requestMonitoringCount = Cache::get('request_monitoring_count', 0);

					\Log::channel('monitoring')->info('app.requests' . PHP_EOL, 
					[
						'URL : ' . trim($request->url())  . "\n",
						'IP Address' => $request->ip()  . "\n",
						'Remote Host' => trim($remotehost)  . "\n",
						'Remote Address' => $remoteaddress  . "\n",
						'Local Address ' => $localaddress  . "\n",
						'Port' => $port . "\n",
						'User Agent' => trim($ua) . "\n",
						//'Count' => $requestMonitoringCount . "\n",
						'Request2' => $request->fullUrl()  . "\n",
						'Request3' => json_encode($request->all())  . "\n",
						'Method: '  => $request->method() . "\n", 
						'Response' =>$response->getContent()  . "\n",
						'Status' => $response->status(). "\n",
						'Duration: '  =>  number_format($endTime - LARAVEL_START, 3) . "\n",
					]);
				}else {
					Log::info('app.requests' . PHP_EOL, 
					[
						'URL : ' . trim($request->url())  . "\n",
						'IP Address' => $request->ip()  . "\n",
						'Remote Host' => trim($remotehost)  . "\n",
						'Remote Address' => $remoteaddress  . "\n",
						'Local Address ' => $localaddress  . "\n",
						'Port' => $port . "\n",
						'User Agent' => trim($ua) . "\n",
						//'Count' => $requestCount . "\n",
						'Request2' => $request->fullUrl()  . "\n",
						'Request3' => json_encode($request->all())  . "\n",
						'Method: '  => $request->method() . "\n", 
						'Response' =>$response->getContent()  . "\n",
						'Status' => $response->status(). "\n",
						'Duration: '  =>  number_format($endTime - LARAVEL_START, 3) . "\n",
					]);
				}

				// Log::info('app.requests' . PHP_EOL, 
				// 	[
				// 		'URL : ' . trim($request->url())  . "\n",
				// 		'IP Address' => $request->ip()  . "\n",
				// 		'Remote Host' => trim($remotehost)  . "\n",
				// 		'Remote Address' => $remoteaddress  . "\n",
				// 		'Local Address ' => $localaddress  . "\n",
				// 		'Port' => $port . "\n",
				// 		'User Agent' => trim($ua) . "\n",
				// 		'Monitoring' => $monitoring. "\n",
				// 		'Request2' => $request->fullUrl()  . "\n",
				// 		'Request3' => json_encode($request->all())  . "\n",
				// 		'Method: '  => $request->method() . "\n", 
				// 		'Response' =>$response->getContent()  . "\n",
				// 		'Status' => $response->status(). "\n",
				// 		'Duration: '  =>  number_format($endTime - LARAVEL_START, 3) . "\n",
				// 	]);
			

			//}

		} catch(Exception $e) {
			Log::error($e->getMessage());
		}
	
	}

}