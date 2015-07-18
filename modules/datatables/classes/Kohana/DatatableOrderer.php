<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Datatable order algorithm class.
 * This class is used in datatables internally for ajax ordering.
 * 
 * @author KennuX
 *
 */
class Kohana_DatatableOrderer
{
	/**
	 * Maps to the $_GET['order'] array from the ajax request.
	 * @var array
	 */
	protected $_order = array();
	
	/**
	 * count($this->_order) > 0
	 * @var unknown
	 */
	protected $_has_order = FALSE;
	
	/**
	 * array_keys($columns) in order().
	 * @var unknown
	 */
	protected $_columns = array();
	
	/**
	 * The order array is mapping to $_GET['order'] array.
	 * 
	 * @param array $order
	 * @param array $order_disabled Order disabled on fields (index).
	 */
	public function __construct($order, $order_disabled = array())
	{
    	// Validate order array
    	$this->_order = array();
    	foreach ($order AS $o)
    	{
    		// Order validation
    		if ( ! isset($o['column']) || ! isset($o['dir']) || in_array($o['column'], $order_disabled))
    			continue;
    		
    		// Points multiplier for _order
    		$o['dir'] = ($o['dir'] == 'asc') ? 1 : -1;
    		$o['column'] = intval($o['column']);
    		$this->_order[] = $o;
    	}
    	
    	$this->_has_order = count($this->_order) > 0;
	}
	
	/**
	 * Performs ordering.
	 * 
	 * Header columns:
	 * The column of the datatable.
	 * For more information see Datatable::__construct() (The $columns parameter).
	 * 
	 * The value is in here not relevant, only the key.
	 * 
	 * @param array $entries
     * @param $columns The header columns.
	 * @return array The ordered entries.
	 */
	public function order(array $entries, array $columns)
	{
		$this->_columns = array_keys($columns);
		
		// Order entries
		if ($this->_has_order)
		{
			usort($entries, array($this, '_order'));
		}
		
		return $entries;
	}
	
	/**
	 * usort() order callback.
	 * @param array $a
	 * @param array $b
	 */
	protected function _order($a, $b)
	{
		$a_points = 0;
		$b_points = 0;
		
		foreach ($this->_order AS $order)
		{
			// Compare
			$col = $this->_columns[$order['column']];
			$cmp = strcmp ($a[$col], $b[$col]);

			// Transform cmp by direction
			$cmp *= $order['dir'];
			
			// Add points
			if ($cmp < 0)
				$b_points++;
			else if ($cmp > 0)
				$a_points++;
		}
		
		return $a_points < $b_points ? -1 : 1;
	}
}