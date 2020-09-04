<?php

namespace Dorcas\ModulesFinance;
use Illuminate\Support\ServiceProvider;

class ModulesFinanceServiceProvider extends ServiceProvider {

	public function boot()
	{
		$this->loadRoutesFrom(__DIR__.'/routes/web.php');
		$this->loadViewsFrom(__DIR__.'/resources/views', 'modules-finance');
		$this->publishes([
			__DIR__.'/config/modules-finance.php' => config_path('modules-finance.php'),
		], 'dorcas-modules');
		/*$this->publishes([
			__DIR__.'/assets' => public_path('vendor/modules-finance')
		], 'dorcas-modules');*/
	}

	public function register()
	{
		//add menu config
		$this->mergeConfigFrom(
	        __DIR__.'/config/navigation-menu.php', 'navigation-menu.modules-finance.sub-menu'
	     );
	}

}


?>