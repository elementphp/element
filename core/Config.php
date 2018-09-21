<?php

namespace element\core;

/**
 * <p>Singleton class</p>
 * @return array
 */
final class Config
{
	public $_config;
	
	public static function get($element, $part){
		return Config::Instance()->_config[$element][$part];
	}

	public static function getSection($element){
		return Config::Instance()->_config[$element];
	}

	public static function Instance()
	{
        static $inst = null;
		if ($inst === null) {
			$inst = new self();
		}
		return $inst;
	}
	private function __construct() { 
		if(file_exists("../app/configuration/configuration.php")){
			require_once "../app/configuration/configuration.php";
			$this->_config = $config;
		} else {
			die("No configuration file found");
		}
		
	}
	private function __clone() { }
}