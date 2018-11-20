<?php

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

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/test/send', 'Api\LineBotController@getForm');
Route::post('/test/send', 'Api\LineBotController@testSendLine')->name('test.send.line');
Route::get('/test/create-rich-menu', 'Api\LineBotController@getFormRichMenu');
Route::post('/test/create-rich-menu', 'Api\LineBotController@testRichMenu')->name('test.rich.menu');

Route::get('/test/list-rich-menu', 'Api\LineBotController@getListRichMenu')->name('get.list.rich.menu');