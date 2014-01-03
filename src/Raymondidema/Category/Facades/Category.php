<?php namespace Raymondidema\Category\Facades;

use Illuminate\Support\Facades\Facade;

class Category extends Facade
{
	protected static function getFacadeAccessor() { return 'category'; }
}