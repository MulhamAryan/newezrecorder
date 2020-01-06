<?php
    if(!empty($recordingInfo["start_time"])) {
        $recordingInfo["stop_time"] = explode(":", $recordingInfo["stop_time"]);
        $converted = ((int)$recordingInfo["stop_time"][0] * 60 * 60) + ((int)$recordingInfo["stop_time"][1] * 60);
        $startedConverted = date('H:i:s', $recordingInfo["start_time"] + $converted);
        $converted = $startedConverted;
    }
    else{
        $startedConverted = $converted;
    }
    ?>

<script>
    function stop_recording(fnct){
        if(window.confirm("<?php echo $lang["stop_recording_message"];?>")) {
            $.ajax({
                type: 'GET',
                url: "ajax.php?action=recording&status=" + fnct,
                cache: false,
                timeout: 10000,
                error: function(){
                    alert("<?php echo $lang["execution_failed"];?>");
                },
                success: function () {
                    $("#recordingNow").fadeOut();
                    $("#recordingPublish").fadeIn();
                }
            });
        }
    }
    $(document).ready(function (){
        $("#camposition").click(function (){
            $("#campresets").fadeIn();
        });
    });
</script>
<?php

    if($recordingstatus == "stop")
        $hideRecordingScreen = "display:none;";
    else
        $displayPublishOptions = "display:none;";

?>
<div class="recorder">
    <div class="indiv" style="text-align: center">
        <div id="finalized" style="display: none">
            <hr>
            <div id="deleted_record" style="display: none">
                <?php
                    echo $lang["record_deleted"];
                ?>
            </div>
            <div id="published_in_private" style="display: none">
                <?php
                echo $lang["published_in_private"];
                ?>
            </div>
            <div id="published_in_public" style="display: none">
                <?php
                echo $lang["published_in_public"];
                ?>
            </div>
            <a href="index.php"><? echo $lang["start_new"];?></a>
            <hr>
        </div>
        <div id="recordingPublish" style="<?php echo $displayPublishOptions;?> padding: 20px;">
            <b><i class="fas fa-share-square"></i> <?php echo $lang["where_publish"];?></b>
            <hr>
            <div class="publish publishDelete" onclick="if(confirm('<?php echo $lang["confirm_delete_record"];?>')) publishRecord('trash');"><i class="fas fa-times-circle"></i><br><?php echo $lang["delete_record"]; ?></div>
            <div class="publish publishPrivate" onclick="publishRecord('private');"><i class="fas fa-user-shield"></i><br><?php echo $lang["publish_in_private"]; ?></div>
            <div class="publish publishPublic" onclick="publishRecord('public');"><i class="fas fa-user-friends"></i><br><?php echo $lang["publish_in_public"]; ?></div>
        </div>
        <div id="recordingNow" style="<?php echo $hideRecordingScreen;?>">
            <?php
                $recorderNum = count($recorderInfo);
                if($recorderNum != 1){
                    $class = "float-left";
                }
                foreach ($recorderInfo as $recorderInfoKey => $recorderInfoValue){
                    ?>
                    <div class="<?php echo $class;?> player">
                        <h5><i class="fas fa-<?php echo $recorderInfoValue["icon"];?>"></i> <?php echo $recorderInfoValue["tempname"];?></h5>
                        <hr>
                        <video id="video<?php echo $recorderNum;?>" width="100%" height="100%" muted autoplay="" ></video>
                        <div class="view-metter"></div>
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
                    elseif($recordingstatus == "play" || $recordingstatus == "resume"){
                        $start = 'style="display:none;"';
                    }
                    if($autostop == 1){
                        echo '<span id="autostop_before" '.$start.'>';
                        echo $lang["auto_stop_actived"] . ' ' . $lang["auto_stop_to"] . '<span style="color:#FF0000; font-weight: bold;" id="counter">' . $hour . 'h ' . $lang["and"] . ' ' . $minute .'m</span> ' .$lang["and"]. ' ' . $lang["publish_in"] . ' <b>' . $publishalbum . '</b>';
                        echo '</span>';

                        echo '<span id="autostop_before" '.$pause.'>';
                        echo $lang["auto_stop_actived"] . ' ' . $lang["auto_stop_to"] . '<span style="color:#FF0000; font-weight: bold;" id="counter2">' . $converted .'</span> ' .$lang["and"]. ' ' . $lang["publish_in"] . ' <b>' . $publishalbum . '</b>';
                        echo '</span><hr>';

                    }
                    ?>
                <div <?php echo $start;?> class="recordingbutton" id="play" onclick="recordStatus('play');"><i class="fas fa-play-circle"></i><br><?php echo $lang["start_recording"];?></div>
                <div <?php echo $pause;?> class="recordingbutton" id="pause" onclick="recordStatus('pause');"><i class="fas fa-pause-circle"></i><br><?php echo $lang["pause_recording"];?></div>
                <div class="recordingbutton" id="stop" onclick="stop_recording('stop');"><i class="fas fa-stop-circle"></i><br><?php echo $lang["stop_recording"];?></div>
                <div class="recordingbutton" id="camposition" rel="<?php echo $asset;?>"><i class="fas fa-arrows-alt"></i><br><?php echo $lang["cam_position"];?></div>
            </div>
            <div id="campresets" style="display: none">test</div>
        </div>
    </div>
</div>