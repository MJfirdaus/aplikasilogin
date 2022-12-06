<?php

// koneksi ke database
$conn = mysqli_connect("localhost", "root", "", "phpdasar"); // value: "nama host-nya", "username mysql-nya", "password-nya", "nama database-nya". Ini dibikin variable supaya mempermudah

function query($query) { // membuat function untuk ambil data mahasiswa / query data mahasiswa
    global $conn; // mencari variable $conn, soalnya di dalam fungsi ini tidak ada variable $conn
    $result = mysqli_query($conn, $query); // query data mahasiswa
    $rows = [];
    while( $row = mysqli_fetch_assoc($result) ) { // ambil data
        $rows[] = $row;
    }
    return $rows;
}


function tambah($data) {
    global $conn;
    // ambil data dari setiap elemen dalam form

    $nama = htmlspecialchars($data["nama"]); // htmlspecialchars = agar website kita tidak bisa didisipi script html dan css
    $nim = htmlspecialchars($data["nim"]);
    $email = htmlspecialchars($data["email"]);
    $jurusan = htmlspecialchars($data["jurusan"]);

    // upload gambar 
    $gambar = upload();
    if( !$gambar ) {
        return false;
    }

    // query insert data
    $query = "INSERT INTO mahasiswa
              VALUES ('', '$nama', '$nim', '$email', '$jurusan', '$gambar')
             ";
    mysqli_query($conn, $query);

    return mysqli_affected_rows($conn);
}


function upload() {

    // ambil datanya
    $namaFile = $_FILES['gambar']['name'];
    $ukuranFile = $_FILES['gambar']['size'];
    $error = $_FILES['gambar']['error'];
    $tmpName = $_FILES['gambar']['tmp_name'];

    // tahap pengecekan
    // 1. cek apakah tidak ada gambar yang diupload
    if( $error === 4 ) { // 4 itu artinya tidak ada gambar yang diupload
        echo "
              <script>
                alert('Pilih gambar terlebih dahulu!');
              </script>
             ";
        return false; // jika tidak upload apa-apa hentikan function-nya 
    }

    // 2. cek apakah yang diupload adalah gambar
    $ekstensiGambarValid = ['jpg', 'jpeg', 'png']; // user hanya bisa mengupload tiga ekstensi file saja
    $ekstensiGambar = explode('.', $namaFile); //fungsi explode digunakan untuk memecah sebuah string menjadi array
    $ekstensiGambar = strtolower(end($ekstensiGambar)); // fungsi strtolower = untuk menjadikan semua huruf menjadi huruf kecil 
    // fungsi end = untuk mengambil array yang paling akhir
    if( !in_array($ekstensiGambar, $ekstensiGambarValid) ) { // fungsi in_array = untuk mengecek apakah ada string di dalam sebuah array
        echo "
              <script>
                alert('Yang anda upload bukan gambar!');
              </script>
             ";
        return false;
    } 

    // 3. cek apakah ukuran gambarnya terlalu besar atau tidak
    if( $ukuranFile > 1000000 ) { // ukurannya dalam satuan byte
        echo "
              <script>
                alert('Ukuran gambar terlalu besar!');
              </script>
             ";
        return false;
    }

    // 4. lolos pengecekan, gambar siap diupload
    // generate nama gambar baru
    $namaFileBaru = uniqid(); // fungsi uniqid = untuk membangkitkan string random angka, yang nanti akan menjadi nama gambar kita
    $namaFileBaru .= '.';
    $namaFileBaru .= $ekstensiGambar;

    move_uploaded_file($tmpName, 'img/' . $namaFileBaru); // fungsi move_uploaded_file() = untuk memindahkan file ke tujuannya

    return $namaFileBaru;
}


function hapus($id) {
    global $conn; 
    
    $file = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM mahasiswa WHERE id = $id")); 
    // agar file gambar di dalam folder juga terhapus
    unlink('img/' . $file["gambar"]);
    $hapus = "DELETE FROM mahasiswa WHERE id = $id";
    mysqli_query($conn, $hapus);

    return mysqli_affected_rows($conn);
}


function ubah($data) {
    global $conn;

    $id = $data["id"];
    $nama = htmlspecialchars($data["nama"]);
    $nim = htmlspecialchars($data["nim"]);
    $email = htmlspecialchars($data["email"]);
    $jurusan = htmlspecialchars($data["jurusan"]);
    $gambarLama = $data["gambarLama"];

    // cek apakah user pilih gambar baru atau tidak
    if( $_FILES["gambar"]["error"] === 4 ) {
        $gambar = $gambarLama;
    } else {
        $gambar = upload();
    }
    

    // query ubah data
    $query = "UPDATE mahasiswa SET
                nama = '$nama',
                nim = '$nim',
                email = '$email',
                jurusan = '$jurusan',
                gambar = '$gambar'
              WHERE id = $id
             ";
    mysqli_query($conn, $query);

    return mysqli_affected_rows($conn);
}


function cari($keyword) {
    $query = "SELECT * FROM mahasiswa WHERE
                nama LIKE '%$keyword%' OR
                nim LIKE '%$keyword%' OR
                email LIKE '%$keyword%' OR
                jurusan LIKE '%$keyword%'
             "; // LIKE '%$keyword%' = memudahkan untuk mencari data, tidak harus full menuliskan kata katanya
    return query($query); 
}


function registrasi($data) {
    global $conn;

    $username = strtolower(stripslashes($data["username"])); // fungsi stripslashes = agar karakter backslash tidak masuk ke dalam database
    $password = mysqli_real_escape_string($conn, $data["password"]); // fungsi mysqli_real_escape_string = untuk memungkinkan si user-nya memasukkan password ada tanda kutipnya dan tanda kutipnya akan masuk ke dalam database secara aman
    $password2 = mysqli_real_escape_string($conn, $data["password2"]);


    // cek username sudah ada atau belum
    $result = mysqli_query($conn, "SELECT username FROM user WHERE username = '$username'");

    if( mysqli_fetch_assoc($result) ) {
        echo "
                <script>
                    alert('Username sudah terdaftar!');
                </script>
             ";
        return false; // hentikan function-nya
    }

    // cek konfirmasi password
    if( $password !== $password2 ) {
        echo "
                <script>
                    alert('Konfirmasi password tidak sesuai!');
                </script>
             ";
        return false; // hentikan function-nya
    }

    // enkripsi password
    $password = password_hash($password, PASSWORD_DEFAULT); // fungsi password_hash(password apa yang mau di acak, mengacaknya pake algoritma apa); = untuk mengenkripsi password
    // PASSWORD_DEFAULT = algoritma acak yang di pilih oleh php
    

    // tambahkan user baru ke database
    mysqli_query($conn, "INSERT INTO user VALUES('', '$username', '$password')");

    return mysqli_affected_rows($conn);

}
