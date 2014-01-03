<?php namespace Raymondidema\Category;

use Illuminate\Support\ServiceProvider;
use Raymondidema\Category\Models\Category as Cat;

class CategoryServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('raymondidema/category');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app['category'] = $this->app->share(function($app)
		{
			return new Category(new Cat);
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('category');
	}

}