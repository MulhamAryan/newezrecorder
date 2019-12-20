<script>
    function stop_recording(fnct,asset){
        if(window.confirm("<?php echo $lang["stop_recording_message"];?>")) {
            $.ajax({
                type: 'GET',
                url: "ajax.php?action=recording&status=" + fnct,
                cache: false,
                timeout: 10000,
                error: function(){
                    alert("Warning: This action could not be executed.\n\nVerify that you are still connected to PODC and refresh the page in your web browser (ctrl+R / cmd+R) before retrying.");
                },
                success: function () {
                    $("#recordingNow").fadeOut();
                    $("#recordingPublish").fadeIn();
                }
            });
        }
    }
</script>
<?php
    if($recordingstatus == "stop") {
        $hideRecordingScreen = "display:none;";
    }
    else{
        $displayPublishOptions = "display:none;";
    }
?>
<div class="recorder">
    <div class="indiv" style="text-align: center">
        <div id="recordingPublish" style="<?php echo $displayPublishOptions;?>">
            <b><i class="fas fa-share-square"></i> <?php echo $lang["where_publish"];?></b>
            <hr>
            <div class="publish publishDelete"><i class="fas fa-times-circle"></i><br><?php echo $lang["delete_record"]; ?></div>
            <div class="publish publishPrivate"><i class="fas fa-user-shield"></i><br><?php echo $lang["publish_in_private"]; ?></div>
            <div class="publish publishPublic"><i class="fas fa-user-friends"></i><br><?php echo $lang["publish_in_public"]; ?></div>
        </div>
        <div id="recordingNow" style="<?php echo $hideRecordingScreen;?>">
            <?php
            $recorderNum = count($recorderInfo);
            if($recorderNum != 1){
                $class = "float-left";
            }
            foreach ($recorderInfo as $recorderInfoKey => $recorderInfoValue){
                ?>
                <div class="<?php echo $class;?>" style="width: 50%; margin: auto; text-align: center">
                    <b><i class="fas fa-<?php echo $recorderInfoValue["icon"];?>"></i> <?php echo $recorderInfoValue["tempname"];?></b>
                    <hr>
                    <video id="video<?php echo $recorderNum;?>" width="100%" height="100%" muted autoplay="" style="border:1px solid #fff"></video>
                </div>
            <?php
                $recorderNum--;
            }
            ?>
            <div class="clearfix"></div>
            <script src="<?php echo $config["curenttheme"];?>/js/player/player.js"></script>
            <script>
                <?php
                    $recorderNumUrl = count($recorderInfo);
                    foreach ($recorderInfo as $recorderInfoUrlKey => $recorderInfoUrlValue){
                        echo 'var url'.$recorderNumUrl.' = "'.$config["recorderurl"].'/m3u8.php?asset='.$asset.'&recorder='.$recorderInfoUrlValue["module"].'&type=hd";' . PHP_EOL;
                        echo 'playM3u8(url'.$recorderNumUrl.',"video'.$recorderNumUrl.'");' . PHP_EOL;
                        $recorderNumUrl--;
                    }
                ?>
            </script>
            <hr>
            <div class="controller">

                <?php
                    if($recordingstatus == "init" || $recordingstatus == "pause"){
                        $pause = 'style="display:none;"';
                    }
                    elseif($recordingstatus == "play"){
                        $start = 'style="display:none;"';
                    }
                    if($autostop == 1){
                        echo '<span id="autostop_before" '.$start.'>';
                        echo $lang["auto_stop_actived"] . ' ' . $lang["auto_stop_at"] . '<span style="color:#FF0000; font-weight: bold;">' . $hour . 'h ' . $lang["and"] . ' ' . $minute .'m</span> ' .$lang["and"]. ' ' . $lang["publish_in"] . ' <b>' . $publishalbum . '</b>';
                        echo '</span>';

                        echo '<span id="autostop_before" '.$pause.'>';
                        echo $lang["auto_stop_actived"] . ' ' . $lang["auto_stop_at"] . '<span style="color:#FF0000; font-weight: bold;">' . $converted .'</span> ' .$lang["and"]. ' ' . $lang["publish_in"] . ' <b>' . $publishalbum . '</b>';
                        echo '</span><hr>';

                    }
                    ?>
                <div <?php echo $start;?> class="recordingbutton" id="play" onclick="recordStatus('play','<?php echo $asset;?>');"><i class="fas fa-play-circle"></i><?php echo $lang["start_recording"];?></div>
                <div <?php echo $pause;?> class="recordingbutton" id="pause" onclick="recordStatus('pause','<?php echo $asset;?>');"><i class="fas fa-pause-circle"></i><?php echo $lang["pause_recording"];?></div>
                <div class="recordingbutton" id="stop" onclick="stop_recording('stop','<?php echo $asset;?>');"><i class="fas fa-stop-circle"></i><?php echo $lang["stop_recording"];?></div>
                <div class="recordingbutton" id="camposition" rel="<?php echo $asset;?>"><i class="fas fa-arrows-alt"></i><?php echo $lang["cam_position"];?></div>
            </div>
        </div>
    </div>
</div>