<?php

namespace element\core;

class Session {

    public function startSession(){
        if(!isset($_SESSION)) {
			session_start();
		}
    }

    public function endSession(){
        if(isset($_SESSION)){
            session_unset();
            session_destroy();
        }
    }

    public function setVar($name, $value){
        if(isset($_SESSION)){
            $_SESSION[$name] = $value;
        }
    }

    public function getVar($name){
        if(isset($_SESSION) && isset($_SESSION[$name])) {
            return $_SESSION[$name];
        } else {
            return false;
        }
    }

    public function deleteVar($name) {
        if(isset($_SESSION) && isset($_SESSION[$name])) {
            unset($_SESSION[$name]);
        }
    }
}