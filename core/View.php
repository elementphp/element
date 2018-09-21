<?php

namespace element\mvc;

require('Smarty/libs/Smarty.class.php');

class View{

    public $viewDisabled = false;

    public $viewname = ""; 

    public $currentView = null;

    public function __construct($v) {
        if( $v !=="" && !$this->viewDisabled ) {

            $this->currentView = new \Smarty();
            $this->currentView->setTemplateDir(\element\core\Config::get('view','templateDir'));
            $this->currentView->setCompileDir(\element\core\Config::get('view','compileDir'));
            $this->currentView->setCacheDir(\element\core\Config::get('view','cacheDir'));
            $this->currentView->caching = \element\core\Config::get("view", "caching");
            
            $this->viewname = $v;

            $d = $this->defaults();

            foreach($d as $key => $value) {
                $this->currentView->assign($key, $value);
            }

        }
    } 

    private function defaults(){
        $protocol = (isset($_SERVER['HTTPS'])) ? "https://" : "http://";
        $absoluteRef = $protocol . \element\core\Config::get('domain', 'host') . \element\core\Config::get('domain', 'root');
       
        $d = [
            "absoluteUrl" => $absoluteRef
        ];
        return $d;
    }

    public function assign($name, $value){
        $this->currentView->assign($name,$value);
    }

    public function display(){
        if($this->currentView && !$this->viewDisabled){
            $this->currentView->display($this->viewname);
        } 
    }

    public function disable(){
        $this->viewDisabled = true;
    }
}