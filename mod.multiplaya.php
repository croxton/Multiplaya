<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Extends the Playa module to allow retrieval of parent/child relationships for multiple entry ids
 *
 * @package             Multiplaya
 * @author              Mark Croxton (mcroxton@hallmark-design.co.uk)
 * @copyright			Copyright (c) 2011 Hallmark Design
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
		
		// assemble the requested entry ids
		$entry_ids = array();
		
		if (strpos($this->entry_id, '|') !== FALSE || strpos($this->entry_id, '&') !== FALSE)
		{
			$entry_ids = preg_split('/[\&\|]/', $this->entry_id, -1, PREG_SPLIT_NO_EMPTY);
			$entry_ids = array_map('trim', array_map(array($this->EE->db, 'escape_str'), $entry_ids));
		}
		
		// create a unique cache key
		$cache_key_id = count($entry_ids) > 0 ? implode(',', $entry_ids) : $this->entry_id;

		$cache_key = $cache_key_id . '|'
		           . $dir . '|'
		           . ($field_ids ? implode(',', $field_ids) : '*') . '|'
		           . ($col_ids   ? implode(',', $col_ids)   : '*') . '|'
		           . ($row_ids   ? implode(',', $row_ids)   : '*');

		// find the rels if they aren't already cached
		if (! isset($this->cache['rels'][$cache_key]))
		{	
			if ($dir == 'children')
			{
				$this->helper->db_where('parent_entry_id', (count($entry_ids) > 0 ? $entry_ids : $this->entry_id) );
				
				// AND type filtering 
				// => entries must have relationships with ALL specified parent/child entry ids
				if (strpos($this->entry_id, '&') !== FALSE)
				{	
					$this->EE->db->having('COUNT(parent_entry_id)', count($entry_ids)); 
					$this->EE->db->group_by("child_entry_id"); 
				}

				// order
				$this->EE->db->order_by('rel_order');
			}
			else
			{
				$this->helper->db_where('child_entry_id', (count($entry_ids) > 0 ? $entry_ids : $this->entry_id) );
				
				// AND type filtering 
				// => entries must have relationships with ALL specified parent/child entry ids
				if (strpos($this->entry_id, '&') !== FALSE)
				{	
					$this->EE->db->having('COUNT(child_entry_id)', count($entry_ids)); 
					$this->EE->db->group_by("parent_entry_id"); 
				}
			}
			
			// filter by field?
			if ($field_ids)
			{
				$this->helper->db_where('parent_field_id', $field_ids);
			}

			// filter by column?
			if ($col_ids)
			{
				$this->helper->db_where('parent_col_id', $col_ids);
			}

			// filter by row?
			if ($row_ids)
			{
				$this->helper->db_where('parent_row_id', $row_ids);
			}

			// get the relationships
			$rels = $this->EE->db->get('playa_relationships');

			// cache them
			$this->cache['rels'][$cache_key] = $rels;
		}

		return $this->cache['rels'][$cache_key];
	}	
}

/* End of file mod.multiplaya.php */
/* Location: ./system/expressionengine/third_party/multiplaya/mod.multiplaya.php */