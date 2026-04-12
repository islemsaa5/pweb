<?php
// Fix logout page to match clean style
session_start();
$_SESSION = [];
session_destroy();
header("Location: index.php");
exit;
?>
