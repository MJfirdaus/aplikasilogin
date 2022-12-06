<?php
session_start();

// hapus session
$_SESSION = [];
session_unset();
session_destroy();

// hapus cookie
setcookie("id", "", time() - 3600); // cara menghapus cookie = setcookie("nama cookie-nya samakan", "kosongkan", time() - 3600 setting waktu minus)
setcookie("key", "", time() - 3600);

header("Location: login.php");
exit;

?>