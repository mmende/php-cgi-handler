<?php

namespace CLI;

include __DIR__ . '/../src/cli_handler.php';

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

$cli = new Handler($opts);


if($cli->get('help')) {
	// Print the manual
	$cli->printManual();
	
} else {

	// Get a specific value
	$file = $cli->get('file');
	$from = $cli->get('trim', 0);
	$to = $cli->get('trim', 1);

	echo "Trimming $file from $from to $to...\n";

	if($cli->get('header')) {
		echo "The file has a header\n";
	} else {
		echo "The file has no header\n";
	}

	// Or all values
	print_r($cli->getAll());
}