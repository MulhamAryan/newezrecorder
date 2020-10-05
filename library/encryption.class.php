<?php

    class Encryption{
        function __construct()
        {
            global $config;
            $this->recorder_public_key  = $config["basedir"] . "/etc/keys/ezrecorder_pub.pem";
            $this->recorder_private_key = $config["basedir"] . "/etc/keys/ezrecorder_priv.pem";
            $this->ezcast_public_key    = "";
            $this->ezcast_private_key   = $config["basedir"] . "/etc/keys/ezcast_priv.pem";
        }

        public function localDecrypt($encryptString = '',$keyFile = null)
        {
            if(empty($keyFile)){
                $keyFile = $this->recorder_private_key;
            }

            $decrypted = '';
            $privateKey = (string) file_get_contents($keyFile);
            openssl_private_decrypt(base64_decode($encryptString), $decrypted, $privateKey);
            $answer = @unserialize($decrypted);
            if($answer !== false){
                return $answer;
            }
            else{
                return $decrypted;
            }
        }

        public function remoteDecrypt($encryptString = ''){
            $decrypted = $this->localDecrypt($encryptString,$this->ezcast_private_key);
            return $decrypted;
        }

        public function encrypt($data = '')
        {
            $publicKey = (string) file_get_contents($this->recorder_public_key);
            openssl_public_encrypt($data, $encrypt_data, $publicKey);
            $encrypt_data = base64_encode($encrypt_data);
            return $encrypt_data;
        }

    }