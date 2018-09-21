<?php
    $config = [
				"domain" =>[
					"host" => "localhost/",
					"root" => "element"
				],
				"db" => [
					"dsn"	=>  "",
					"host" 	=> 	"localhost",
					"user" 	=> 	"root",
					"pw"   	=> 	""
				],
				"defaults" => [
					"controller_suffix" 	=> "Controller",
					"action_suffix" 		=> "Action",
					"default_controller" 	=> "Index",
					"default_action" 		=> "index"
				],
				"view" => [
					"templateDir" => '../app/views/',
					"compileDir"  =>'../app/templates_c/',
					"cacheDir"    =>'../app/cache/',
					"caching" => 0
				], 
				"session" => [
					"autostart" => true
				],
				"errors" => [
					"redirect" => true,
					"redirect_location" => "404.html"
				]
			];