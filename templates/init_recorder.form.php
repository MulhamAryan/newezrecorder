<?php
$video_tag1 = "<video id=\"video\" width=\"100%\" height=\"100%\" muted autoplay=\"\" style=\"border:1px solid #fff;\"></video>";
$video_tag2 = "<video id=\"video2\" width=\"100%\" height=\"100%\" muted autoplay=\"\" style=\"border:1px solid #fff;\"></video>";
?>
<div class="recorder">
    <div class="indiv" >
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
                echo 'var url'.$recorderNumUrl.' = "http://localhost/newezrecorder/m3u8.php?asset='.$asset.'&recorder='.$recorderInfoUrlValue["module"].'&type=hd";' . PHP_EOL;
                echo 'playM3u8(url'.$recorderNumUrl.',"video'.$recorderNumUrl.'");' . PHP_EOL;
                $recorderNumUrl--;
            }
        ?>

    </script>
        <hr>
        <div class="controller">
            <div class="recordingbutton" id="startrecording" onclick="recordStatus('start','<?php echo $asset;?>');"><i class="fas fa-play-circle"></i><?php echo $lang["start_recording"];?></div>
            <div class="recordingbutton" id="pauserecording" onclick="recordStatus('pause','<?php echo $asset;?>');"><i class="fas fa-pause-circle"></i><?php echo $lang["pause_recording"];?></div>
            <div class="recordingbutton" id="stoprecording" onclick="recordStatus('stop','<?php echo $asset;?>');"><i class="fas fa-stop-circle"></i><?php echo $lang["stop_recording"];?></div>
            <div class="recordingbutton" id="camposition" rel="<?php echo $asset;?>"><i class="fas fa-arrows-alt"></i><?php echo $lang["cam_position"];?></div>
        </div>
    </div>
</div>