<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\SocketController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::group(['namespace' => 'Api'], function (){
    Route::post('/auth/getToken' , [AuthController::class , 'getToken']);
    Route::post('/auth/code',[AuthController::class,'code']);
    Route::post('/auth/getUserModelLis' , [AuthController::class , 'getUserModelLis']);
    Route::post('/auth/mobileAuth' , [AuthController::class , 'mobileAuth']);
    Route::post('/auth/getOwnCodeQr' , [AuthController::class , 'getOwnCodeQr']);
    Route::post('/auth/getModelToken' , [AuthController::class , 'getModelToken']);
    Route::post('/auth/ttsPersonal' , [AuthController::class , 'ttsPersonal']);
    Route::post('/auth/getVoicePersonLis' , [AuthController::class , 'getVoicePersonLis']);
    Route::post('/auth/tts' , [AuthController::class , 'tts']);
    Route::post('/auth/submitModel' , [AuthController::class , 'submitModel']);
    Route::post('/auth/getUserInfo' , [AuthController::class , 'getUserInfo']);
    Route::post('/upload/vioceFile',[UploadController::class,'vioceFile']);
    Route::post('/upload/getUploadFile',[UploadController::class,'getUploadFile']);
    Route::post('/socket/vioceChange',[SocketController::class,'vioceChange']);
});
