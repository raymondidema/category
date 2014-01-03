# Category

Category Table (Recursive) (PostgreSQL only)


## Config

     ./app/config/app.php
     
     'providers' => array( 'Raymondidema\Category\CategoryServiceProvider' );
     'aliases' => array( 'Categories' => 'Raymondidema\Category\Facades\Category' );


### Children

     Categories::children($id, $depth = 1, $attributes = array('*'));
     
### Descendants

     Categories::decendants($id, $depth = 1,  $attributes = array('*'));
     
### Ancestors

     Categories::ancestors($id, $attributes = array('*'));

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

### How to interact with the model

     <?php

     use \Raymondidema\Category\Models\Category as Codequent;

     class Category extends Codequent
     {
     	// do stuff here!!!
     }

