<script>
    $(document).ready(function () {
        $("#userlogin").submit(function (event) {
            var public_key = "<?php echo $public_key_content; ?>";
            // Encrypt with the public key...
            var encrypt = new JSEncrypt();
            encrypt.setPublicKey(public_key);
            var answer  = "";
            var message = $("#message");
            var username = $("#username").val();
            var password = $("#userpassword").val();
            var encryptedUsername = encrypt.encrypt(username);
            var encryptedPassword = encrypt.encrypt(password);

            answer = '<div class="alert alert-warning" id="wait"><div class="spinner-border text-secondary" style="vertical-align: middle" role="status"></div><span style="vertical-align: middle"><?php echo $lang["please_wait"]; ?></span></div>';
            message.html(answer);
            $.ajax({
                url: 'ajax.php?action=login',
                type: 'POST',
                cache: false,
                data: {username:encryptedUsername,password:encryptedPassword},
                dataType:"json",
                success: function (data = []) {
                    console.log(data);
                    if(data["success"] === 1) {
                        answer = '<div class="alert alert-success"><?php echo $lang["login_success"];?></div>';
                        window.location.href = "index.php";
                    }
                    else{
                        answer = '<div class="alert alert-danger">' + data["errorMsg"] + '</div>';
                    }
                    message.html(answer);
                },
                error: function(){
                    message.html('<div class="alert alert-danger"><?php echo $lang["check_internet_connection"];?></div>');
                }
            });
            return false;
        });
    });
</script>
<div class="login">
    <form action="" id="userlogin">
        <div class="fields">
            <div class="divlogo">
                <img src="<?php echo $config["curenttheme"] . '/img/ezrecorder.png';?>" style="width: 80%">
            </div>
            <hr>
            <?php
                $checkLock = (empty($checkLock) ? "":$checkLock);
                if(!empty($errorMsg)){
                    echo $tmp->error($errorMsg);
                }
                elseif($checkLock != false){?>
                    <script type="text/javascript">
                        var res = false;
                        res = window.confirm("<?php echo $lang["recorder_in_use"];?> \n <?php echo $lang["author"];?> : <?php echo $current_user; ?> \n <?php echo $lang["course"];?> : <?php echo $course; ?> \n <?php echo $lang["date_hour"];?> : <?php echo $start_time; ?>");
                        if(res) {
                            window.location = '?action=recording_force_quit';
                        }
                        else {
                            window.location = '?action=login';
                        }
                    </script>
            <?php } ?>
            <div class="passwordForm" id="passwordForm">
                <button type="button" class="close" aria-label="Close" id="closePassForm">
                    <span aria-hidden="true">&times;</span>
                </button>
                <br>
                <?php echo $lang["forgot_password_text"]; ?>
                <hr>
            </div>
            <div id="loginForm">
                <div id="message"></div>
                <div class="form-group">
                    <label for="userlogin"><i class="fas fa-user"></i> <?php echo $lang["netid"];?> : </label>
                    <input type="text" name="usernetid" autofocus="false" autocapitalize="off" autocorrect="off" tabindex="1" id="username" placeholder="<?php echo $lang["netid"];?>">
                </div>
                <div class="form-group">
                    <label for="userpassword"><i class="fas fa-lock"></i> <?php echo $lang["password"];?> : </label>
                    <input type="password" name="userpassword" autofocus="false"  autocapitalize="off" autocorrect="off" tabindex="2" id="userpassword" placeholder="<?php echo $lang["password"];?>">
                </div>
                <input type="submit" name="userlogin" value="<?php echo $lang["login"];?>" class="btn btn-success" id="userlogin">
                <br><br>
                <div class="float-left">
                    <i class="fas fa-globe-europe"></i>
                    <?php
                        foreach ($languagesList as $langListKey => $langListValue){
                            if($langListValue["enabled"] == true)
                                echo '| <a href="?language=' . $langListKey . '">' . $langListValue["name"] . ' </a>';
                        }
                    ?>
                </div>
                <div class="float-right">
                    <i class="far fa-life-ring"></i> <a href="?action=help" target="_blank"><?php echo $lang["help"];?> ? </a>
                </div>
            </div>
            <br>
            <hr>
        </div>
        <a href="#forgot_password" id="forgot_password">
            <div class="forgot_password"><?php echo $lang["forgot_password"];?></div>
        </a>

    </form>
</div>
