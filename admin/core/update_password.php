<?php
chdir("../../");
session_start();
require_once "db/config.php";
require_once "const/check_session.php";
require_once "auditlog/audit.php"; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION["account_id"] ?? 0;

    $cpassword = $_POST["cpassword"];
    $npassword = password_hash($_POST["npassword"], PASSWORD_DEFAULT);

    if (password_verify($cpassword, $login)) {
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
                "UPDATE tbl_staff SET password = ? WHERE id = ?"
            );
            $stmt->execute([$npassword, $account_id]);

            log_activity($user_id, "Updated their password");

            $_SESSION["reply"] = [["success", "Password updated"]];
            header("location:../profile");
        } catch (PDOException $e) {
            echo "Connection failed: " . $e->getMessage();
        }
    } else {
        log_activity($user_id, "Entered wrong current password");
        
        $_SESSION["reply"] = [["warning", "Current password is not correct"]];
        header("location:../profile");
    }
} else {
    header("location:../");
}
?>