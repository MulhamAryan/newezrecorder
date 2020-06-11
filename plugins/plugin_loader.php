<?php

    class PluginLoader{
        function __construct($plugins_list = array())
        {
            global $config;

            $this->enabled_plugin = array();
            $this->active_plugin_dir = array();
            $this->paramaters = "parameters.json";

            foreach ($plugins_list as $plValue){
                if(is_array($plValue)) {
                    $countActive = 0;
                    foreach ($plValue as $activeKey => $activeValue) {
                        foreach ($activeValue as $pluginKey => $pluginValue) {
                            if($pluginValue == true) {
                                $countActive++;
                                $this->enabled_plugin[$activeKey][$pluginKey] = true;
                            }
                            if($countActive > 1){
                                echo "Error <b>$countActive</b> plugins in <b>$activeKey</b> active found, please enable only one in `<b>" . $config["listplugins"] . "</b>`";
                                echo "<pre>";
                                var_dump($this->enabled_plugin[$activeKey]);
                                echo "</pre>";
                                exit();
                            }
                        }
                    }
                }
            }
        }
        function getActivePlugin(){
            foreach ($this->enabled_plugin as $enabledPluginKey => $enabledPluginValue){
                foreach ($enabledPluginValue as $epvk => $epvv){
                    $parameterFile = __DIR__ . "/" . $enabledPluginKey . "/" . $epvk . "/" . $this->paramaters;
                    if(file_exists($parameterFile)){
                        $paramJson = json_decode(file_get_contents($parameterFile),true);
                        $this->active_plugin_dir[$paramJson["plugin_type"]] = __DIR__ . "/" . $enabledPluginKey . "/" . $epvk . "/" . $paramJson["library_file"];
                    }
                    else{
                        echo "Plugin $this->paramaters file not found in `<b>" . __DIR__ . "/$enabledPluginKey/$epvk/$this->paramaters`";
                        exit();
                    }
                }
            }
            return $this->active_plugin_dir;
        }
        public function getIsActive($plugintype,$pluginname){
            if(@$this->enabled_plugin[$plugintype][$pluginname])
                return true;
            else
                return false;
        }
    }

    $pluginloader = new PluginLoader($plugins_list);
    foreach ($pluginloader->getActivePlugin() as $activePluginKey => $activePluginValue){
        if(file_exists($activePluginValue)){
            $plugin[$activePluginKey] = include($activePluginValue);
        }
        else{
            echo "Plugin library file not found `<b>$activePluginValue</b>`";
            exit();
        }
    }