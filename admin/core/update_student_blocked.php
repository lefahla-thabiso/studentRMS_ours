<?php
session_start();
require_once "db/config.php";
require_once "auditlog/audit.php";

if (isset($_POST["id"]) && isset($_POST["reason"])) {
    $user_id = $_SESSION["account_id"] ?? 0;
    
    $id = $_POST["id"];
    $reason = trim($_POST["reason"]);

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
    $stmt = $conn->prepare(
        "SELECT reason from tbl_block_student SET WHERE id = ?"
    );
    $stmt->execute([$user_id]);
    $result = $staffinfo->fetch(PDO::FETCH_ASSOC);
    log_activity(
        $user_id,
        "Reason before updating blocked student " .
            $id .
            " is " .
            $result["reason"]
    );

    $stmt = $conn->prepare(
        "UPDATE tbl_block_student SET reason = ? WHERE id = ?"
    );
    $updated = $stmt->execute([$reason, $id]);
    log_activity(
        $user_id,
        "Updating reason for blocking student " .
            $id .
            " is " .
            $result["reason"]
    );
    if ($updated) {
        header("Location: ../block?msg=updated");
        exit();
    } else {
        echo "Update failed.";
    }
} else {
    echo "Invalid request.";
}
?>