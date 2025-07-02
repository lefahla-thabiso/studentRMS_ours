<?php
chdir("../../");
session_start();
require_once "db/config.php";
require_once "const/check_session.php";
require_once "auditlog/audit.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION["account_id"] ?? 0;

    $fname = ucfirst($_POST["fname"]);
    $lname = ucfirst($_POST["lname"]);
    $email = $_POST["email"];
    $gender = $_POST["gender"];
    $id = $account_id;

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
            "SELECT email FROM tbl_staff WHERE email = ? AND id != ? UNION SELECT email FROM tbl_students WHERE email = ? AND id != ?"
        );
        $stmt->execute([$email, $id, $email, $id]);
        $result = $stmt->fetchAll();

        if (count($result) > 0) {
            log_activity($user_id, "trying to use already existing email");

            $_SESSION["reply"] = [["error", "Email is already added"]];
            header("location:../profile");
        } else {
            $stmt = $conn->prepare(
                "UPDATE tbl_staff SET fname=?, lname=?, gender=?, email=?, status=? WHERE id = ?"
            );
            $stmt->execute([$fname, $lname, $gender, $email, $status, $id]);
            log_activity($user_id, "Your profit is up to date");

            $_SESSION["reply"] = [["success", "Account updated successfully"]];
            header("location:../profile");
        }
    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
} else {
    header("location:../");
}
?>