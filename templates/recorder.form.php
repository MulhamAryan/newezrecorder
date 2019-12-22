<div class="recorder">
    <div class="indiv">
        <div class="loadingRecording" id="loadingRecording">
            <div class="progress">
                <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="75" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div>
            </div>
            <br>
            <?php echo $lang["loading_recording"]; ?>
        </div>
        <form method="post" action="?action=init_recording" id="initRecorder">
            <div class="form-group">
                <label for="course"><i class="fab fa-discourse"></i> Cours</label>
                <?php
                    if (!isset($coursesList) || empty($coursesList)) {
                        echo $lang["no_courses_found"];
                    }
                    else {
                        ?>
                <select name="course" id="course" class="form-control">
                        <?php
                        foreach ($coursesList as $listKey => $listValue) {
                            ?>
                            <option value="<?php echo $listKey; ?>"><?php echo $listValue; ?></option>
                            <?php
                        }
                        ?>
                </select>
                    <?php
                    }
                ?>
            </div>
            <hr>
            <div class="form-group">
                <label for="title"><i class="fas fa-heading"></i> <?php echo $lang["title"];?> : </label>
                <input type="text" name="title" id="title" maxlength="70" value="<?php echo $prefill_title; ?>" placeholder="<?php echo $lang["title"];?>" required>
            </div>
            <hr>
            <div class="form-group">
                <label for="description"><i class="fas fa-align-left"></i> <?php echo $lang["description"];?> : </label>
                <textarea id="description" placeholder="<?php echo $lang["description"];?>" name="description"></textarea>
            </div>
            <hr>
            <div class="form-group">
                <i class="fas fa-record-vinyl"></i>
                <?php echo $lang["select_type"];?> :
                <br><br>
                <div class="customRadio">
                    <?php
                    $i = 1;
                        if($disableFullList == 0){
                            ?>

                            <input type="radio" name="recorder" value="all" id="r<?php echo $i;?>" checked/>
                            <label class="radio" for="r<?php echo $i;?>"><i class="fas fa-photo-video"></i><br>Camera + Slide</label>

                            <?php
                            $i++;
                        }
                        foreach ($recorder_modules as $recorderKey => $recorderValue){
                            if($recorderValue["enabled"] == true) {
                                ?>
                                <input type="radio" name="recorder" value="<?php echo $recorderValue["module"]; ?>" id="r<?php echo $i; ?>"/>
                                <label class="radio" for="r<?php echo $i; ?>"><i class="fas fa-<?php echo $recorderValue["icon"]; ?>"></i><br><?php echo $recorderValue["tempname"]; ?></label>
                                <?php
                            }
                            $i++;
                        }
                    ?>
                </div>
                <hr>
                <label for="autostop"><input type="checkbox" name="advancedoptions" id="autostop" value="1"> Options :</label>
                <div class="recordoptions" id="recordoptions">
                    <?php echo $lang["auto_stop_after"]; ?><input type="time" name="autostop" value="02:00" max="12:00" id="stoptime" required>
                    <?php echo $lang["publish_in"];?> :
                    <label for="publicalbum"><input type="radio" name="publishin" value="1" id="publicalbum" checked> <?php echo $lang["private_album"];?> </label> |
                    <label for="privatealbum"><input type="radio" name="publishin" value="2" id="privatealbum"> <?php echo $lang["public_album"];?> </label>
                </div>

            </div>
            <hr>
            <div class="float-right">
                <input type="submit" name="init_record" value="<?php echo $lang["continue"];?>" class="btn btn-success">
                <input type="reset" name="cancel" value="<?php echo $lang["cancel"];?>" class="btn btn-secondary" onclick="location.href='?action=logout';">
            </div>
            <div class="float-left">
                <label for="streaming"><input type="checkbox" id="streaming" name="streaming" value="1"> <?php echo $lang["enable_streaming"];?></label>
            </div>
            <div class="clear"></div>
        </form>
    </div>
</div>