<?php

    class PluginLoader{
        function __construct($plugins_list = array())
        {
            global $config;

            $this->enabled_plugin = array();
            $this->active_plugin_dir = array();
            $this->paramaters = "parameters.json";

            foreach ($plugins_list as $plKey => $plValue){
                if(is_array($plValue)){
                    $countActive = 0;
                    foreach ($plValue as $activeKey => $activeValue){
                        if($activeValue == 1) {
                            $countActive++;
                            $this->enabled_plugin[$plKey][$activeKey] = true;
                        }
                        if($countActive > 1){
                            echo "Error <b>$countActive</b> plugins in <b>$plKey</b> Found active please enable only one in `<b>". $config["basedir"] . "global_config.inc</b>` file<br>";
                            exit();
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

    }

    $pluginloader = new PluginLoader($plugins_list);

    foreach ($pluginloader->getActivePlugin() as $activePluginKey => $activePluginValue){
        if(file_exists($activePluginValue)){
            $module[$activePluginKey] = include($activePluginValue);
        }
        else{
            echo "Plugin library file not found `<b>$activePluginValue</b>`";
            exit();
        }
    }
?>