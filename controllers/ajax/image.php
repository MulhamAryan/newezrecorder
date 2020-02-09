<?php

    $fileName = isset($input["filename"]) ? $input["filename"] : "";
    $extension = isset($input["extension"]) ? $input["extension"] : "";
    $fileDir = $config["var"] . "/" . $fileName . "." . $extension;

    if(is_file($fileDir) && file_exists($fileDir)){
        header('Content-Type: image/jpeg');
        readfile($fileDir);
    }
    else{
        return false;
    }