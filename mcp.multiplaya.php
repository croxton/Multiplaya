<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Extends the Playa module to allow retrieval of parent/child relationships for multiple entry ids
 *
 * @package             Multiplaya
 * @author              Mark Croxton (mcroxton@hallmark-design.co.uk)
 * @copyright			Copyright (c) 2011 Hallmark Design
 * @link                http://hallmark-design.co.uk
 */

// --------------------------------------------------------------------

class multiplaya_mcp {

	/**
	 * Constructor
	 */
	function __construct()
	{
		// Make a local reference to the ExpressionEngine super object
		$this->EE =& get_instance();
	}
}
// END CLASS

/* End of file mcp.multiplaya.php */
/* Location: ./system/expressionengine/third_party/multiplaya/mcp.multiplaya.php */