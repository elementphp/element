<?php

namespace element\core;

class Response{

    public function redirect($url)
    {

        if($url === Config::get("errors", "redirect_location")) {
            
            $protocol = (isset($_SERVER['HTTPS'])) ? "https://" : "http://";
            $absoluteRef = $protocol . \element\core\Config::get('domain', 'host') . \element\core\Config::get('domain', 'root');
            
            $url = $absoluteRef . "/" . $url;
        }

        if(headers_sent())
        {
            $string = '<script type="text/javascript">';
            $string .= 'window.location = "' . $baseUri.$url . '"';
            $string .= '</script>';

            echo $string;
        }
        else
        {
            header('Location: '.$url);
        }
        exit;
    }
}