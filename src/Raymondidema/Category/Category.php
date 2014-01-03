<?php namespace Raymondidema\Category;

use Raymondidema\Category\Models\Category as Cat;

class Category
{
	protected $category;

	public function __construct(Cat $cat)
	{
		$this->category = $cat;
	}

	/**
	 * Get children from row
	 * @param  integer $id         ID is required if you want to search for children
	 * @param  integer $depth      Max depth of tree
	 * @param  array   $attributes List of attributes in the select query
	 * @return object              Returns the object
	 */
	public function children($id, $depth = 1, $attributes = array('*'))
	{
		return $this->category->children($id, $depth, $attributes);
	}

	/**
	 * Same as Children
	 * @param  integer $id         ID is required if you want to search for children
	 * @param  integer $depth      Max depth of tree
	 * @param  array   $attributes List of attributes in the select query
	 * @return object              Returns the object
	 */
	public function decendants($id, $depth = 1, $attributes = array('*'))
	{
		return $this->category->children($id, $depth, $attributes);
	}

	/**
	 * Get all the Ancestors
	 * @param  integer $id        ID is required if you want to get the Ancestors
	 * @param  array  $attributes List of attributes in the select query
	 * @return object             Returns the object
	 */
	public function ancestors($id, $attributes = array('*'))
	{
		return $this->category->ancestors($id, $attributes);
	}
}