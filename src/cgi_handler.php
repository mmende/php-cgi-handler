<?php
/**
 * This script helps cgi parameter handling by specifying flags and specific value types expected for them
 */

/**
 * These are the available types
 */
abstract class CGI_TYPE {
	const STRING = 0;
	const BOOL = 1;
	const INT = 2;
	const FLOAT = 3;
}

/**
 * This class processes the command line arguments
 */
class CGI_Handler {

	private $flags = [];
	private $non_optionals_set = true;

	function __construct($flags=[]) {
		$this->flags = $flags;
		$this->processArgs();
		$this->checkForOptionals();
	}

	/**
	 * Returns a value for a given flag
	 *
	 * @param  string  $value The flags name
	 * @param  integer $index An optional index if the flag contains several values
	 *
	 * @return mixed          The specific value if such was found or false otherwise
	 */
	public function get($value, $index=0) {
		// return false if the flag does not exist
		if(array_key_exists($value, $this->flags)===false) {
			//echo "Flag $value does not exist...\n";
			return false;
		}
		// return false if values does not exist
		if(array_key_exists('values', $this->flags[$value])===false) {
			//echo "Value for $value was not specified...\n";
			return false;
		}
		// return false if the index is out of range
		if($index >= count($this->flags[$value]['values'])) {
			//echo "Index $index not in range of $value values...\n";
			return false;
		}
		return $this->flags[$value]['values'][$index];
	}

	/**
	 * Returns all values
	 *
	 * @return array An array with flags as keynames and values as value array
	 */
	public function getAll()
	{
		// Reshape to flags[ flag => [value1, value2], flag2 => [value1] ]
		$reshaped = [];
		foreach ($this->flags as $flag => $values) {
			if(array_key_exists('values', $values)) {
				$reshaped[$flag] = $values['values'];
			} else {
				$reshaped[$flag] = [false];
			}
		}
		return $reshaped;
	}

	/**
	 * Returns if all non optional flags where set
	 *
	 * @return bool If all non optionals where set
	 */
	public function nonOptionalsSet()
	{
		return $this->non_optionals_set;
	}

	/**
	 * Prints the manual
	 */
	public function printManual()
	{
		global $argv;
		echo "Usage: php " . $argv[0] . " <options>\n\n";
		echo "Options:\n";
		foreach ($this->flags as $flag => $values) {
			$description = array_key_exists('description', $values) ? $values['description'] : '';
			if(array_key_exists('optional', $values) && $values['optional']===false) {
				echo "-" . $flag . "\t\t" . $description . "\n";
			} else {
				echo "[-" . $flag . "]\t\t" . $description . "\n";
			}
		}
	}

	/**
	 * Processes all command line arguments
	 */
	private function processArgs() {
		global $argc, $argv;
		// Iterate over all flags or values
		for ($i = 1; $i < $argc; $i++) { 
			$is_flag = strcmp(substr($argv[$i], 0, 1), '-')===0;
			$flag = $is_flag ? substr($argv[$i], 1) : $argv[$i];

			//echo $is_flag ? "Flag " . $argv[$i] . " detected...\n" : "Non flag detected " . $argv[$i] . "\n";
			if($is_flag && array_key_exists($flag, $this->flags)) {

				// The key was found in the list of flags
				if(array_key_exists('types', $this->flags[$flag])) {

					// Loop over values and check their types
					for ($j = 0; $j < count($this->flags[$flag]['types']); $j++) {
						// Get the next value from cli
						++$i;
						
						// Check if this is another flag
						$no_value = strcmp(substr($argv[$i], 0, 1), '-')===0;
						if($no_value) {
							//echo "$j value for $flag not specified";
							$i--;
							break;
						}

						// Try to add the value with it's specified type
						$this->flags[$flag]['values'][$j] = $this->getValueByType( $this->flags[$flag]['types'][$j], $argv[$i] );
					}


				} else {

					// This is a boolean flag only
					$this->flags[$flag]['values'] = [true];

				}

				// Indicator for non optionals
				$this->flags[$flag]['set'] = true;

			} else if($is_flag===false) {

				//echo "Unflagged value $flag, added to common...\n";
				// Create commons if it does not exist
				if(array_key_exists('common', $this->flags)===false || array_key_exists('values', $this->flags['common'])===false) {
					$this->flags['common'] = ['values' => []];
				}

				// Add the value to the common flags
				$this->flags['common']['values'][ count($this->flags['common']['values']) ] = $argv[$i];

			} else {
				echo "Unknown option $flag, skipping...\n";
			}
		}
	}

	/**
	 * Determines if all optionals where set
	 */
	private function checkForOptionals()
	{
		foreach ($this->flags as $flag => $values) {
			if(array_key_exists('optional', $values) && $values['optional']===false) {
				// The value is non optional and should have been set
				if(array_key_exists('set', $values) === false) {
					$this->non_optionals_set = false;
					return;
				}

			}
		}
	}

	/**
	 * Returns the casted value with it's specific type
	 *
	 * @param  CGI_TYPE $cgi_type The data type
	 * @param  mixed    $value    The value
	 *
	 * @return mixed              The casted type or the original value on fail
	 */
	private function getValueByType($cgi_type, $value) {
		if($cgi_type===CGI_TYPE::STRING) return strval($value);
		if($cgi_type===CGI_TYPE::BOOL) return boolval($value);
		if($cgi_type===CGI_TYPE::INT) return intval($value);
		if($cgi_type===CGI_TYPE::FLOAT) return doubleval($value);
		return $value;
	}

}