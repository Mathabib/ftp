<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\FTPController;
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



Route::view('/', 'ftp-login')->name('ftp.login');
Route::post('/connect', [FTPController::class, 'connect'])->name('ftp.connect');
Route::post('/disconnect', [FTPController::class, 'disconnect'])->name('ftp.disconnect');
Route::get('/browse/{path?}', [FTPController::class, 'browse'])->where('path', '.*')->name('ftp.browse');
Route::post('/upload', [FTPController::class, 'upload'])->name('ftp.upload');
Route::post('/create-folder', [FTPController::class, 'createFolder'])->name('ftp.createFolder');
Route::post('/delete', [FTPController::class, 'delete'])->name('ftp.delete');
Route::post('/rename', [FTPController::class, 'rename'])->name('ftp.rename');
Route::get('/download/{path}', [FTPController::class, 'download'])->where('path', '.*')->name('ftp.download');

Route::post('/test', [FTPController::class, 'test'])->name('ftp.test');

