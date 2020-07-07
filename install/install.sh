#!/bin/bash
if (( $EUID != 0 )); then
    echo "This script should run as root please use sudo while installing EZrecorder : sudo ./install.sh"
    exit 1
fi
error=0
echo "Welcome in this installation script !"
echo "This script is aimed to install the following components of EZcast:"
echo -e "\e[1m\e[32m--------------------------------------------------------------\033[0m"
echo -e "\e[1m\e[32m---   EZrecorder : for automated recording in classrooms   ---\033[0m"
echo -e "\e[1m\e[32m--------------------------------------------------------------\033[0m"
echo "First of all, this script will check if every needed programs, commands,"
echo "and libraries required by EZrecorder are installed"

if ! [ -x "$(command -v apachectl)" ]; then
  echo -e "- APACHE \e[1m\e[31mis not installed.\033[0m" >&2
  error=1
else
  echo -e "- APACHE \e[1m\e[32mis installed.\033[0m" >&2
  apache_path="$(which apachectl)"
fi

if ! [ -x "$(command -v ssh)" ]; then
  echo -e "- SSH \e[1m\e[31mis not installed.\033[0m" >&2
  error=1
else
  echo -e "- SSH \e[1m\e[32mis installed.\033[0m" >&2
  ssh_path="$(which ssh)"
fi

if ! [ -x "$(command -v scp)" ]; then
  echo -e "- SCP \e[1m\e[31mis not installed.\033[0m" >&2
  error=1
else
  echo -e "- SCP \e[1m\e[32mis installed.\033[0m" >&2
  scp_path="$(which scp)"
fi

if ! [ -x "$(command -v php)" ]; then
  echo -e "- PHP \e[1m\e[31mis not installed.\033[0m" >&2
  error=1
else
  echo -e "- PHP \e[1m\e[32mis installed.\033[0m" >&2
  php_path="$(which php)"
fi
check_php_curl=$($php_path -r "echo (function_exists('curl_version'))? 'enabled' : 'disabled';")
check_php_xml=$($php_path -r "echo (function_exists('simplexml_load_file'))? 'enabled' : 'disabled';")
check_php_pdo=$($php_path -r "echo (extension_loaded('pdo_sqlite'))? 'enabled' : 'disabled';")

if [[ "$check_php_curl" == "disabled" ]]; then
  echo -e "  |-> php-curl extension \e[1m\e[31mis not installed.\033[0m" >&2
  error=1
else
  echo -e "  |-> php-curl extension \e[1m\e[32mis installed.\033[0m" >&2
fi

if [[ "$check_php_xml" == "disabled" ]]; then
  echo -e "  |-> php-xml extension \e[1m\e[31mis not installed.\033[0m" >&2
  error=1
else
  echo -e "  |-> php-xml extension \e[1m\e[32mis installed.\033[0m" >&2
fi

if [[ "$check_php_pdo" == "disabled" ]]; then
  echo -e "  |-> php-sqlite3 extension \e[1m\e[31mis not installed.\033[0m" >&2
  error=1
else
  echo -e "  |-> php-sqlite3 extension \e[1m\e[32mis installed.\033[0m" >&2
fi

if ! [ -x "$(command -v curl)" ]; then
  echo -e "- CURL \e[1m\e[31mis not installed.\033[0m" >&2
  error=1
else
  echo -e "- CURL \e[1m\e[32mis installed.\033[0m" >&2
  curl_path="$(which curl)"
fi

if ! [ -x "$(command -v ffmpeg)" ]; then
  echo -e "- FFMPEG \e[1m\e[31mis not installed.\033[0m" >&2
  error=1
else
  echo -e "- FFMPEG \e[1m\e[32mis installed.\033[0m" >&2
  ffmpeg_path="$(which ffmpeg)"
fi

if (($error == 1)); then
  echo -e "\e[1m\e[31m--------------------------------------------------------------\033[0m"
  echo -e "\e[1m\e[31m---            Can't continue the installation             ---\033[0m"
  echo -e "\e[1m\e[31m--- Please make sure that all the packages are installed.  ---\033[0m"
  echo -e "\e[1m\e[31m--------------------------------------------------------------\033[0m"
  exit 1
fi

