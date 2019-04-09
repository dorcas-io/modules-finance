<?php

/*Route::group(['namespace' => 'Dorcas\ModulesFinance\Http\Controllers', 'middleware' => ['web']], function() {
    Route::get('sales', 'ModulesFinanceController@index')->name('sales');
});*/




Route::group(['middleware' => ['auth'], 'namespace' => 'Finance', 'prefix' => 'apps/finance'], function () {
    Route::get('/', 'Accounts@index')->name('apps.finance');
    Route::get('/entries', 'Entries@index')->name('apps.finance.entries');
    Route::post('/entries', 'Entries@create');
    Route::get('/entries/{id}', 'Entries@showEntry')->name('apps.finance.entry.confirmation');
    Route::post('/entries/{id}', 'Entries@update');
    
    Route::group(['middleware' => ['pay_gate']], function () {
        Route::get('/reports', 'Reports@index')->name('apps.finance.reports');
        Route::get('/reports/configure', 'ConfigureReport@index')->name('apps.finance.reports.configure');
        Route::post('/reports/configure', 'ConfigureReport@configure');
        Route::get('/reports/configure/{id}', 'ConfigureReport@index');
        Route::post('/reports/configure/{id}', 'ConfigureReport@configure');
        Route::get('/reports/{id}', 'Reports@showReportsManager')->name('apps.finance.reports.documents');
    
        Route::get('/transtrak', 'Transtrak@index')->name('apps.finance.transtrak');
    });
    
    Route::get('/{id}', 'Accounts@index');
    Route::post('/{id}', 'Accounts@create');
});


?>