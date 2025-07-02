<?php
chdir("../../");
session_start();
require_once "db/config.php";
require_once "auditlog/audit.php";
require_once "auditlog/audit.php"; 

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // $title = $_POST["title"];
    $user_id = $_SESSION['account_id'] ?? 0;

    $selected_student = $_POST["selected_student"];
    $reason = $_POST["reason"];
   $post_date = date('Y-m-d G:i:s');
    // $level = $_POST["audience"];

    $status = 1;
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

        $stmt = $conn->prepare("SELECT * FROM tbl_block_student where studentID = ?");
        $stmt->execute([$selected_student]);
        $student = $stmt->fetchAll();
        
        if(empty($student)){
            $stmt = $conn->prepare("SELECT * FROM tbl_students where id = ?");
            $stmt->execute([$selected_student]);
            $student = $stmt->fetchAll();
            foreach ($student as $studentDetails) {
                $stmt = $conn->prepare(
                    "INSERT INTO tbl_block_student (studentID, firstName, lastName,grade,reason,status) VALUES (?,?,?,?,?,?)"
                );
                $stmt->execute([$studentDetails[0],$studentDetails[1],$studentDetails[3],$studentDetails[6],$reason,$status]);
            }
            $_SESSION["reply"] = [["success", "Student Blocked successfully"]];
            log_activity($user_id,  $student[0][0] . ' '."successfully blocked");

            header("location:../block");
        }else{
             $stmt = $conn->prepare("SELECT * FROM tbl_students where id = ?");
            $stmt->execute([$selected_student]);
            $student = $stmt->fetchAll();
            log_activity($user_id, $student[0][0] . ' '. "already blocked");
             $_SESSION["reply"] = [["WARNING", $student[0][1] . " " . $student[0][3] . " is Already blocked"]];
            header("location:../block");
        }
    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
} else {
    header("location:../");
}
?>