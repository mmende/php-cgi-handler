<?php
/**
 * The MIT License (MIT)
 * 
 * Copyright (c) 2015 Martin Mende
 * 
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 * 
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace CLI {

	/**
	 * The colors available for cli
	 */
	abstract class COLOR {
		const BLACK = 30;
		const BLUE = 34;
		const GREEN = 32;
		const CYAN = 36;
		const RED = 31;
		const PURPLE = 35;
		const BROWN = 33;
		const LIGHTGRAY = 37; 
	}

	/**
	 * Wraps $msg with a cli color code
	 *
	 * @param  string $msg   The message to colorize
	 * @param  int    $color A color code
	 *
	 * @return string        The colorized string
	 */
	function _c($msg, $color) {
		return "\033[" . $color . "m " . $msg . " \033[0m";
	}

	/**
	 * These are the available types
	 */
	abstract class Type {
		const STRING = 0;
		const BOOL = 1;
		const INT = 2;
		const FLOAT = 3;
	}

	/**
	 * A value to add for an option
	 */
	class Value {
		public $type;
		public $description;
		public $optional;

		public $value = '';
		public $set = false;

		function __construct($type, $description='', $default='', $optional=true) {
			$this->type = $type;
			$this->description = $description;
			$this->value = $default;
			$this->optional = $optional;
		}

		public function set($value)
		{
			$this->value = $this->getTypedValue($value);
			$this->set = true;
		}

		public function getPrintableType()
		{
			if($this->type===Type::STRING) return "string";
			if($this->type===Type::BOOL) return "bool";
			if($this->type===Type::INT) return "int";
			if($this->type===Type::FLOAT) return "float";
			return "Unknown";
		}

		/**
		 * Returns the casted value with it's specific type
		 *
		 * @param  CGI_TYPE $cgi_type The data type
		 * @param  mixed    $value    The value
		 *
		 * @return mixed              The casted type or the original value on fail
		 */
		private function getTypedValue($value) {
			if($this->type===Type::STRING) return strval($value);
			if($this->type===Type::BOOL) return boolval($value);
			if($this->type===Type::INT) return intval($value);
			if($this->type===Type::FLOAT) return doubleval($value);
			return $value;
		}
	}

	/**
	 * An option (flag) fetch from command line
	 */
	class Option {
		public $name;
		public $description;
		public $optional;

		public $values;
		public $set = false;

		function __construct($name, $values=[], $description='', $optional=true) {
			$this->name = $name;
			$this->values = $values;
			$this->description = $description;
			$this->optional = $optional;
		}

		public function setValue($value, $index=0)
		{
			if( count($this->values) > $index ) {
				$theValue = $this->values[$index];
				$theValue->set($value);
			} else {
				echo "Index $index out of value range\n";
			}
		}

		public function getValue($index)
		{
			if( count($this->values) > $index ) {
				return $this->values[$index]->value;
			} else {
				return false;
			}
		}

		public function setTrue()
		{
			array_push($this->values, new Value(Type::BOOL, '', true));
		}
	}

	/**
	 * The Handler
	 */
	class Handler {

		private $options;
		private $flagPrefix;

		function __construct($options=[], $flagPrefix='--') {
			$this->options = $options;
			$this->flagPrefix = $flagPrefix;

			$this->processOptions();

			//print_r($this->options);
			$this->checkOptionals();
		}

		public function nonOptionalsSet()
		{
			return false;
		}

		public function get($flag, $value=0)
		{
			$option = $this->getOption($flag);
			if($option!==false) {
				return $option->getValue($value);
			}
		}

		public function getAll()
		{
			$values = [];
			foreach ($this->options as $option) {
				$values[$option->name] = [];
				$i = 0;
				foreach ($option->values as $value) {
					$values[$option->name][$i] = $value->value;
					++$i;
				}
				if(count($option->values) <= 0) {
					$values[$option->name][$i] = false;
				}
			}
			return $values;
		}

		public function printManual()
		{
			global $argv;
			echo _c("Usage: ", COLOR::LIGHTGRAY) . "\n\t" . _c("php", COLOR::RED) . _c($argv[0], COLOR::PURPLE) . _c(" <options>", COLOR::BLUE) . "\n\n";
			echo _c("Options:", COLOR::LIGHTGRAY) . "\n\n";
			foreach ($this->options as $option) {
				if($option->optional) {
					echo _c("[" . $this->flagPrefix . $option->name . "]", COLOR::BLUE) . "\n\t";
				} else {
					echo _c($this->flagPrefix . $option->name, COLOR::BLUE) . "\n\t";
				}
				foreach ($option->values as $value) {
					if(strlen($value->description) > 0)
						echo _c($value->getPrintableType(), COLOR::BROWN) . _c("<" . $value->description . "> ", COLOR::RED) . "\n\t";
				}
				echo " " . $option->description . "\n\n";
			}
		}

		private function processOptions()
		{
			global $argv;
			for ($i = 1; $i < count($argv); ++$i) {
				// Check whether this argument is a flag or a value
				$is_flag = strcmp(substr($argv[$i], 0, strlen($this->flagPrefix)), $this->flagPrefix)===0;

				$flag = $is_flag ? substr($argv[$i], strlen($this->flagPrefix)) : $argv[$i];
				$option = $is_flag ? $this->getOption($flag) : false;

				if($is_flag && $option!==false) {
					$option->set = true;
					if(count($option->values) <= 0) {
						// This value is a flag only
						$option->setTrue();
					} else {
						// loop over values
						for ($j=0; $j < count($option->values); ++$j) {
							// Get the next value from cli
							++$i;
							// Check if end is reached
							if($i >= count($argv))
								return;
							// Check if this is another flag
							$no_value = strcmp(substr($argv[$i], 0, strlen($this->flagPrefix)), $this->flagPrefix)===0;
							if($no_value) {
								$i--;
								break;
							}
							// Add a value
							$option->setValue($argv[$i], $j);
						}
					}
				} else {
					echo "Undefined option $flag, skipping...\n";
				}
			}
		}

		private function checkOptionals()
		{
			foreach ($this->options as $option) {
				// Check if the flag itself is non optional and not set
				if($option->optional===false && $option->set===false) {
					echo "\n" . _c("The option " . $this->flagPrefix . $option->name . " is not optional.", COLOR::RED) . "\n\n";
					$this->printManual();
					die;
				}
				// Check all values for optionality if the option is set
				if($option->set===true)
					foreach ($option->values as $value) {
						if($value->optional===false && $value->set===false) {
							echo "\n" . _c("The option " . $this->flagPrefix . $option->name . " has non optional values.", COLOR::RED) . "\n\n";
							$this->printManual();
							die;
						}
					}
			}
		}

		private function getOption($name)
		{
			foreach ($this->options as $option) {
				if ($option->name===$name) return $option;
			}
			return false;
		}

	}
}