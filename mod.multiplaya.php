<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Extends the Playa module to allow retrieval of parent/child relationships for multiple entry ids
 *
 * @package             Multiplaya
 * @author              Mark Croxton (mcroxton@hallmark-design.co.uk)
 * @copyright			Copyright (c) 2012 Hallmark Design
 * @link                http://hallmark-design.co.uk
 */

/* Sorry about this nasty hack - we need to rewrite Playa's private methods to protected */
/* We wouldn't need this if Brandon made the methods protected in the first place... */
if ( ! class_exists('Playa2'))
{
	$playa2 = file_get_contents(PATH_THIRD.'playa/mod.playa'.EXT);
	$playa2 = str_replace('private function', 'protected function', $playa2);
	$playa2 = str_replace('class Playa', 'class Playa2', $playa2);
	$playa2 = str_replace('<?php', '', $playa2);
	eval($playa2); // can you hear the kittens screaming?
}

class Multiplaya extends Playa2
{		
	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct();	
	}
	
	/**
	 * Fetch relationships: overrides parent method
	 *
	 * @access	protected
	 * @param	string
	 * @return	array
	 */
protected function _fetch_rels($dir)
	{		
		// field, col, and row params
		$field_ids = $this->_fetch_field_ids();
		$col_ids   = $this->_fetch_col_ids();
		$row_ids   = $this->_fetch_row_ids();
		$sql_end = '';
		
		// get the list of entry IDs to include/exclude (if any)
		$filter_ids = array(
			'child'  => $this->EE->TMPL->fetch_param('child_id'),
			'parent' => $this->EE->TMPL->fetch_param('parent_id')
		);
		
		// assemble the requested entry ids
		$multi_entry_ids = array();
		
		if (strpos($this->entry_id, '|') !== FALSE || strpos($this->entry_id, '&') !== FALSE)
		{
			$multi_entry_ids = preg_split('/[\&\|]/', $this->entry_id, -1, PREG_SPLIT_NO_EMPTY);
			$multi_entry_ids = array_map('trim', array_map(array($this->EE->db, 'escape_str'), $multi_entry_ids));
		}
		
		// create a unique cache key
		$cache_key_id = count($multi_entry_ids) > 0 ? implode(',', $multi_entry_ids) : $this->entry_id;

		$cache_key = $cache_key_id . '|'
		           . $dir . '|'
		           . ($field_ids   ? implode(',', $field_ids) : '*') . '|'
		           . ($col_ids     ? implode(',', $col_ids)   : '*') . '|'
		           . ($row_ids     ? implode(',', $row_ids)   : '*') . '|'
		           . ($filter_ids['child']  ? str_replace('|', ',', $filter_ids['child']) : '*')
		           . ($filter_ids['parent'] ? str_replace('|', ',', $filter_ids['parent']) : '*');
			
		// find the rels if they aren't already cached
		if (! isset($this->cache['rels'][$cache_key]))
		{		
			switch($dir)
			{
				case 'children':

					$sql = 'SELECT DISTINCT(rel.child_entry_id) AS entry_id
					        FROM exp_playa_relationships rel';
					
					if (count($multi_entry_ids) > 0)
					{
						$where[] = 'rel.parent_entry_id IN ('.implode(",", $multi_entry_ids).')';
					}
					else
					{
						$where[] = 'rel.parent_entry_id = '.$this->entry_id;
					}
					
					// AND type filtering 
					// => entries must have relationships with ALL specified parent/child entry ids
					if (strpos($this->entry_id, '&') !== FALSE)
					{	
						$sql_end = 'GROUP BY rel.child_entry_id
									HAVING COUNT(rel.parent_entry_id) = '.count($multi_entry_ids).' ';
					}
					
					$sql_end .= 'ORDER BY rel.rel_order';
					
					break;

				case 'parents':

					$sql = 'SELECT DISTINCT(rel.parent_entry_id) AS entry_id
					        FROM exp_playa_relationships rel';
					
					if (count($multi_entry_ids) > 0)
					{	
						$where[] = 'rel.child_entry_id IN ('.implode(",", $multi_entry_ids).')';
					}
					else
					{
						$where[] = 'rel.child_entry_id = '.$this->entry_id;
					}
					
					// AND type filtering 
					// => entries must have relationships with ALL specified parent/child entry ids
					if (strpos($this->entry_id, '&') !== FALSE)
					{	
						$sql_end = 'GROUP BY rel.parent_entry_id
									HAVING COUNT(rel.child_entry_id) = '.count($multi_entry_ids);
					}
					
					break;

				case 'siblings';

					$sql = "SELECT DISTINCT(rel.child_entry_id) AS entry_id
					        FROM exp_playa_relationships rel
					        INNER JOIN exp_playa_relationships parent ON parent.parent_entry_id = rel.parent_entry_id";
					break;
			}
			
			// filter by field?
			if ($field_ids)
			{
				$where[] = 'rel.parent_field_id IN ('.implode(',', $field_ids).')';
			}

			// filter by column?
			if ($col_ids)
			{
				$where[] = 'rel.parent_col_id IN ('.implode(',', $col_ids).')';
			}

			// filter by row?
			if ($row_ids)
			{
				$where[] = 'rel.parent_row_id IN ('.implode(',', $row_ids).')';
			}

			// filter by entry ID?
			foreach ($filter_ids as $col => $entry_ids)
			{
				if ($entry_ids)
				{
					$entry_ids = str_replace('|', ',', $entry_ids);

					if ($not = (strncmp($entry_ids, 'not ', 4) == 0))
					{
						$entry_ids = substr($entry_ids, 4);
					}

					$where[] = "rel.{$col}_entry_id".($not ? ' NOT' : '').' IN ('.$entry_ids.')';
				}
			}

			if (isset($where))
			{
				$sql .= ' WHERE '.implode(' AND ', $where);
			}

			if (!empty($sql_end))
			{
				$sql .= ' '.$sql_end;
			}

			// get the relationships
			$rels = $this->EE->db->query($sql);

			// cache them in case an identical request comes later
			$this->cache['rels'][$cache_key] = $rels;
		}

		return $this->cache['rels'][$cache_key];
	}
}

/* End of file mod.multiplaya.php */
/* Location: ./system/expressionengine/third_party/multiplaya/mod.multiplaya.php */