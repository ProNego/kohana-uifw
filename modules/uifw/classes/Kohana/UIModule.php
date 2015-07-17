<?php defined('SYSPATH') or die('No direct script access.');

/**
 * The ui module base class.
 * This class holds general functions used for all ui modules.
 * It can get used for example to implement server-side classes to handle jquery ui scripts.
 */
abstract class Kohana_UIModule
{
    /**
     * All additional options for the media module.
     * The key is the option name and the value is the option value.
     * Javascript-objects should get passed in as string, ex.: "{"option": 123}".
     * @var array
     */
    protected $_options = array();

    /**
     * Overwrite this property in your own implementation.
     * All scripts should be set in here, relative to the docpath.
     * Example: array('media/datatables/js/jquery.dataTables.min.js');
     * @var array
     */
    protected $_scripts = array();

    /**
     * See $_scripts for more details.
     * @var array
     */
    protected $_styles = array();

    /**
     * The html identity used for rendering.
     * If this is left null, no html id will get added to the DOM.
     * @var null or string
     */
    protected $_html_id = NULL;

    /**
     * Sets the html id used for rendering this media module.
     * @param $id
     */
    public function set_html_id($id)
    {
        $this->_html_id = $id;
    }

    /**
     * Renders this media module.
     * @return string The evaluated module view (HTML-DOM) code.
     */
    public final function render()
    {
        // Add scripts and styles
        foreach ($this->_scripts AS $sc)
            Media::add_script($sc);
        foreach ($this->_styles AS $st)
            Media::add_style($st);

        return $this->_render(array(
            "id" => $this->_html_id,
            "options" => $this->_options
        ));
    }

    /**
     * Called from render(). Execute your rendering code in here.
     * @param $view_data Pre-defined view data variables. Available keys:
     * array
     * (
     *      "id" => $this->_html_id,
     *      "options" => $this->_options,
     * );
     * @return string The evaluated module view (HTML-DOM) code.
     */
    protected abstract function _render($view_data);

    /**
     * Adds an option entry for the given name and value.
     * This will __NOT__ handle enquoting strings automatically!
     * @param $name
     * @param $val
     */
    public function add_option($name, $val, $enquote = FALSE)
    {
        if ($enquote && is_string($val))
            $val = '"'.$val.'"';
        $this->_options[$name] = $val;
    }
}