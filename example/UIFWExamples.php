<?php
/**
 * UIFramework example controller.
 * @author KennuX
 *
 */
class Controller_UIFWExamples extends Kohana_Controller
{
	public function before()
	{
		// Jquery must be loaded first, so we include it in before()
		Media::add_script('media/jquery/jquery-1.11.3.min.js');
	}
	
	public function action_index()
	{
		// Print menu
		$this->response->body('<a href="'.URL::site('UIFWExamples/datatables').'">Datatables</a><br>'.
				'<a href="'.URL::site('UIFWExamples/datatables_ajax').'">Datatables Ajax</a><br>'.
				'<a href="'.URL::site('UIFWExamples/select2').'">Select2</a><br>');
	}
	
	/**
	 * static datatables example action
	 */
	public function action_datatables()
	{
		// Column definition
		$columns = array(
			"test1" => "Testcolumn #1",
			"test2" => "Testcolumn #2",
		);

		// Generate random data
		$data = array();
		for ($i = 0; $i < 2000; $i++)
		{
			$data[] = array('test1' => $this->generateRandomString(25), 'test2' => $this->generateRandomString(25));
		}
		
		// Create datatable
		$datatable = new Datatable($columns);
		$datatable->add_entries($data);
		
		// Render html code from datatable->render() with the html layout
		$this->renderHtml($datatable->render());
	}
	
	/**
	 * ajax-based serverside processed datatables example action
	 */
	public function action_datatables_ajax()
	{
		// Column definition
		$columns = array(
			"test1" => "Testcolumn #1",
			"test2" => "Testcolumn #2",
		);

		// Generate random data
		$data = Session::instance()->get('dattables_example', array());
		
		if (empty($data))
		{
			for ($i = 0; $i < 5000; $i++)
			{
				$data[] = array('test1' => $this->generateRandomString(25), 'test2' => $this->generateRandomString(25));
			}
		}
		
		// Create datatable
		$datatable = new Datatable($columns);
		
		// Here you could also pass in a orm model like this: $datatable->add_entries(ORM::factory('Model'));
		// Then the model needs to have columns named like the keys in the column definition, so in this example
		// test1 and test2 are needed properties on the model.
		$datatable->add_entries($data);
		
		$datatable->set_serverside(URL::base().'UIFWExamples/datatables_ajax');
		
		// Render html code from datatable->render() with the html layout
		
		if (Request::$current->is_ajax())
			echo $datatable->ajax_render();
		else
			$this->renderHtml($datatable->render());
	}
	
	/**
	 * Select2 demo action
	 */
	public function action_select2()
	{
		$select2 = new Select2(array("testval1" => "Test Entry #1","testval2" => "Test Entry #2","testval3" => "Test Entry #3"), 'testselect');
		$this->renderHtml($select2->render());
	}
	
	/**
	 * Renders the given html code in the body of a basic html layout.
	 * @param unknown $html
	 */
	private function renderHtml($html)
	{
		$result = '<html>
				<head>
					'.Media::get_html_styles_and_scripts().'
				</head>
				<body>
					'.$html.'
				</body>
		</html>';
		
		$this->response->body($result);
	}
	
	/**
	 * Helper function to generate a random string
	 * @param number $length
	 */
	private function generateRandomString($length = 10)
	{
		// Source: http://stackoverflow.com/questions/4356289/php-random-string-generator
	    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	    $charactersLength = strlen($characters);
	    $randomString = '';
	    for ($i = 0; $i < $length; $i++) {
	        $randomString .= $characters[rand(0, $charactersLength - 1)];
	    }
	    return $randomString;
	}
}