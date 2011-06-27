<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Extends the Playa module to allow retrieval of parent/child relationships for multiple entry ids
 *
 * @package             Multiplaya
 * @author              Mark Croxton (mcroxton@hallmark-design.co.uk)
 * @copyright			Copyright (c) 2011 Hallmark Design
 * @link                http://hallmark-design.co.uk
 */

class Multiplaya_upd {

	public $version = '1.0.0';
	public $name 	= 'Multiplaya';
	
	/**
	 * Constructor
	 */
	function __construct()
	{
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();
	}

	// --------------------------------------------------------------------

	/**
	 * Module Installer
	 *
	 * @access	public
	 * @return	bool
	 */
	function install()
	{
		$data = array(
					'module_name' => $this->name,
					'module_version' => $this->version,
					'has_cp_backend' => 'n'
					);

		$this->EE->db->insert('modules', $data);
		
		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Module Uninstaller
	 *
	 * @access	public
	 * @return	bool
	 */
	function uninstall()
	{
		$this->EE->db->select('module_id');
		$this->EE->db->from('modules');
		$this->EE->db->where('module_name', $this->name);
		$query = $this->EE->db->get();

		$this->EE->db->delete('module_member_groups', array('module_id' => $query->row('module_id')));
		$this->EE->db->delete('modules', array('module_name' => $this->name));

		return TRUE;
	}

	// --------------------------------------------------------------------

	/**
	 * Module Updater
	 *
	 * @access	public
	 * @return	bool
	 */
	function update($current = '')
	{
		return FALSE;
	}

}
// END CLASS

/* End of file upd.multiplaya.php */
/* Location: ./system/expressionengine/third_party/multiplaya/upd.multiplaya.php */