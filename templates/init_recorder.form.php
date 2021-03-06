<?php
    $camcontroller = ($plugin["camcontrollers"] != null ? true:false);
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

</script>
<?php
    if($recordingstatus == "stop"){
        $hideRecordingScreen = "display:none;";
    }
    else{
        $displayPublishOptions = "display:none;";
    }
    $hideRecordingScreen = isset($hideRecordingScreen) ? $hideRecordingScreen:"";
    $displayPublishOptions = isset($displayPublishOptions) ? $displayPublishOptions:"";
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
            <a href="index.php"><?php echo $lang["start_new"];?></a>
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
            <div class="clearfix"></div>
            <?php echo $plugin["player"]->player($recorderInfo); ?>
            <div class="clearfix"></div>
            <hr>
            <div class="controller">
                <?php
                    if($recordingstatus == "init" || $recordingstatus == "pause"){
                        $pause = 'style="display:none;"';
                    }
                    elseif($recordingstatus == "play" || $recordingstatus == "resume"){
                        $start = 'style="display:none;"';
                    }
                $start = (!empty($start) ? $start:"");
                $pause = (!empty($pause) ? $pause:"");
                    if($autostop == 1){
                        echo '<span id="autostop_before" '.$start.'>';
                        echo $lang["auto_stop_actived"] . ' ' . $lang["auto_stop_to"] . '<span style="color:#FF0000; font-weight: bold;" id="counter">' . $hour . 'h ' . $lang["and"] . ' ' . $minute .'m</span> ' .$lang["and"]. ' ' . $lang["publish_in"] . ' <b>' . $publishalbum . '</b>';
                        echo '</span>';

                        echo '<span id="autostop_before" '.$pause.'>';
                        echo $lang["auto_stop_actived"] . ' ' . $lang["auto_stop_to"] . '<span style="color:#FF0000; font-weight: bold;" id="counter2">' . $converted .'</span> ' .$lang["and"]. ' ' . $lang["publish_in"] . ' <b>' . $publishalbum . '</b>';
                        echo '</span><hr>';

                    }
                    ?>
                <?php echo $plugin["player"]->javascript($recorderInfo); ?>
                <div <?php echo $start;?> class="recordingbutton" id="play" onclick="recordStatus('play');"><i class="fas fa-play-circle"></i><br><?php echo $lang["start_recording"];?></div>
                <div <?php echo $pause;?> class="recordingbutton" id="pause" onclick="recordStatus('pause');"><i class="fas fa-pause-circle"></i><br><?php echo $lang["pause_recording"];?></div>
                <div class="recordingbutton" id="stop" onclick="stop_recording('stop');"><i class="fas fa-stop-circle"></i><br><?php echo $lang["stop_recording"];?></div>
                <?php if($camcontroller == true){ ?><div class="recordingbutton" id="camposition" rel="<?php echo $asset;?>"><i class="fas fa-arrows-alt"></i><br><?php echo $lang["cam_position"];?></div><?php } ?>
            </div>
            <?php if($camcontroller == true){ ?>
                    <div id="campresets" class="campresets" style="display: none;">
                        <ul>
                        <?php
                            foreach ($plugin["camcontrollers"]->positionNamesGet() as $scene){
                                echo '<li onclick="changeCamPosition(\'' . $scene .'\');">';
                                echo "<div style=\"background-image: url('" . $config["curenttheme"] ."/img/cam_position/" . $scene . ".png');\"></div>";
                                echo '<span>' . $scene .'</span></li>';
                            }
                        ?>
                        </ul>
                </div>
            <?php } ?>
        </div>
    </div>
</div>