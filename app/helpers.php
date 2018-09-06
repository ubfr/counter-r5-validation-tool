<?php
function pr($value){

    echo"<pre>";
    print_r($value);
    echo"</pre>";
}

function customRequestCaptcha(){
    return new \ReCaptcha\RequestMethod\Post();
    }
?>