echo -e "\e[1m\e[32m--------------------------------------------------------------\033[0m"
echo -e "\e[1m\e[32m---        All packages and extensions are installed       ---"
echo -e "\e[1m\e[32m--------------------------------------------------------------\033[0m"
parentdir="$(dirname "$PWD")"
echo "The script now is going to create differents directories"
read -p "- Enter the username of machine default -> [podclient] : " podclient
podclient=${podclient:-podclient}
read -p "- Enter recorderdata path in '/var/www/' default -> [recorderdata] : " recorderdata_path
recorderdata_path=${recorderdata_path:-recorderdata}
mkdir "/var/www/$recorderdata_path/"
echo -e "- \e[1m$recorderdata_path\033[0m successfully created in              : \e[1m'/var/www/$recorderdata_path'\033[0m"
mkdir "/var/www/$recorderdata_path/movies/"
echo -e "- \e[1mmovies\033[0m directory successfully created in           : \e[1m'/var/www/$recorderdata_path/movies/'\033[0m"
mkdir "/var/www/$recorderdata_path/movies/local_processing/"
echo -e "- \e[1mlocal_processing\033[0m directory successfully created in : \e[1m'/var/www/$recorderdata_path/movies/local_processing/'\033[0m"
mkdir "/var/www/$recorderdata_path/movies/trash/"
echo -e "- \e[1mtrash\033[0m directory successfully created in            : \e[1m'/var/www/$recorderdata_path/movies/trash/'\033[0m"
mkdir "/var/www/$recorderdata_path/movies/upload_ok/"
echo -e "- \e[1mupload_ok\033[0m directory successfully created in        : \e[1m'/var/www/$recorderdata_path/movies/upload_ok/'\033[0m"
mkdir "/var/www/$recorderdata_path/movies/upload_to_server/"
echo -e "- \e[1mupload_to_server\033[0m directory successfully created in : \e[1m'/var/www/$recorderdata_path/movies/upload_to_server/'\033[0m"
mkdir "/var/www/$recorderdata_path/var/"
echo -e "- \e[1mvar directory\033[0m successfully created in              : \e[1m'/var/www/$recorderdata_path/var/'\033[0m"
mkdir "/var/www/$recorderdata_path/log/"
echo -e "- \e[1mlog directory\033[0m successfully created in              : \e[1m'/var/www/$recorderdata_path/log/'\033[0m"
mkdir "/var/www/$recorderdata_path/log/cmd/"
echo -e "- \e[1mcommand line log directory\033[0m successfully created in              : \e[1m'/var/www/$recorderdata_path/log/cmd/'\033[0m"
apache_user=$(ps -ef | egrep '(httpd|apache2|apache)' | grep -v `whoami` | grep -v root | head -n1 | awk '{print $1}')
echo -e "- Getting your apache user -> $apache_user"
printf "deny from all\nAllowOverride None\n" >> "/var/www/$recorderdata_path/.htaccess"
echo -e "- /var/www/$recorderdata_path/.htaccess successfully created"
chown -R $apache_user:$apache_user "/var/www/$recorderdata_path/"
echo -e "- chown -R $apache_user:$apache_user /var/www/$recorderdata_path/ done !"
chmod -R 0777 "/var/www/$recorderdata_path/"
echo -e "- chmod -R 0777 /var/www/$recorderdata_path/ done !"

adduser $apache_user video
usermod -a -G video $apache_user
echo -e "- Adding user $apache_user to video group done !"

adduser $apache_user audio
usermod -a -G audio $apache_user
echo -e "- Adding user $apache_user to sound group done !"

cp -r $parentdir/htdocs/ /var/www/html/ezrecorder
echo -e "- htdocs folder copied to /var/www/html/ezrecorder"

chown -R $podclient:$podclient $parentdir
echo -e "- Changing owner of $parentdir -> $podclient done !"

chown -R $podclient:$podclient /var/www/html/ezrecorder/
echo -e "- Changing owner of /var/www/html/ezrecorder -> $podclient done !"

echo "<?php chdir(\"$parentdir/\");" >> "/var/www/html/ezrecorder/config.php"
echo -e "- Creating config.php file in /var/www/html/ezrecorder/  done !"

cp $parentdir/etc/config/plugins.example.json $parentdir/etc/config/plugins.json
echo -e "- Creating plugins.json file  done !"

cp $parentdir/etc/config/recorder.example.json $parentdir/etc/config/recorder.json
echo -e "- Creating recorder.json file  done !"

echo -e "\e[1m\e[32m--------------------------------------------------------------\033[0m"
echo -e "\e[1m\e[32m---     All directories has been successfully created      ---"
echo -e "\e[1m\e[32m--------------------------------------------------------------\033[0m"

echo "The script now is going to create recorder configuration file"
