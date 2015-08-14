<?php

include __DIR__ . '/../src/cgi_handler.php';

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
if($cgi->nonOptionalsSet()===false) {
	$cgi->printManual();
} else {

	// Get a specific value
	$from = $cgi->get('trim', 0);
	$to = $cgi->get('trim', 1);

	echo "Trimming from $from to $to...\n";

	// Or all values
	print_r($cgi->getAll());
}