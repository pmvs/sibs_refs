<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


 //rotas para os novos testes PL 2025:03-24
Route::group(['namespace'=>'api', 'prefix' => 'v2'], function() {

    Route::get('/testes', [ \App\Http\Controllers\TestController::class, 'teste_v2'] )->middleware('log.route','cors','json.response','XSS','gba');
    Route::post('/testes', [ \App\Http\Controllers\TestController::class, 'execTests'] )->name('testes.v2.post')->middleware('log.route','cors','json.response','XSS','gba');

});

Route::group(['namespace'=>'Api', 'prefix' => 'v1'], function() {
   
    // Route::post('login', [ \App\Http\Controllers\Api\AuthenticationController::class,  'store']);
    // Route::post('logout', [ \App\Http\Controllers\Api\AuthenticationController::class, 'destroy'])->middleware('auth:api');
    // Route::get('sha1', [ \App\Http\Controllers\Api\AuthenticationController::class, 'calculaSHA1']);
    // Route::get('certificado', [ \App\Http\Controllers\Api\AuthenticationController::class, 'certificado']);
    //Route::get('certificado-info', [ \App\Http\Controllers\Api\AuthenticationController::class, 'certificadoInfo']);
    // Route::get('certificado-valido', [ \App\Http\Controllers\Api\AuthenticationController::class, 'certificadoValido']);
    // Route::post('token', [ \App\Http\Controllers\Api\AuthenticationController::class, 'createToken']);
    // Route::post('generate-jwt-token', [ \App\Http\Controllers\Api\AuthenticationController::class, 'generateJwtToken']);
   
    // Route::get('get-token-adfs', [ \App\Http\Controllers\Api\AuthenticationController::class, 'getTokenFromADFS']);

    // Route::post('secret', [ \App\Http\Controllers\Api\AuthenticationController::class, 'createSecret']);
    // Route::get('health', [ \App\Http\Controllers\Api\AuthenticationController::class, 'getHealth']);
    // Route::post('testpost', [ \App\Http\Controllers\Api\AuthenticationController::class, 'testPost']);

    //get public key from adfs
    // Route::get('bdp/read/keys', [ \App\Http\Controllers\Api\AuthenticationController::class, 'readPublicKeyfromADFS']);

    //teste de conetividade PSP-BDP
    //Route::get('/bdp/invocaHealth/{type}', [ \App\Http\Controllers\Api\AuthenticationController::class, 'invocaHealthBDP']);

    //teste de conetividade PSP-BDP
    // Route::get('/bdp/authorize', [ \App\Http\Controllers\Api\AuthenticationController::class, 'getAuthorizationFromADFS']);

    //upload SQL COPs
    Route::post('/cop/upload', [ \App\Http\Controllers\UploadController::class, 'getUploadFile']);
  
    //contas encerradas
    Route::post('/pl/contaencerrada/{identificador}/{nif}/{iban}', [ \App\Http\Controllers\GbaController::class, 'contaEncerrada'])->middleware('cors','gba');;

    //testes
    Route::get('/testa/{nometeste}/{parametro}', [ \App\Http\Controllers\TestController::class, 'testa'] )->middleware('log.route','cors','json.response','XSS','gba');
    //Route::get('/teste/{nometeste}/{parametro}/{valor}', 'TestController@testavalor')->name('testa.valor')->middleware('cors','json.response','xss','gba', 'checkgbaheader');
    Route::get('/testes', [ \App\Http\Controllers\TestController::class, 'testes'] )->name('testes')->middleware('cors','gba');
    Route::post('/testes', [ \App\Http\Controllers\TestController::class, 'testespost'] )->name('testes.post')->middleware('cors','gba');
    Route::post('/testes/associar', [ \App\Http\Controllers\TestController::class, 'testesassociar'] )->name('testes.associar.post')->middleware('cors','gba');
    Route::post('/testes/copb', [ \App\Http\Controllers\TestController::class, 'testescopb'] )->name('testes.copb')->middleware('cors','gba');

    // Route::get('/testes/carga', [ \App\Http\Controllers\TestController::class, 'testescarga'] )->name('testes.carga')->middleware('cors','gba');
    // Route::post('/testes/carga', [ \App\Http\Controllers\TestController::class, 'testescargaplpost'] )->name('testes.carga.pl.post')->middleware('cors','gba');

    //rotas para os novos testes PL 2025:03-24
    Route::get('/testa/{nometeste}/{parametro}', [ \App\Http\Controllers\TestController::class, 'testa'] )->middleware('log.route','cors','json.response','XSS','gba');


    Route::group(['prefix'=>'hb'], function() {
        Route::group(['prefix'=>'pl'], function() {
            Route::post('/insert', [ \App\Http\Controllers\Api\ProxyLookupApi::class, 'inserePL']);
            Route::post('/delete', [ \App\Http\Controllers\Api\ProxyLookupApi::class, 'removePL']);
            Route::post('/confirmation', [ \App\Http\Controllers\Api\ProxyLookupApi::class, 'confirmPL']);
            Route::post('/contacts', [ \App\Http\Controllers\Api\ProxyLookupApi::class, 'contactsPL']);
            Route::post('/account', [ \App\Http\Controllers\Api\ProxyLookupApi::class, 'accountPL']);
            Route::post('/reativate', [ \App\Http\Controllers\Api\ProxyLookupApi::class, 'reativatePL']);
            Route::post('/eliminate', [ \App\Http\Controllers\Api\ProxyLookupApi::class, 'eliminatePL']);
        });
        Route::group(['prefix'=>'cop'], function() {
            Route::post('/getNomePrimeiroTitular', [ \App\Http\Controllers\Api\CopAPi::class, 'getNomePrimeiroTitular']);
        });
    });


});



