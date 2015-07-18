<?php defined('SYSPATH') or die('No direct script access.');

/**
 * Datatables kohana module implementation.
 */
class Kohana_Datatable extends UIModule
{
    protected $_scripts = array('media/datatables/js/jquery.dataTables.min.js');
    protected $_styles = array('media/datatables/css/jquery.dataTables.css');

    /**
     * The header columns as strings front-to-back. Ex.: array("id", "name", "price");
     * @var array
     */
    protected $_columns = array();

    /**
     * All entries of this datatable object.
     * A single entry must be an array with data organized as specified in the columns array.
     * Ex.: columns = array("id", "name", "price"), entry = array(1, "Entry with id 1", 10);
     * @var array
     */
    protected $_entries = array();

    /**
     * The available extensions data-array.
     * This contains all available data tables extensions in the following format:
     * array(
     *      'autofill' => array(
     *          'js' => array('media/datatables/extensions/AutoFill/js/dataTables.autoFill.min.js', '...'),
     *          'css' => array('media/datatables/extensions/AutoFill/css/dataTables.autoFill.min.css', '...'),
     *      )
     * );
     * @var array
     */
    protected $_available_extensions = array(
        // TODO
    );

    /**
     * Contains keys to available_extensions, for example array("autofill") would activate the auto fill module.
     * @var array
     */
    protected $_active_extensions = array();

    /**
     * Contains all disabled features.
     * https://www.datatables.net/examples/basic_init/filter_only.html
     *
     * For example if this is array("paging"), in the datatables function call options "paging": false will get used to disable it.
     * @var array
     */
    protected $_disabled_features = array();
    
    /**
     * Gets set to a string if the datatable is serverside processed.
     * In this case the ajax_render() function should get rendered after filling the datatable with data.
     * The main html rendering however is done with render().
     * @var unknown
     */
    protected $_serverside = FALSE;
    
    /**
     * The page length
     * @var int
     */
    protected $_page_length = 10;
    
    /**
     * The cache instance used for caching. May be null for no caching.
     * @var Cache
     */
    protected $_cache = NULL;
    
    /**
     * Used as additional prefix after datatables_. Ex. datatables_articletable_*. Used to prevent collision with other cached tables. Must be set if cache is not false.
     * @var string
     */
    protected $_cache_data_name = NULL;
    
    /**
     * Holds the ids of columns which are not allowed to get ordered.
     * @var array
     */
    protected $_order_disabled = array();
    
    /**
     * Holds the ids of columns which are not allowed to filtered ordered.
     * This can for example get used for orm-models if you got "imaginary" properties which are not in really existing in the database.
     * You can permit filtering on them to prevent a mysql error
     * @var array
     */
    protected $_filtering_disabled = array();
    
    /**
     * Loaded in constructor.
     * @var Config
     */
    protected $_config = NULL;

    /**
     * Initializes the datatable.
     * Caching will get used to cache datatable results. It will use a datatables_ prefix for cache entries.
     * Caching will __ONLY__ get used for ajax rendering (for filtering and ordering).
     * 
     * @param $columns The header columns as strings front-to-back with their array-key or ORM-property as key. Ex.: array("id" => "id", "name" => "name", "price" => "price");
     * @param bool $cache Standard is false (No caching). Otherwise the name of the caching group OR a Cache instance.
     * @param string $cache_data_name Used as additional prefix after datatables_. Ex. datatables_articletable_* for name="articletable". Used to prevent collision with other cached tables. Must be set if cache is not false.
     */
    public function __construct($columns, $page_length = 10, $cache = FALSE, $cache_data_name = '')
    {
        $this->_columns = $columns;
        $this->_page_length = $page_length;
        $this->_config = Kohana::$config->load('datatables');
        
        if ($cache instanceof Cache)
        	$this->_cache = $cache;
        else if (is_string($cache))
        	$this->_cache = Cache::instance($cache);
        
        if ($this->_cache != NULL && empty($cache_data_name))
        	throw new Exception('Cant use datatables caching without cache data name!');
    }
    
    /**
     * Sets this datatable to serverside table.
     * The url must be absolute or relative to the domain path.
     */
    public function set_serverside($ajax_url)
    {
    	$this->_serverside = $ajax_url;
    }

    /**
     * Adds the given entry to the data table data.
     * The entry must be an array with data organized as specified in the columns array.
     * Ex.: columns = array("id", "name", "price"), entry = array(1, "Entry with id 1", 10);
     * @param array $entry
     */
    public function add_entry($entry)
    {
        if (count($entry) != count($this->_columns))
            throw new Exception('Datatables entry must have the same ammount of indices as the columns array.', 500);

        $this->_entries[] = $entry;
    }

