<div class="recorder">
    <div class="form">
        <div class="loadingRecording" id="loadingRecording">
            <?php echo $lang["loading_recording"]; ?> <br><br>
            <div class="progress">
                <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
            </div>


        </div>
        <form method="post" action="?action=init_recording" id="initRecorder">
            <div class="form-group">
                <label for="course"><?php echo $lang["course"];?></label>
                    <?php
                    if (!isset($coursesList) || empty($coursesList))
                        echo $lang["no_courses_found"];
                    else {?>
                        <select name="course" id="course" class="form-control">
                            <?php
                            foreach ($coursesList as $courseListKey => $courseListValue) {
                                ?>
                                <option value="<?php echo $courseListKey; ?>" <?php echo $tmp->isSelected($courseListKey,$lastCourse); ?>><?php echo $courseListValue; ?></option>
                                <?php } ?>
                        </select>
                        <?php
                    }
                    ?>
                </div>
                <hr>
                <div class="form-group">
                    <label for="title"><?php echo $lang["title"];?> : </label>
                    <input type="text" name="title" id="title" maxlength="70" value="<?php echo $lastTitle; ?>" placeholder="<?php echo $lang["title"];?>" required>
                </div>
                <hr>
                <div class="form-group">
                    <label for="description"><?php echo $lang["description"];?> : </label>
                    <textarea id="description" placeholder="<?php echo $lang["description"];?>" name="description"><?php echo $lastDescription;?></textarea>
                </div>
                <hr>
                <div class="form-group">
                    <?php echo $lang["select_type"];?> :
                    <br><br>
                    <div class="customRadio">
                        <?php $i = 1; if($disableFullList == 0){?><input type="radio" name="recorder" value="all" id="r<?php echo $i;?>" <?php echo $tmp->isChecked($lastRecorder,"all");?> /><label class="radio" for="r<?php echo $i;?>"><i class="fas fa-photo-video"></i><br>Camera + Slide</label>
                            <?php $i++; }
                        foreach ($recorder_modules as $recorderKey => $recorderValue){
                            if($recorderValue["enabled"] == true) {
                                ?>
                                <input type="radio" name="recorder" value="<?php echo $recorderValue["module"]; ?>" id="r<?php echo $i; ?>" <?php echo $tmp->isChecked($lastRecorder,$recorderValue["module"]);?>/>
                                <label class="radio" for="r<?php echo $i; ?>"><i class="fas fa-<?php echo $recorderValue["icon"]; ?>"></i><br><?php echo $recorderValue["tempname"]; ?></label>
                                <?php
                            }
                            $i++;
                        }
                        ?>
                    </div>
                    <hr>
                    <label class="switch">
                        <input type="checkbox" name="advancedoptions" id="autostop" value="1" <?php echo $tmp->isChecked($lastAdvancedOptions,1);?> />
                        <span class="slider round"></span>
                    </label>
                    <label for="autostop"><?php echo $lang["options"];?> :</label>
                    <div class="recordoptions">
                        <div class="float-left"> <?php echo $lang["auto_stop_after"]; ?> <input type="time" name="autostop" value="<?php echo $lastAutoStopTime;?>" max="12:00" min="00:30" id="stoptime" style="padding: 0 !important;" required> hh:mm</div>
                        <div class="float-right">
                            <?php echo $lang["publish_in"];?> :
                            <label for="privatealbum" class="privatealbum"><input type="radio" name="publishin" value="1" id="privatealbum" <?php echo $tmp->isChecked($lastAutoPublishIn,1) ?> required/> <?php echo $lang["private_album"];?> </label>
                            <label for="publicalbum" class="publicalbum"><input type="radio" name="publishin" value="2" id="publicalbum" <?php echo $tmp->isChecked($lastAutoPublishIn,2) ?> required/> <?php echo $lang["public_album"];?> </label>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="float-right">
                    <input type="submit" name="init_record" value="<?php echo $lang["continue"];?>" class="btn btn-success">
                    <input type="reset" name="cancel" value="<?php echo $lang["cancel"];?>" class="btn btn-secondary" onclick="location.href='?action=logout';">
                </div>
                <div class="float-left">

                        <label class="switch">
                            <input type="checkbox" id="streaming" name="streaming" value="1">
                            <span class="slider round"></span>
                        </label>
                        <label for="streaming"><?php echo $lang["enable_streaming"];?></label>

                </div>
                <div class="clear"></div>
            <div class="clearfix"></div>
        </form>
    </div>
</div>
