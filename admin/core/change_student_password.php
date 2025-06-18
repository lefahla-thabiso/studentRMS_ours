<?php
chdir("../../");
session_start();
require_once "db/config.php";
require_once "const/check_session.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user = trim($_POST["cPassid"]);
    $nPassword = trim($_POST["nPass"]);
    $cPassword = trim($_POST["cnPass"]);

    $enPassword = password_hash($nPassword, PASSWORD_DEFAULT);

    // if (password_verify($cpassword, $login)) {

    if ($nPassword != $cPassword) {
        $_SESSION["reply"] = [["error", "Passwords do not match"]];
        header("location:../students");
    } elseif ($nPassword == "") {
        $_SESSION["reply"] = [["error", "New Password is not entered"]];
        header("location:../students");
    } elseif ($cPassword == "") {
        $_SESSION["reply"] = [["error", "Confirmation password is empty"]];
        header("location:../students");
    }// }  elseif (strlen($cPassword) + strlen($nPassword) < 16) {
    //     $_SESSION["reply"] = [["error", "Lengths do not match"]];
    //     header("location:../teachers");
    // } 
    else {
        try {
            $conn = new PDO(
                "mysql:host=" .
                    DBHost .
                    ";dbname=" .
                    DBName .
                    ";charset=" .
                    DBCharset .
                    ";collation=" .
                    DBCollation .
                    ";prefix=" .
                    DBPrefix .
                    "",
                DBUser,
                DBPass
            );
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $conn->prepare(
                "UPDATE tbl_students SET password = ? WHERE id = ?"
            );
            $stmt->execute([$enPassword, $user]);

            // $_SESSION["reply"] = [["success", "Password updated"]];
            $_SESSION["reply"] = [["success", "Password updated"]];
            header("location:../students");
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
        // header("location:../");
        // }else{
        // $_SESSION['reply'] = array (array("warning", "Current password is not correct"));
        // header("location:../teachers");
        // }
    }
}
?>