    /**
     * Same as add_entry() but batched.
     * Or, if you pass in an orm-model as entries, data will get read directly from the database.
     * Using ORM will cause the filter and order system to use the database for that.
     * @param ORM or array $entries Multiple entries in an array, ex.: array(array(1, "Entry with id 1", 10), ...); or ORM-Model
     */
    public function add_entries($entries)
    {
    	if ($entries instanceof ORM)
    		$this->_entries = $entries;
    	else
        	$this->_entries = array_merge($this->_entries, $entries);
    }

    /**
     * Activates the extension with the given name.
     * For a list of available extensions, see Datatable->$_available_extension.
     * @param $extension The key on an available extension entry.
     */
    public function activate_extension($extension)
    {
        $this->_active_extensions[] = $extension;
    }
    
    /**
     * Disables filtering on the specified column id.
     * The id is based on the index of the column set in the constructor.
     * @param int$column_id
     */
    public function disable_filterability($column_id)
    {
    	$this->_filtering_disabled[] = $column_id;
    }

    /**
     * Disables order on the specified column id.
     * The id is based on the index of the column set in the constructor.
     * @param int$column_id
     */
    public function disable_orderability($column_id)
    {
    	$column_defs = Arr::get($this->_options, 'columnDefs', array());
    	 
    	// Create column definition
    	$column_def = new stdClass();
    	$column_def->orderable = false;
    	$column_def->targets = $column_id;
    
    	$column_defs[] = $column_def;
    	 
    	$this->_options['columnDefs'] = $column_defs;
    	$this->_order_disabled[] = $column_id;
    }
    
    /**
     * Filters and orders this datatable's entries.
     * The parameters are mapping to the ajax get parameters of datatables.
     * They are documented here: https://datatables.net/manual/server-side.
     * 
     * If entries is a ORM-object, this will not execute the query but build it.
     */
    public function filter_and_order($columns, $search, $order)
    {
    	// Construct cache key
    	$hash = hash('sha512', serialize(array('search' => $search, 'columns' => $columns, 'order' => $order)));
	    $cache_string = 'datatables_'.$this->_cache_data_name.'_'.$hash;
	    $is_orm = ($this->_entries instanceof ORM);
	    
    	$read_from_cache = FALSE;
    	
	    // Try reading from cache
    	if (!$is_orm && $this->_cache != NULL)
    	{
    		// Check cache
    		$cached_entries = $this->_cache->get($cache_string, NULL);
    	
    		if ($cached_entries != NULL)
    		{
    			$this->_entries = $cached_entries;
    			return;
    		}
    	}
	    	 
    	
    	// Array-filtering
    	if (is_array($this->_entries))
    	{
	    	// Perform search
	    	$search = new DatatableFilter($columns, $search, $this->_filtering_disabled);
	    	$orderer = new DatatableOrderer($order, $this->_order_disabled);
	    	if (!$read_from_cache)
	    	{
	    		// Filter
	    		$this->_entries = $search->filter($this->_entries);
	    		
	    		// Order
	    		$this->_entries = $orderer->order($this->_entries, $this->_columns);
	    		
		    	// Set cache
		    	if ($this->_cache != NULL)
		    		$this->_cache->set($cache_string, $this->_prepare_entries($this->_entries), $this->_config->get('cache_time', 300));
	    	}
    	}
    	// ORM-filtering
    	else if ($is_orm)
    	{
    		// TODO: Regex?
    		$column_keys = array_keys($this->_columns);
    		
    		// Analyze order array
    		foreach ($order AS $o)
    		{
    			// Order validation
    			if ( ! isset($o['column']) || ! isset($o['dir']) || in_array($o['column'], $this->_order_disabled))
    				continue;
    		
    			// SQL injection check
    			$o['dir'] = (strtolower($o['dir']) == "asc" || strtolower($o['dir']) == "desc") ? $o['dir'] : 'desc';
    			
    			// Get column name
    			$db_col = $column_keys[intval($o['column'])];
    			$this->_entries->order_by($db_col, $o['dir']);
    		}
    		
    		// Analyze filters
    		if(isset($search['value']) && !empty($search['value']))
    		{
    			$this->_entries->or_where_open();
    			$i = 0;
    			// Set like for all columns
    			foreach ($this->_columns AS $db_col => $label)
    			{
    				$i++;
    				
    				// Check if filtering is allowed
    				if (in_array($i-1, $this->_filtering_disabled))
    					continue;
    				
    				$this->_entries->or_where($db_col, 'LIKE', '%'.$search['value'].'%');
    			}
    			$this->_entries->or_where_close();
    		}
    		 
    		// Construct column filter
    		foreach ($columns AS $key => $col)
    		{
    			if (! in_array($key, $this->_filtering_disabled) && isset($col['search']) &&
    					isset($col['search']['value']) && !empty($col['search']['value']))
    			{
    				$db_col = $column_keys[intval($key)];
    				$this->_entries->where($db_col, 'LIKE', '%'.$col['search']['value'].'%');
    			}
    		}
    	}
	    	
    }
    
