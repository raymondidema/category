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

	protected $id;

	protected $order = ' ORDER BY level';

	protected $parameters = array();

	protected $cacheName;

	protected $collection;

	public function table($table)
	{
		$this->table = $table;
		return $this;
	}

	public function children($id)
	{
		$this->decendants($id);
		return $this;
	}

	public function childrenWithRoot($id)
	{
		$this->root = true;
		$this->decendants($id);
		return $this;
	}

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
			$this->query_extended .= ' WHERE NOT id = ?';
			$this->parameters = array($id,$depth,$id);
		}
		else
		{
			$this->parameters = array($id,$depth);
		}

		return $this;
	}

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

	public function get($attributes = array('*'))
	{
		$this->compileQuery($attributes);
		return $this->collection;
	}

	public function compileQuery($attributes)
	{
		$query = $this->query;
		
		$id = $this->id;

		$attributes = implode(',',$attributes);

		$query .= ' SELECT '.$attributes.' FROM q ';

		$query .= $this->query_extended;

		$query .= $this->order;

		$this->collection = DB::select($query,$this->parameters);
	}

	public function setOrder($order)
	{
		$this->order = $order;
	}

	public function orderBy($name, $order = 'DESC')
	{
		$orderBy = ' ORDER BY '. $name. ' ' .$order. ' ';
		$this->setOrder($orderBy);
		return $this;
	}

	public function depth($depth)
	{
		$this->setDepth($depth);
		return $this;
	}

	public function setDepth($depth)
	{
		$this->depth = $depth;
	}

	public function remember($minutes = 10, $attributes = array('*'))
	{
		$data = $this;
		
		$cached = Cache::remember($this->table.'_recurcive_'.$this->cacheName, $minutes, function() use ($data, $attributes)
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
}