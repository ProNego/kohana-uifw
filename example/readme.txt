This is an example on how to use the kohana uifw and it's modules.

First, you need to create a new kohana project, so download the newest version from www.kohanaframework.org.

Then install kohana framework (change bootstrap, etc.).
Now include all the uifw modules like so:

Kohana::modules(array(
	// 'auth'       => MODPATH.'auth',       // Basic authentication
	// 'cache'      => MODPATH.'cache',      // Caching with multiple backends
	// 'codebench'  => MODPATH.'codebench',  // Benchmarking tool
	// 'database'   => MODPATH.'database',   // Database access
	// 'image'      => MODPATH.'image',      // Image manipulation
	// 'minion'     => MODPATH.'minion',     // CLI Tasks
	'datatables'     => MODPATH.'datatables',	// Datatables module
	'select2'     => MODPATH.'select2',	// select2 module
	'uifw'     => MODPATH.'uifw',	// UI Framework module
	// 'orm'        => MODPATH.'orm',        // Object Relationship Mapping
	// 'unittest'   => MODPATH.'unittest',   // Unit testing
	// 'userguide'  => MODPATH.'userguide',  // User guide and API documentation
	));

Copy the UIFWExamples.php file to application/classes/Controller/UIFWExamples.
Now you should be ready to view it by opening http://yourhost.tld/your/path/to/kohana/UIFWexamples/index in your Browser.