Route::middleware(['domainnew:plqual.ccammafra.pt'])->group(function () {
    Route::get('/test', function (Request $request) {
        return response()->json(['message' => 'Welcome to plqual API']);
    });
});




Route::middleware(['domainnew:vop-cert.ccammafra.pt'])->group(function () {
    Route::group(['namespace'=>'api', 'prefix' => 'vop'], function() {
        Route::get('/test', function (Request $request) {
            return response()->json(['message' => 'Welcome to vop-cert API']);
        });
        Route::get('/health',  [ \App\Http\Controllers\Api\VopApi::class, 'getHealth'])->middleware('json.response');
    });
});

Route::group(['namespace'=>'Api'], function() {

    /**
     * CONP
     * Summary: 
     * Notes: 
     * Output-Formats: [application/json]
    */
    Route::get('/conp/health',  [ \App\Http\Controllers\Api\CopAPi::class, 'getHealth'])->middleware('json.response');
    Route::post('/conp/cops',  [ \App\Http\Controllers\Api\CopAPi::class, 'postCops'])->middleware('json.response', 'checkip','jwt.verify','throttle:none');
    Route::post('/conp/copb',  [ \App\Http\Controllers\Api\CopAPi::class, 'postCopb'])->middleware('json.response','checkip','jwt.verify','throttle:none');
    
     /**
     * CONP
     * Summary: Indisponibilidades 
     * Notes: 
     * Output-Formats: [application/json]
    */
    Route::post('/conp/unavailability/list',  [ \App\Http\Controllers\Api\CopAPi::class, 'unavailabilityList'])->middleware('json.response','checkip','jwt.verify','throttle:none');

     /**
     * PROXY LOOKUP
     * Summary: 
     * Notes: 
     * Output-Formats: [application/json]
    */
    Route::get('/pl/notification/health',  [ \App\Http\Controllers\Api\ProxyLookupApi::class, 'getHealth'])->middleware('json.response');
    Route::post('/pl/notification/removed_association/',  [ \App\Http\Controllers\Api\ProxyLookupApi::class, 'removedAssociation'])->middleware('json.response','jwt.verify');
    Route::post('/pl/notification/expired_association/',  [ \App\Http\Controllers\Api\ProxyLookupApi::class, 'expiredAssociation'])->middleware('json.response','jwt.verify');

});

