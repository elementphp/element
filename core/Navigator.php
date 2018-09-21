<?php

namespace element\core;

class Navigator{
	
	private static $request;

	public static function navigate(){
		self::$request = new Request();
		self::arrangeParams();
	}

	private static function arrangeParams(){
		
		$url = ltrim(self::$request->uri,"/");

		$direct = strpos($url,".php");


		$response = [
			"controller" => Config::get("defaults", "default_controller"),
			"action"	 => Config::get("defaults", "default_action"),
			"args"     => []
		];

		if( $direct ) {
		
			$params = explode("/",$url);
			$fileName = end($params);
			$namePos = strpos($fileName,'Controller');

			if(!$namePos) {
				echo "No controller with that name.";
			} else {
				$response["controller"] = substr($fileName,0,$namePos);
				if( isset($_GET['action']) )$response["action"] = $_GET['action'];
			}
		} else {

			$params = explode('/', $url);

			if(Config::get("domain", "root")!==""){
				array_shift($params);
			}
			
			if ( count($params) > 0 ){

				$response["controller"] = ( isset($params[0]) && !empty($params[0]) ) ? $params[0] : Config::get("defaults", "default_controller");
				$response["action"] 	= ( isset($params[1]) && !empty($params[1]) ) ? $params[1] : Config::get("defaults", "default_action");

				foreach( $params as $key => $value ) {
					if($key !== 0 && $key !== 1)$response["args"][$key] = $value;
				}
			}
		}
		try{	
			self::go(ucfirst($response["controller"]), $response["action"], $response["args"]);
		} catch (\Exception $ex){
			echo $ex->getMessage();
		//	echo "Sorry ... this has massively broken.";
		}
	}
	
	private static function go($className, $method, $args=[]){
		
		try{
			$class = 'element\mvc\\' . $className . Config::get("defaults", "controller_suffix");
			$controller = new $class($method,self::$request);
			$method = $method . Config::get("defaults", "action_suffix");
			$controller->getFunction($method, $args);
		} catch(Exception $ex) {
			echo $ex->getMessage();
		}	
		
	}

}