<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Select2 kohana module.
 * Select2: https://select2.github.io/
 */
class Kohana_Select2 extends UIModule
{
    protected $_scripts = array('media/select2/js/select2.full.min.js');
    protected $_styles = array('media/select2/css/select2.min.css');

    /**
     * Array with id => value for the option, value => Name of the entry.
     * @var array
     */
    protected $_data = array();

    /**
     * The name this select2-selectbox will get.
     * @var null
     */
    protected $_name = NULL;

    /**
     * True if the select is a multiselect.
     * Standard = false.
     * @var bool
     */
    protected $_multiple = FALSE;

    /**
     * @param $data Array with id => value for the option, value => Name of the entry.
     */
    public function __construct($data, $name)
    {
        $this->_data = $data;
        $this->_name = $name;
    }

    /**
     * @param bool $multiple
     */
    public function set_multiple(bool $multiple)
    {
        $this->_multiple = $multiple;
    }

    protected function _render($view_data)
    {
        // Init the select 2 view.
        $view = View::factory('select2', $view_data);
        $view->bind('data', $this->_data);
        $view->bind('multiple', $this->_multiple);
        return $view->render();
    }
}