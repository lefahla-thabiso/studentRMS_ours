<?php
require_once "../../db/config.php";

if (isset($_POST['id']) && isset($_POST['reason'])) {

    $id = $_POST['id'];
    $reason = trim($_POST['reason']);

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
        
    $stmt = $conn->prepare("UPDATE tbl_block_student SET reason = ? WHERE id = ?");
    $updated = $stmt->execute([$reason, $id]);

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
