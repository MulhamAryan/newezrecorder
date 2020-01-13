<?php

include_once "config.inc";
require_once 'ptzoptics_cgi.php';

class PtzController extends PTZOptics_CGI_API
{

    function __construct()
    {
        global $module_parameters;
        parent::__construct($module_parameters["ip"]);
    }

    function positionNamesGet()
    {
        $presets = $this->getPresets();
        if ($presets)
            return array_values($presets);
        else
            return array();
    }

    function positionSave($name)
    {
        $last_id = $this->getLastUsedPresetId();
        if ($last_id >= PTZOptics_CGI_API::PRESET_MAX)
            return -1;

        $new_preset_id = $last_id + 1;
        $presets = $this->getPresets();

        //override already existing key
        if ($presets) {
            $existing_key = array_search($name, $presets);
            if ($existing_key !== false)
                $new_preset_id = $existing_key; //in this case
        } else {
            $presets = array();
        }

        //save in camera
        $this->preset_save($new_preset_id);

        //save in our local list
        $presets[$new_preset_id] = $name;
        $this->setPresets($presets);

        return 0;
    }

    function positionDelete($name)
    {
        $presets = $this->getPresets();
        if (!$presets)
            return false;
        $existing_key = array_search($name, $presets);
        if ($existing_key === false)
            return false;

        unset($presets[$existing_key]);
        $this->setPresets($presets);
        return true;
    }

    function positionMove($name)
    {
        $presets = $this->getPresets();
        if (!$presets)
            return false;

        $existing_key = array_search($name, $presets);
        if ($existing_key === false)
            return false;

        $this->preset_go_to($existing_key);
        return true;
    }

    function getLastUsedPresetId()
    {
        $presets = $this->getPresets();
        if (!$presets)
            return 0;
        return max(array_keys($presets));
    }

    function getPresets()
    {
        global $module_parameters;
        if (file_exists($module_parameters["preset_file"])) {
            $string_data = file_get_contents($module_parameters["preset_file"]);
            return unserialize($string_data);
        }
        else
            return "Preset file doesn't exists";
    }

    function setPresets($presets = array())
    {
        global $module_parameters;

        $string_data = serialize($presets);
        file_put_contents($module_parameters["preset_file"], $string_data);
    }

}

$CamController = new PtzController();

return $CamController;

?>