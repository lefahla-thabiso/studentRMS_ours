<?php
chdir("../../");
session_start();
require_once "db/config.php";
require_once "auditlog/audit.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $user_id = $_SESSION["account_id"] ?? 0;

    $std = $_POST["student"];
    $term = $_POST["term"];
    $class = $_POST["class"];

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

        foreach ($_POST as $key => $value) {
            if ($key !== "student" and $key !== "term" and $key !== "class") {
                $reg_no = $std;
                $score = $value;
                $subject = $key;

                $stmt = $conn->prepare(
                    "SELECT * FROM tbl_exam_results WHERE student = ? AND class=? AND subject_combination=? AND term = ?"
                );
                $stmt->execute([$reg_no, $class, $subject, $term]);
                $result = $stmt->fetchAll();

                if (count($result) < 1) {
                    $stmt = $conn->prepare(
                        "INSERT INTO tbl_exam_results (student, class, subject_combination, term, score) VALUES (?,?,?,?,?)"
                    );
                    $stmt->execute([$reg_no, $class, $subject, $term, $score]);
                    log_activity(
                        $user_id,
                        "Inserted marks for " .
                            $reg_no .
                            "student in for grade " .
                            $class .
                            ", term " .
                            $term .
                            " score being " .
                            $score
                    );
                } else {
                    $stmt = $conn->prepare(
                        "UPDATE tbl_exam_results SET score = ? WHERE student = ? AND class=? AND subject_combination=? AND term = ?"
                    );
                    $stmt->execute([$score, $reg_no, $class, $subject, $term]);
                    log_activity(
                        $user_id,
                        "Updated marks for " .
                            $reg_no .
                            "student in for grade " .
                            $class .
                            ", term " .
                            $term .
                            " score being " .
                            $score
                    );
                }
            }
        }

        $_SESSION["reply"] = [["success", "Results updated successfully"]];
        header("location:../single_results");
    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
} else {
    header("location:../");
}
?>