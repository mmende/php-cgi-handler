<?php

namespace CGI;

include __DIR__ . '/../src/cgi_handler.php';

$opts = [
	new Option('file', [
		new VALUE(Type::STRING, 'Filename', '', false)
	], 'The audio file to edit', false),
	new Option('trim', [
			new VALUE(Type::FLOAT, 'start', 0.0),
			new VALUE(Type::FLOAT, 'end', -1.0)
	], 'Trims the audio between the given range in seconds'),
	new Option('header', [], 'Set this flag if the audio contains a header'),
	new Option('help', [], 'Prints this help page')
];

$cgi = new Handler($opts);


if($cgi->get('help')) {
	// Print the manual
	$cgi->printManual();
	
} else {

	// Get a specific value
	$file = $cgi->get('file');
	$from = $cgi->get('trim', 0);
	$to = $cgi->get('trim', 1);

	echo "Trimming $file from $from to $to...\n";

	if($cgi->get('header')) {
		echo "The file has a header\n";
	} else {
		echo "The file has no header\n";
	}

	// Or all values
	print_r($cgi->getAll());
}

// Without namespace
/*
$opts = [
	new \CGI\Option('file', [
		new \CGI\VALUE(\CGI\Type::STRING, 'Filename', '', false)
	], 'The audio file to edit', false),
	new \CGI\Option('trim', [
			new \CGI\VALUE(\CGI\Type::FLOAT, 'start', 0.0),
			new \CGI\VALUE(\CGI\Type::FLOAT, 'end', -1.0)
	], 'Trims the audio between the given range in seconds'),
	new \CGI\Option('header', [], 'Set this flag if the audio contains a header'),
	new \CGI\Option('help', [], 'Prints this help page')
];

$cgi = new \CGI\Handler($opts);

// Print the manual if an optional was not set
if($cgi->get('help')) {
	$cgi->printManual();
} else {

	// Get a specific value
	$from = $cgi->get('trim', 0);
	$to = $cgi->get('trim', 1);

	echo "Trimming from $from to $to...\n";

	echo $cgi->get('header');

	// Or all values
	print_r($cgi->getAll());
}*/