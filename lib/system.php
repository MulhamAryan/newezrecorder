<?php
    class System{
        function removeCharacters($string){
            $string = str_replace(' ', '-', $string);
            $string = preg_replace('/[^A-Za-z0-9\-]/', '', $string);
            return $string;
        }

    }
