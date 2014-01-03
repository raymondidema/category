<?php namespace Raymondidema\Category\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;
use Illuminate\Database\Eloquent\Collection;
use \Illuminate\Support\Facades\DB;
use \Illuminate\Database\Eloquent\Builder;

class Category extends Eloquent
{
	protected $fillable = ['id', 'parent_id', 'name', 'slug'];

	protected $table = 'categories';

	public function children($id, $depth = 1, $attributes = ['*'])
	{
		$union = implode(',hc.',$attributes);
		$select = implode(',',$attributes);
		$depth = $depth+1;

		$query = DB::select('
			WITH    RECURSIVE
			q AS
			(
			SELECT  '.$select.', ARRAY[id] AS level
			FROM    '.$this->table.' hc
			WHERE   id = ?
			UNION ALL
			SELECT  hc.'.$union.', q.level || hc.id
			FROM    q
			JOIN    '.$this->table.' hc
			ON      hc.parent_id = q.id
			WHERE   array_upper(level, 1) < ?
			)
			SELECT  '.$select.'
			FROM    q
			WHERE NOT id = ?
			ORDER BY
			position DESC, level',[$id,$depth,$id]);
		return new Collection($query);
	}

	public function decendants($id, $depth = 1, $attributes = ['*'])
	{
		return $this->children($id, $depth = 1, $attributes = ['*']);
	}

	public function ancestors($id, $attributes = ['*'])
	{
		$union = implode(',hc.', $attributes);
		$select = implode(',', $attributes);
		$h = implode(',h.', $attributes);
		$query = DB::select('
			WITH RECURSIVE
				q AS
				(
				SELECT	h.'.$h.', 1 AS level
				FROM	'.$this->table.' h
				WHERE	id = ?
				UNION ALL
				SELECT	hc.'.$union.', level + 1
				FROM	q
				JOIN	'.$this->table.' hc
				ON		hc.id = q.parent_id
				)
			SELECT		'.$select.'
			FROM		q
			ORDER BY
				level	DESC',[$id]);
		return new Collection($query);
	}

	public function products()
	{
		return $this->belongsToMany('Product');
	}
}