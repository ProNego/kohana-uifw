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
     * Sets the columns for this datatable
     * @param $columns The header columns as strings front-to-back. Ex.: array("id", "name", "price");
     */
    public function __construct($columns, $page_length = 10)
    {
        $this->_columns = $columns;
        $this->_page_length = $page_length;
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
     * @param $entries Multiple entries in an array, ex.: array(array(1, "Entry with id 1", 10), ...);
     */
    public function add_entries($entries)
    {
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
    	// TODO:
    	// Server-side filtering
    	// Optional get parameter handling
    	
    	// Get parameters
    	$draw = Arr::get($_GET, 'draw', 0);
    	$start = Arr::get($_GET, 'start', 0);
    	$length = Arr::get($_GET, 'length', 10);
    	
    	// Calculate offsets
    	$total = count($this->_entries);
    	$sent_count = $total - $start - $length;
    	if ($sent_count < 0)
    		$sent_count = $length + $sent_count;
    	else
    		$sent_count = $length;
    	
    	// Slice out data from the data array
    	$data_array = array_slice($this->_entries, $start, $sent_count);
    	$data = array('draw' => $draw, 'recordsTotal' => $total, 'recordsFiltered' => $total, 'data' => $data_array);
    	
    	return json_encode($data);
    }
}