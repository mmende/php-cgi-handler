# The CLI Handler classes help working with command line parameters

It is possible to specify possible options, values associated with these options and print a manual for the specification. The values can then easily be accessed from the handler.

The classes are in the `CLI` namespace. To create a `\CLI\Handler` instance you create an array of `\CLI\Option`s.
The options as well as the options values can be set to non optional. If a non optional was not set the manual will be printed automatically and the user will be informed about the non optional option or value. The values will automatically be casted to the specified type and can be accessed with `get($option, $index=0)` or in the array `getAll()`.

```php
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
```

A simple example application can be seen in the demo.php and would then e.g. called like `$ php demo/demo.php -file test.audio -trim 10.5 42.1`.

## Methods

Method | Description
-------|------------
`get(option, [index])` | Returns a value for a specific option
`getAll()` | Returns an array with all values
`printManual()` | Prints a manual created with the descriptions specified