    /**
     * Called just after the entries have been filtered and ordered and are getting rendered.
     * This function will "convert" orm models to arrays.
     * Returns the prepared entries array.
     * 
     * @param bool $no_keys Retruns the array with no key, ex. input. array(array("test1" => "test1 text")) - output array(array("test1 text")).
     */
    private function _prepare_entries($entries, $no_keys = FALSE)
    {
    	$return = array();
    	
    	$is_orm = isset($entries[0]) && ($entries[0] instanceof ORM);
    	
    	foreach ($entries AS $key => $val)
    	{
    		if ($is_orm)
    		{
    			$obj = $val->object();
    			
	    		// Create new array
	    		$return[$key] = array();
	    		 
	    		// Evaluate model
	    		foreach ($this->_columns AS $col_key => $col)
	    		{
	    			if ($no_keys)
	    			{
	    				$return[$key][] = $obj[$col_key];
	    			}
	    			else
	    			{
	    				if (isset($obj[$col_key]))
	    					$return[$key][$col_key] = $obj[$col_key];
	    				else
	    					$return[$key][$col_key] = $val->get($col_key);
	    			}
	    		}
    		}
    		// No keys for array?
    		else if (is_array($val) && $no_keys)
    		{
    			$return[$key] = array_values($val);
    		}
    	}
    	
    	return $return;
    }
    
    /**
     * Renders the datatable view for this datatable object and return's it's html content.
     */
    protected function _render($view_data)
    {
        // Add extensions
        foreach ($this->_active_extensions AS $ext)
        {
            // Check if the extension REALLY exists.
            if (isset($this->_available_extensions[$ext]))
            {
                // If it exists, grab it!
                $extension = $this->_available_extensions[$ext];

                // Add extension media
                foreach ($extension['js'] AS $js)
                    Media::add_script($js);
                foreach ($extension['css'] AS $css)
                    Media::add_style($css);
            }
        }
        
        
        // Prepare view for rendering
        $view = View::factory('datatable', $view_data);
        
        // Check if serverside is enabled
        if ($this->_serverside != FALSE)
        {
        	$view->set('serverside', true);
        	$view->set('ajax_url', $this->_serverside);
        }
        else
        {
	    	// find all by orm if we are on an orm object
	    	if ($this->_entries instanceof ORM)
	    		$this->_entries = $this->_prepare_entries($this->_entries->find_all()->as_array());
        }
        
        $view->bind('columns', $this->_columns);
        $view->bind('entries', $this->_entries);
        $view->bind('page_length', $this->_page_length);
        $view->bind('disabled_features', $this->_disabled_features);

        // Return the view html content
        return $view->render();
    }
    
    /**
     * Ajax serverside processing function.
     * In your ajax action, just fill this datatable instance with data like you did when rendering.
     * Then call ajax_render. It will do ANYTHING on it's own. It handles the get-parameters and returns the json-STRING (not object) to send to the client.
     */
    public function ajax_render()
    {
    	// Manual to Datatables SSP: https://datatables.net/manual/server-side
    	
    	// Get total
    	$is_orm = ($this->_entries instanceof ORM);
    	if ($is_orm)
    		$total_all = $this->_entries->count_all();
    	else
    		$total_all = count($this->_entries);
    	
    	// Get parameters
    	$draw = Arr::get($_GET, 'draw', 0);
    	$start = Arr::get($_GET, 'start', 0);
    	$length = Arr::get($_GET, 'length', 10);
    	
    	// Get filter and sort values
    	$columns = Arr::get($_GET, 'columns', array());
    	$order = Arr::get($_GET, 'order', array());
    	$search = Arr::get($_GET, 'search', array('value' => '', 'regex' => false));
    	
    	// Filter
    	$this->filter_and_order($columns, $search, $order);
    	
    	// Calculate offsets
    	if ($is_orm)
    	{
    		// ORM needs special counting
    		$orm_result = $this->_entries->find_all();
	    	$total = $orm_result->count();
    	}
    	// Array counting
    	else 
    		$total = count($this->_entries);
    	
    	// Calculate the ammount of items we are sending
	    $sent_count = $total - $start - $length;
    	if ($sent_count < 0)
    		$sent_count = $length + $sent_count;
    	else
    		$sent_count = $length;

    	// Slice out data from the data array
    	if ($is_orm)
    	{
    		// If we are on an orm object
    		$data_array = array();
    		
    		for ($i = $start; $i < ($start + $sent_count); $i++)
    		{
    			$data_array[] = $orm_result[$i];
    		}
    		
    		$data_array = $this->_prepare_entries($data_array, TRUE);
    	}
    	else
    		$data_array = $this->_prepare_entries(array_slice($this->_entries, $start, $sent_count), TRUE);
    	
    	// Build json data and return
    	$data = array('draw' => $draw, 'recordsTotal' => $total_all, 'recordsFiltered' => $total, 'data' => $data_array);
    	return json_encode($data);
    }
}