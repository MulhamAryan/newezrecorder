<div class="login">
    <form method="post" action="?action=login">
        <div class="fields">
            <div class="divlogo">
                <span class="ez">EZ</span>
                <span class="recorder">recorder</span>
            </div>
            <hr>
            <?php
                if(!empty($errorMsg)){
                    echo $tmp->error($errorMsg);
                }
                ?>
            <div class="passwordForm" id="passwordForm">
                <button type="button" class="close" aria-label="Close" id="closePassForm">
                    <span aria-hidden="true">&times;</span>
                </button>
                <br>
                <?php echo $lang["forgot_password_text"]; ?>
                <hr>
            </div>
            <div id="loginForm">
                <div class="form-group">
                    <label for="userlogin"><i class="fas fa-user"></i> <?php echo $lang["netid"];?> : </label>
                    <input type="text" name="usernetid" autofocus="false" autocapitalize="off" autocorrect="off" tabindex="1" id="userlogin" placeholder="<?php echo $lang["netid"];?>">
                </div>
                <div class="form-group">
                    <label for="userpassword"><i class="fas fa-lock"></i> <?php echo $lang["password"];?> : </label>
                    <input type="password" name="userpassword" autofocus="false"  autocapitalize="off" autocorrect="off" tabindex="2" id="userpassword" placeholder="<?php echo $lang["password"];?>">
                </div>
                <input type="submit" name="userlogin" value="<?php echo $lang["login"];?>">
                <br><br>
                <div class="float-left">
                    <i class="fas fa-globe-europe"></i>
                    <?php
                        foreach ($languagesList as $langListKey => $langListValue){
                            if($langListValue["enabled"] == true)
                                echo '| <a href="?action=language&select=' . $langListKey . '">' . $langListValue["name"] . ' </a>';
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