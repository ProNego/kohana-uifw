<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Datatable search algorithm class.
 * This class is used in datatables internally for ajax filtering.
 * 
 * @author KennuX
 *
 */
class Kohana_DatatableFilter
{
	/**
	 * The filters array constructed from the columns array in the constructor.
	 * It's index is the entry-array-index and the value is an array with value (string) and regex (is_regex bool flag).
	 * @var array
	 */
	protected $_filters = array();
	
	/**
	 * count($this->_filters) > 0
	 * @var unknown
	 */
	protected $_has_filters = FALSE;
	
	/**
	 * Maps to the $_GET['search'] array from the ajax request.
	 * This is the GLOBAL search.
	 * @var array
	 */
	protected $_search = array();
	
	/**
	 * !empty($search['value']);
	 * @var unknown
	 */
	protected $_has_search = FALSE;
	
	/**
	 * Contains all column ids which are not allowed to get filtered.
	 * @var array
	 */
	protected $_filtering_disabled = array();
	
	/**
	 * The columns are mapping to the ajax $_GET['columns'] array.
	 * The search array is mapping to $_GET['search'] array.
	 * 
	 * @param array $columns
	 * @param array $search
	 */
	public function __construct($columns, $search, $filtering_disabled)
	{
    	$this->_has_search = !empty($search['value']);
    	$this->_filtering_disabled = $filtering_disabled;
    	$this->_search = $search;
    	
    	// Construct column filter
    	$this->_filters = array();
    	foreach ($columns AS $key => $col)
    	{
    		if (! in_array($key, $this->_filtering_disabled) && isset($col['search']) &&
    			isset($col['search']['value']) && !empty($col['search']['value']))
    		{
    			$this->_filters[$key] = $col['search'];
    		}
    	}
    	$this->_has_filters = count($this->_filters) > 0;
	}
	
	/**
	 * Performs filtering.
	 * @param array $entries
	 * @return array The filtered entries.
	 */
	public function filter(array $entries)
	{
		// Filter entries array
		if ($this->_has_filters || $this->_has_search)
		{
			$entries = array_filter($entries, array($this, '_filter'));
		}
		
		return $entries;
	}
	
	/**
	 * The actual filtering function used for array_filter.
	 */
	protected function _filter($entry)
	{
		// TODO: Regular expressions!
		$return = FALSE;
			
		// Check if search global is active
		// If so, perform it.
		if ($this->_has_search)
		{
			
			foreach ($entry AS $v)
			{
				$return = $return || (strpos($v, $this->_search['value']) !== FALSE);
				 
				if ($return)
					break;
			}
		}
		else if ($this->_has_filters)
		{
			// Check all filters
			foreach ($this->_filters AS $key => $filter)
			{
				$return = $return || strpos($entry[$key], $filter);
				 
				if ($return)
					break;
			}
		}
		
		return $return;
	}
}