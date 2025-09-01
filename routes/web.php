<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Route::get('/', function () {
//     return view('welcome');
// });

// Route::middleware(['domain'])->group(function () {
//     Route::get('/', function (\Illuminate\Http\Request $request) {
//         if ($request->get('domain') === 'plqual') {
//             return view('welcome');
//         } elseif ($request->get('domain') === 'vop-cert') {
//             return view('welcome-vop');
//         }
//     });
// });

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
     * PROXY LOOKUP
     * Summary: 
     * Notes: 
     * Output-Formats: [application/json]
    */
    Route::get('/pl/notification/health',  [ \App\Http\Controllers\Api\ProxyLookupApi::class, 'getHealth'])->middleware('json.response');
    Route::post('/pl/notification/removed_association/',  [ \App\Http\Controllers\Api\ProxyLookupApi::class, 'removedAssociation'])->middleware('json.response','jwt.verify');
    Route::post('/pl/notification/expired_association/',  [ \App\Http\Controllers\Api\ProxyLookupApi::class, 'expiredAssociation'])->middleware('json.response','jwt.verify');


Route::get('/', function () {
    return view('welcome');
});

// Route::middleware(['domain:plqual'])->group(function () {
//     // routes for plqual.ccammafra.pt
//     Route::get('/', function () {
//         return view('welcome');
//     });
// });

// Route::middleware(['domain:vop-cert'])->group(function () {
//     // routes for vop-cert.ccammafra.pt
//     Route::get('/', function () {
//         return view('welcome-vop');
//     });
// });

// Route::group(['domain' => 'plqual'], function () {
//     // routes for plqual.ccammafra.pt
//     Route::get('/', function () {
//         return view('welcome');
//     });
// });

// Route::group(['domain' => 'vop-cert'], function () {
//     // routes for vop-cert.ccammafra.pt
//     Route::get('/', function () {
//         return view('welcome-vop');
//     });
// });





// Route::get('/dashboard', function () {
//     return view('dashboard');
// })->middleware(['auth', 'verified'])->name('dashboard');

// Route::middleware('auth')->group(function () {
//     Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
//     Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
//     Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
// });

require __DIR__.'/auth.php';
