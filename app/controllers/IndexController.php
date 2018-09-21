<?php

namespace element\mvc;

class IndexController extends Controller{

    public function indexAction(){
        // set a view variable
        $this->view->assign("welcomeMessage", "Keep it simple!");
    }


}
