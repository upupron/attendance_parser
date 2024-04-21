<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/', 'App\Http\Controllers\UploadController@showForm')->name('upload.form');
Route::get('/upload', 'App\Http\Controllers\UploadController@showForm')->name('upload.form');
Route::post('/upload', 'App\Http\Controllers\UploadController@upload')->name('upload');
Route::get('/display', 'App\Http\Controllers\DisplayController@display')->name('display');

