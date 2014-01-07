<?php namespace Raymondidema\Category;

use \Illuminate\Support\Facades\DB;
use \Illuminate\Support\Facades\Cache;

class Category
{
	/**
	 * Shows the root true or false;
	 * @var boolean
	 */
	protected $root = false;

	/**
	 * Depth to retreive the tree default = 0
	 * @var integer
	 */
	protected $depth = 2;

	/**
	 * Table name
	 * @var string
	 */
	protected $table;

	/**
	 * The Object
	 * @var mixed
	 */
	protected $data;

	/**
	 * Build Query
	 * @var object
	 */
	protected $query;

	protected $query_extended;

	/**
	 * Id
	 * @var int
	 */
	protected $id;

	/**
	 * Order by
	 * @var string
	 */
	protected $order = ' ORDER BY level';

	/**
	 * Extra parameters
	 * @var array
	 */
	protected $parameters = array();

	/**
	 * cachename
	 * @var string md5
	 */
	protected $cacheName;

	/**
	 * Collection
	 * @var object
	 */
	protected $collection;

	/**
	 * where method
	 * @var array
	 */
	protected $where = array();

	/**
	 * Table selector
	 * @param  string $table Sets the tablename
	 * @return object        returns the object
	 */
	public function table($table)
	{
		$this->table = $table;
		return $this;
	}

	/**
	 * Gets the children
	 * @param  int    $id ID root
	 * @return object     Returns the object
	 */
	public function children($id)
	{
		$this->decendants($id);
		return $this;
	}

	/**
	 * Children with root
	 * @param  int    $id ID root
	 * @return object     Returns the object
	 */
	public function childrenWithRoot($id)
	{
		$this->root = true;
		$this->decendants($id);
		return $this;
	}

	/**
	 * Decendants
	 * @param  int $id ID of the root
	 * @return mixed
	 */
	public function decendants($id)
	{
		$this->id = $id;

		if($this->depth == 0)
		{
			$depth = 99; // maximum depth
		}
		else
		{
			$depth = $this->depth;
		}

		$this->query = '
		WITH    RECURSIVE
		q AS
		(
			SELECT  *, ARRAY[id] AS level
			FROM    '.$this->table.' hc
			WHERE   id = ?
			UNION ALL
			SELECT  hc.*, q.level || hc.id
			FROM    q
			JOIN    '.$this->table.' hc
			ON      hc.parent_id = q.id
			WHERE   array_upper(level, 1) < ?
		)
		';

		$this->cacheName = 'decendants.'.$id;

		
		if($this->root == false)
		{
			$this->where[] = ' NOT id = ?';
			$this->parameters = array($id,$depth,$id);
		}
		else
		{
			$this->parameters = array($id,$depth);
		}

		return $this;
	}

	/**
	 * Ancestors
	 * @param  int $id    Current position ID
	 * @return object     collection
	 */
	public function ancestors($id)
	{
		$this->id = $id;
		$this->query = '
			WITH RECURSIVE
				q AS
				(
				SELECT	h.*, 1 AS level
				FROM	'.$this->table.' h
				WHERE	id = ?
				UNION ALL
				SELECT	hc.*, level + 1
				FROM	q
				JOIN	'.$this->table.' hc
				ON		hc.id = q.parent_id
				)';
		$this->parameters = array($id);
		$this->cacheName = 'ancestors.'.$id;
		return $this;
	}

	/**
	 * Same as Ancestors
	 * @param  int $id    Current position ID
	 * @return object     Collection
	 */
	public function breadcrumb($id)
	{
		$this->ancestors($id);
		return $this->get();
	}

	public function decendantsWithRoot($id)
	{
		$this->root = true;
		$this->decendants($id);
		return $this;
	}

	/**
	 * Gets the complete Tree
	 * @param  int    $id ID as root
	 * @return object     Collection
	 */
	public function tree($id)
	{
		$this->depth = 0;
		$this->decendants($id);
		return $this->get();
	}

	public function treeWithoutRoot($id)
	{
		$this->root = false;
		$this->depth = 0;
		$this->decendantsWithRoot($id);
		return $this->get();
	}

	/**
	 * Get function almost the same as Eloquent
	 * @param  array  $attributes Array for selecting partials (id, name, slug)
	 * @return object             Returns a collection
	 */
	public function get($attributes = array('*'))
	{
		$this->compileQuery($attributes);
		return $this->collection;
	}

	/**
	 * Build the query and compile
	 * @param  mixed $attributes  Array for selecting partials
	 * @return object             Collection
	 */
	public function compileQuery($attributes)
	{
		$query = $this->query;
		
		$id = $this->id;

		$attributes = implode(',',$attributes);

		$query .= ' SELECT '.$attributes.' FROM q ';

		$query .= (count($this->where)) ? ' WHERE ' : '';

		$query .= implode(' AND ', $this->where);

		$query .= $this->order;

		$this->collection = DB::select($query,$this->parameters);
	}

	/**
	 * Set the order
	 * @param string $order Order by
	 */
	public function setOrder($order)
	{
		$this->order = $order;
		$this->cacheName = $this->cacheName.'_orderby_'.$order;
	}

	/**
	 * Order by element
	 * @param  string $name  Order on the name
	 * @param  string $order ASC or DESC by default DESC
	 * @return object        returns the object
	 */
	public function orderBy($name, $order = 'DESC')
	{
		$orderBy = ' ORDER BY '. $name. ' ' .$order. ' ';
		$this->setOrder($orderBy);
		return $this;
	}

	/**
	 * Depth of the children
	 * @param  int    $depth Depth of the children
	 * @return object        returns the object
	 */
	public function depth($depth)
	{
		$this->setDepth($depth);
		return $this;
	}

	/**
	 * Setting the depth
	 * @param int $depth Setting the depth
	 */
	public function setDepth($depth)
	{
		$this->depth = $depth;
		$this->cacheName = $this->cacheName.'_depth_'.$depth;
	}

	/**
	 * Cache function
	 * @param  integer $minutes    Time in minutes
	 * @param  array   $attributes Attributes (id, name, slug)
	 * @return object              Returns the object
	 */
	public function remember($minutes = 10, $attributes = array('*'))
	{
		$data = $this;
		
		$cached = Cache::remember(md5($this->table.'_recurcive_'.$this->cacheName), $minutes, function() use ($data, $attributes)
		{
			// do something here!!!
			$query = $data->query;
		
			$id = $data->id;

			$attributes = implode(',',$attributes);

			$query .= ' SELECT '.$attributes.' FROM q ';

			$query .= $data->query_extended;

			$query .= $data->order;

			return DB::select($query,$data->parameters);
		});

		return $cached;
	}

	/**
	 * Where like eloquent
	 * @param  string $name [description]
	 * @param  mixed  $item [description]
	 * @return object       Returns the object
	 */
	public function where($name, $item)
	{
		$query = " ". $name . " = '" . $item . "' ";
		$this->where[] = $query;
		return $this;
	}

	public function hasChildren($id)
	{

	}
}