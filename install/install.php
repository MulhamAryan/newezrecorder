<?php
    if(posix_geteuid() != 0){
        echo "\e[1m\e[31mThis script should run as root please use sudo while installing EZrecorder : php install.php\e[0m" . PHP_EOL;
    }
    else{
        echo "Welcome in this installation script !" . PHP_EOL;
        echo "This script is aimed to install the following components of EZcast:" . PHP_EOL;
        echo "\e[1m\e[32m--------------------------------------------------------------\033[0m" . PHP_EOL;
        echo "\e[1m\e[32m---   EZrecorder : for automated recording in classrooms   ---\033[0m" . PHP_EOL;
        echo "\e[1m\e[32m--------------------------------------------------------------\033[0m" . PHP_EOL;
        echo "First of all, this script will check if every needed programs, commands," . PHP_EOL;
        echo "and libraries required by EZrecorder are installed" . PHP_EOL;

    }
?>