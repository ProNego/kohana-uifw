<?php defined('SYSPATH') OR die('No direct script access.');

/**
 * The media class is used in order to define stylesheets and scripts which will get loaded in the main view.
 * They can added until the controller function after() gets executed.
 * It is also possible, to add them in your content views!
 * @author KennuX
 *
 */
class Kohana_Media
{
	/**
	 * Contains all currently registered stylesheets.
	 * Array of strings (URLs).
	 * @var array
	 */
	protected static $_styles = array();
	
	/**
	 * Contains all currently registered javascripts.
	 * Array of strings (URLs).
	 * @var array
	 */
	protected static $_scripts = array();
	
	/**
	 * Adds the given url to the styles collection.
	 * The url may be relative to the DOCROOT or an absolute http://... url.
	 * @param string $url
	 */
	public static function add_style($url)
	{
		if (!in_array($url, self::$_styles))
			array_push(self::$_styles, $url);
	}
	
	/**
	 * Adds the given url to the styles collection.
	 * The url may be relative to the DOCROOT or an absolute http://... url.
	 * @param string $url
	 */
	public static function add_script($url)
	{
		if (!in_array($url, self::$_scripts))
			array_push(self::$_scripts, $url);
	}
	
	/**
	 * Builds the html-code for all registered scripts and styles.
	 * This can get used to ouput them in the header of the main layout view.
	 */
	public static function get_html_styles_and_scripts()
	{
		$output = '';
		
		// Build all styles
		foreach (self::$_styles AS $style)
		{
			// Check if the style is remote
			$is_remote = (strpos($style, "http://") !== FALSE);

			// Check if not remote and locally our file exists
			if (!$is_remote && !file_exists(DOCROOT.$style))
				throw new Exception('Style not found: '.DOCROOT.$style);

			$output .= '<link rel="stylesheet" type="text/css" href="'.($is_remote ? '' : URL::base()).$style.'">'."\r\n";
		}
		
		// Build all scripts
		foreach (self::$_scripts AS $script)
		{
			// Check if the style is remote
			$is_remote = (strpos($script, "http://") !== FALSE);

			// Check if not remote and locally our file exists
			if (!$is_remote && !file_exists(DOCROOT.$style))
				throw new Exception('Style not found: '.DOCROOT.$style);

			$output .= '<script type="text/javascript" src="'.($is_remote ? '' : URL::base()).$script.'"></script>'."\r\n";
		}
		
		return $output;
	}
}