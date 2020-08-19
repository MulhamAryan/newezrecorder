<?php
    class templates{
        function error($msg){
            return "<div class=\"alert alert-danger\" role=\"alert\">{$msg}</div>";
        }

        function success($msg){
            return "<div class=\"alert alert-success\" role=\"alert\">{$msg}</div>";
        }

        function loadTempFile($tmpFile){
            global $config;
            global $lang;

            if(file_exists($config["basedir"] . $config["main"]->templates . "/" . $tmpFile)){
                return $config["basedir"] . $config["main"]->templates . "/" . $tmpFile;
            }
            else {
                return "Error: template $tmpFile not found";
            }
        }

        function isChecked($value1,$value2){
            if($value1 == $value2)
                return "checked";
        }

        function isSelected($value1,$value2){
            if($value1 == $value2)
                return "selected";
        }

        function alertDialog($title,$text){
            return "<div id=\"dialog-message\" title=\"{$title}\">{$text}</div>";
        }
    }

    $tmp = new templates();
?>