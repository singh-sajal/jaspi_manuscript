<?php

use App\Http\Controllers\Admin\Auth\AdminAuthController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\VerificationCodeController;
use App\Http\Controllers\Web\HomeController;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

// Redirect to the login page route
Route::get('/', function () {
    return view('home');
});

Route::get('home', function () {
    return view('home');
})->name('home');

// Route::match(['get', 'post'], 'login', [AdminAuthController::class, 'login'])->name('login');


// Route::get('newhome', function () {
//     return view('newhome');
// })->name('newhome');

Route::get('file/view/frame', FileController::class)->name('file.viewframe');
// Sending verification code to the user in each auth module (admin, user, etc)
Route::get('resend/verification_code', VerificationCodeController::class)
    ->name('verification.resend-code');

Route::post('contact', [HomeController::class, 'contactSave'])->name('web.contact');
Route::get('contact', [HomeController::class, 'contact'])->name('web.contact');

Route::match(['get', 'post'], 'forgot_password', [HomeController::class, 'forgot_password'])->name('forgot_password');
