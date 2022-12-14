<?php 

namespace app\core;

class Request 
{
    public function getPath()
    {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $position = strpos($path, '?');
        if($position !== false){
            return substr($path, 0 , $position);
        }

        return $path;
    }

    public function getMehtod()
    {
        return strtolower($_SERVER["REQUEST_METHOD"]);
    }

    public function isGet(){
        return $this->getMehtod() === 'get';
    }

    
    public function isPost(){
        return $this->getMehtod() === 'post';
    }

    public function getBody()
    {
        $body = [];
        if($this->getMehtod() === 'get'){
            foreach($_GET as $key => $value){
                $body[$key] = filter_input(INPUT_GET, $key, FILTER_SANITIZE_SPECIAL_CHARS);

            }
        }

        if($this->getMehtod() === 'post'){
            foreach($_POST as $key => $value){
                $body[$key] = filter_input(INPUT_POST, $key, FILTER_SANITIZE_SPECIAL_CHARS);
            }
        }
        return $body;
    }
}
