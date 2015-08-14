# The CGI Handler class helps writing php scripts called by cli

The usage is pretty simple. You just have to create a CGI_Handler instance with a option array specifying the desired flags. When calling the script from cli flags are those values starting with a - like -file.

```php
include 'cgi_handler.php';

$opts = [
	'file' => [
		'optional' => false,	// This flag should be specified
		'types' => [
			CGI_TYPE::STRING
		],
		'description' => '<path> The audio file to edit'
	],
	'trim' => [
		'types' => [
			CGI_TYPE::FLOAT,
			CGI_TYPE::FLOAT
		],
		'values' => [
			0.0,				// an default value
			-1.0				// another default value
		],
		'description' => 'Trims the audio between <start> and <end>'
	],
	'header' => [				// If you only need to know if the flag was set or not
		'description' => 'Set this flag if the audio contains a header'
	]
];
$cgi = new CGI_Handler($opts);

// Print the manual if an optional was not set
if($cgi->nonOptionalsSet()===false) $cgi->printManual();

```

The script could then called via cli like `php demo/demo.php -file test.audio -trim 10.5 42.1`.