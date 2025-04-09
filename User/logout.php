<?php
session_start();
session_destroy();
header("Location: ../Layout/app.php");
exit();
?>