<?php
    unset($_SESSION);
    session_destroy();
    header("LOCATION:?action=login");
?>