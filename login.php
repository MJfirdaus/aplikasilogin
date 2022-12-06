<?php
session_start(); // untuk memulai super global $_SESSION
require 'functions.php';

// cek cookie-nya terlebih dahulu
if( isset($_COOKIE["id"]) && isset($_COOKIE["key"]) ) {

    // ambil datanya terlebih dahulu
    $id = $_COOKIE["id"];
    $key = $_COOKIE["key"]; 

    // ambil username berdasarkan id-nya
    $result = mysqli_query($conn, "SELECT username FROM user WHERE id = $id");
    $row = mysqli_fetch_assoc($result); // username tersebut diambil oleh $row menggunakan fungsi mysqli_fetch_assoc()

    // cek username dan cookie, apakah username yang diacak sama dengan cookie-nya
    if( $key === hash("sha256", $row["username"]) ) { // $key = berisi username yang sudah di hash/acak
        $_SESSION["login"] = true;
    }
}


if( isset($_SESSION["login"]) ) {
    header("Location: index.php");
    exit;
}


if( isset($_POST["login"]) ) {

    // tangkap datanya terlebih dahulu
    $username = $_POST["username"];
    $password = $_POST["password"];

    // cek ada ga username tertentu di dalam tabel user
    $result = mysqli_query($conn, "SELECT * FROM user WHERE username = '$username'");

    // cek username
    if( mysqli_num_rows($result) === 1 ) { // fungsi mysqli_num_rows = untuk menghitung ada berapa baris yang dikembalikan dari fungsi SELECT * FROM user (diatas). Jika ada/benar maka nilainya 1, kalau ga ada/salah maka nilainya 0

        // cek password
        $row = mysqli_fetch_assoc($result);
        if( password_verify($password, $row["password"]) ) { // fungsi password_verify(string yg belum diacak, string yg sudah diacak) = untuk mengecek sebuah string sama gak dengan hash-nya(acak), kalo sama berarti passwordnya benar

            // cek session
            $_SESSION["login"] = true;

            // cek remember me
            if( isset($_POST["remember"]) ) {
                // buat cookie
                setcookie("id", $row["id"], time() + 60);
                setcookie("key", hash("sha256", $row["username"]), time() + 60); // valuenya diberi nama "key", karena agar tidak ketauan sama orang lain. "key" itu isinya username yang sudah di hash/enkripsi
            }

            header("Location: index.php");
            exit;
        } 

    }

    $error = true; 

}

?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Halaman Login</title>
</head>
<body>

<h1>Halaman Login</h1>

<?php if( isset($error) ) : ?>
    <p style="color: red; font-style: italic;">username / password salah!</p>
<?php endif; ?>
    
<form action="" method="post">

    <ul>
        <li>
            <label for="username">Username :</label>
            <input type="text" name="username" id="username">
        </li>
        <li>
            <label for="password">Password :</label>
            <input type="password" name="password" id="password">
        </li>
        <li>
            <input type="checkbox" name="remember" id="remember">
            <label for="remember">Remember me</label>   
        </li>
        <li>
            <button type="submit" name="login">Login</button>
        </li>
    </ul>

</form>

</body>
</html>