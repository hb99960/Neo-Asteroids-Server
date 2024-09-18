<?php

class App{

    private $controller = 'Asteroid';
    private $method = 'index';
    private $params = [];
    
    private function splitURL(){
        $URL = $_GET['url'] ?? 'home';
    $URL = explode("/", $URL);
    return $URL;
    }
    
    public function loadController(){
        // echo "Inside loadController";
        $URL = $this->splitURL();
        // print_r($URL);
        $filename = "../app/controllers/".ucfirst($URL[0]).".php";
        // echo $filename;
        if(file_exists($filename)){
            require $filename;
            $this->controller = ucfirst($URL[0]);
            if (!empty($URL[1])) {
                // print_r($URL[1]);
                $this->method = $URL[1];
            }

            $this->params = !empty($URL) ? array_values($URL) : [];

        }else{
            $filename = "../app/controllers/_404.php";
            require $filename;
            $this->controller = "_404";
        }
        
        $controller = new $this->controller;
        call_user_func_array([$controller, $this->method],$this->params);
    }
    
}
