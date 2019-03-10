<?php

namespace element\mvc;

/**
 * <p>Controller Base Class</p>
 *
 */
 abstract class Controller{
	
	/**
	 * @var object
	 */
	protected $config;

	/**
	* @var object
	*/
	protected $request;

	/**
	* @var object
	*/
	protected $response;

	/**
	* @var string
	*/
	protected $action;

	/**
	* @var string
	*/
	public $controllerName;

	/**
	* @var boolean
	*/
	public $overrideView = false;

	public function __construct($method,$request){
		$this->config = \element\core\Config::Instance();
		$this->session = new \element\core\Session();
		$this->response = new \element\core\Response();
		$this->request = $request;
		if(\element\core\Config::get('session','autostart')) {
			$this->session->startSession();
		}
		$this->action = $method;
		$this->beforeLoad();
	}

	protected function beforeLoad(){}

	/**
		* <p>Allows calling method using string</p>
		* 
		* @param string $method
		* @param array $args
		* @return method
		*/
	public function getFunction($method, $args = []){
		
		$class = get_called_class();

		$parent = str_replace("element\mvc\\", "", str_replace(\element\core\Config::get("defaults","controller_suffix"), "", $class));
		$child = str_replace(\element\core\Config::get("defaults", "action_suffix"), "", $method);

		$this->controllerName = $parent;
		
		$currentView = "../app/views/" . strtolower($parent) . "/" . strtolower($child) . ".tpl";

		$v = "";
		if(file_exists($currentView)){
			$v = $currentView;
		} 
		$this->view = new View($v);

		if (method_exists($this, $method)) {
			call_user_func_array(array($this, $method), $args);
			if(!$this->overrideView)$this->view->display();
		}
		else{
			// if($this->config::get("errors","redirect")) {
			// 	$location = $this->config::get("errors", "redirect_location");
			// 	$this->response->redirect($location);
			// } else {
				echo "No action with that name.";
			// }
		}
	}
	
	public function setView($tpl) {
	    $parent = $this->controllerName;
		$currentView = "../app/views/" . strtolower($parent) . "/" . strtolower($tpl) . ".tpl";
		$v = null;
		if(file_exists($currentView)){
			$this->overrideView = true;
			$v = $currentView;
			if(empty($this->view->currentView)){
				$this->view = new View($v);
			}
			$this->view->viewname = $currentView;
        }
	}


	public function showView(){
		$this->view->display();
	}
}
