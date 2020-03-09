<?php
    class templates{
        function error($msg){
            return "<div class=\"alert alert-danger\" role=\"alert\">$msg</div>";
        }

        function loadFile($tmpFile){
            global $config;
            global $lang;

            if(file_exists($config["basedir"] . $config["templates"] . "/" . $tmpFile)){
                return $config["basedir"] . $config["templates"] . "/" . $tmpFile;
            }
            else
                return "Error: template $tmpFile not found";
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
            $value = '<div id="dialog-message" title="' . $title . '">';
            $value .= $text;
            $value .= '</div>';
            return $value;
        }
    }

    $tmp = new templates();
?>