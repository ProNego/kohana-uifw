Kohana UIFW
=======================

This is a small ui-"framework" used in multiple projects.
It contains an extendable base class for ui elements like jquery select2.

UIFW-Module
=======================

The uifw module is the core module.
It contains the base class for all ui-elements (UIModule) and the Media-Manager (Media).

The modules are assuming that jquery is ALWAYS loaded, so you can add it by calling Media::add_script('your/path/to/jquery.js');
Alternatively you can also include it as script-tag.
Just make sure to output the Media scripts and styles in your html-header like this:

```PHP
<?php echo Media::get_html_styles_and_scripts(); ?>
´´´

This will output all needed javascript and css import tags for the currently used module.
You just need to make sure you render your content views BEFORE the layout view (Because your Media::add_script()) calls should be located in the views.

Here is an example on how to use the framework correctly (This is a controller we use in one of our projects):

```PHP
class Controller extends Kohana_Controller
{
	/**
	 * The path to the main layout view.
	 * @var string
	 */
	protected $_layout = 'layout/main';
	
	/**
	 * Gets set in before() based on _layout.
	 * @var View
	 */
	protected $_layout_view = NULL;
	
	/**
	 * The content used for rendering.
	 * This will be used in after() for final rendering.
	 * It will get inserted into the layout main view as $content.
	 * @var View or String
	 */
	protected $_content = NULL;
	
	/**
	 * Creates all needed design views and prepares this controller for executing actions.
	 * @see Kohana_Controller::before()
	 */
	public function before()
	{
		// Create the layout view
		$this->_layout_view = View::factory($this->_layout);
		
		// Add the first javascript, jQuery
		// This class __NEEDS__ to be loaded first, so it is added right now
		Media::add_script(URL::base(TRUE, FALSE).'media/js/jquery-1.11.1.min.js');
	}
	public function after()
	{
		// Prepare for final view rendering
		if ($this->_layout_view == NULL)
			throw HTTP_Exception::factory(500, 'No layout view was loaded in before()!');
		
		if ($this->_content == NULL)
			$this->_content = "";
			
		// Render view to string here
		// We do this, in order to evaluate the view code and for example add missing media to the media class.
		else if ($this->_content instanceof View)
			$this->_content = $this->_content->render();
			
		// Set content to view
		$this->_layout_view->bind('content', $this->_content);

		// Render!
		$this->response->body($this->_layout_view->render());
	}
}
´´´

The layout view will get the evaluated content views as $content.

Example layout view:
```PHP
<html>
	<head>
		<title>Example</title>
		<?php echo Media::get_html_styles_and_scripts(); ?>
	</head>
	<body>
		<?php echo $content; ?>
	</body>
</html>
´´´


Third-Party Licenses
=======================

Select2: https://github.com/select2/select2/blob/master/LICENSE.md
Datatables: https://www.datatables.net/license/mit