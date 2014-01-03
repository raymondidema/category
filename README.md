# Category
========

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
