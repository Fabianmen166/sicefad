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



Route::middleware(['lang'])->group(function(){
    Route::prefix('lscefa')->group(function () {
        Route::get('/index', 'LSCEFAController@index')->name('cefa.lscefa.index');
        Route::get('/admin/welcome', 'LSCEFAController@admin')->name('lscefa.admin.welcome');
        Route::get('/intern/welcome', 'LSCEFAController@intern')->name('lscefa.intern.panelpas');

});
});