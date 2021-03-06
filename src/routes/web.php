<?php

Route::group(['namespace' => 'Dorcas\ModulesFinance\Http\Controllers', 'middleware' => ['web','auth'], 'prefix' => 'mfn'], function() {
    Route::get('finance-main', 'ModulesFinanceController@index')->name('finance-main');
    Route::get('/finance-accounts', 'ModulesFinanceController@accounts')->name('finance-accounts');
    Route::get('/finance-accounts/{id}', 'ModulesFinanceController@accounts');
    Route::post('/finance-accounts/{id}', 'ModulesFinanceController@accounts_create');
    Route::get('/finance-entries', 'ModulesFinanceController@entries')->name('finance-entries');
    Route::post('/finance-entries', 'ModulesFinanceController@entries_create');
    Route::get('/finance-entries/{id}', 'ModulesFinanceController@entries_show')->name('finance-entries-confirmation');
    Route::get('/finance-reports', 'ModulesFinanceController@reports')->name('finance-reports');
    Route::get('/finance-reports/{id}', 'ModulesFinanceController@reports_show_manager')->name('finance-reports-show');
    Route::get('/finance-reports-configure', 'ModulesFinanceController@reports_configure')->name('finance-reports-configure');
    Route::post('/finance-reports-configure', 'ModulesFinanceController@reports_configure_post');
    Route::get('/finance-reports-configure/{id}', 'ModulesFinanceController@reports_configure')->name('finance-report-configure');
    Route::post('/finance-reports-configure/{id}', 'ModulesFinanceController@reports_configure_post');
    Route::post('/finance-install', 'ModulesFinanceController@accounts_install');
    Route::put('/finance-accounts/{id}', 'ModulesFinanceController@accounts_update');
    Route::get('/finance-entries-search', 'ModulesFinanceController@entries_search')->name('finance-entries-search');
    Route::delete('/finance-entries/{id}', 'ModulesFinanceController@entries_delete');
    Route::put('/finance-entries/{id}', 'ModulesFinanceController@entries_update');
    Route::post('/finance-reports', 'ModulesFinanceController@reports_create');
    Route::post('/finance-reports/generate', 'ModulesFinanceController@reports_generate');
    Route::get('/finance-tags', 'ModulesFinanceController@tags')->name('finance-tags');
    Route::get('/finance-tags-reports', 'ModulesFinanceController@tags_reports')->name('finance-tags-reports');

    Route::post('/finance-transtrak/fetch', 'Finance\Transtrak@fetch');
    Route::post('/finance-transtrak/login', 'Finance\Transtrak@login');
    Route::post('/finance-transtrak/enable-auto-processing', 'Finance\Transtrak@enableAutoProcessing');
});


/*

    Route::delete('/finance/accounts/{id}', 'Finance\Accounts@delete');


*/


?>