<?php
    class templates{
        function error($msg){
            return "<div class=\"alert alert-danger\" role=\"alert\">$msg</div>";
        }
    }

    $tmp = new templates();
?>