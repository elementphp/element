<?php

namespace element\core;

/**
 * Holds information about the http request
 */
class request {

    public $uri;

    public $post   = false;
    public $get    = false;
    public $head   = false;
    public $delete = false;
    public $put    = false;

    public $postVars    = [];
    public $getVars     = [];

    public function __construct(){
        $this->formRequest();	
    }
	
	public function getUrl(){
		return $this->uri;
	}

    private function formRequest(){
        
        $this->uri      = $_SERVER['REQUEST_URI'];
        $requestType    = $_SERVER['REQUEST_METHOD'];

		switch( $requestType ) {
			case "GET":
				$this->get 	= true;
				$this->getVars = $_GET;
				break;
			case "POST":
				$this->post 	 = true;
				$this->postVars = $_POST;
				$this->getVars = $_GET;
				break;
			case "PUT":
				$this->put = true;
				break;	
			case "DELETE":
				$this->delete = true;
				break;	
			case "HEAD":
				$this->head = true;
				break;				 
		}
    }


}