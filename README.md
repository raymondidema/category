# Category

Category Table (Recursive) (PostgreSQL only)

## Installation

     composer.json

     "require": {
		"laravel/framework": "4.1.*",
		"raymondidema/category": "dev-master"
	},


After updating you could do ``composer update`` or ``composer install``
or alternatively ``composer require raymondidema/category``

## Config

     ./app/config/app.php
     
     'providers' => array( 'Raymondidema\Category\CategoryServiceProvider' );
     'aliases' => array( 'Menustructure' => 'Raymondidema\Category\Facades\Category' );


### Children

     Menustructure::table('categories')
                    ->children($id)
                    ->depth(3)
                    ->get();
     
### Descendants

     Menustructure::table('categories')
                    ->decendants($id)
                    ->depth(2)
                    ->where('name', 'aspire')
                    ->get();
     
### Ancestors

     Menustructure::table('categories')->ancestors($id)->get(array('name','slug'));

### Database layout example

Category requires the following columns: id, parent_id, position

     Schema::create('categories', function(Blueprint $table)
     	{
     		$table->increments('id');
     		$table->integer('parent_id')->unsigned()->nullable();
     		$table->string('name');
     		$table->string('slug');
     		$table->integer('position')->unsigned()->nullable();
     		$table->timestamps();
     		$table->softDeletes();
     	});

### How to interact with a model

this is not required.

     <?php

     use \Raymondidema\Category\Models\Reloquent;

     class Category extends Reloquent
     {
     	// do stuff here!!!
     }

