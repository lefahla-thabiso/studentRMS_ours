<?php
chdir("../../");
session_start();
require_once "db/config.php";
require_once "const/check_session.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $class = $_POST["class"];
    $term = $_POST["term"];

    try {
        // Setup DB connection
        $conn = new PDO(
            "mysql:host=" . DBHost . ";dbname=" . DBName . ";charset=" . DBCharset,
            DBUser,
            DBPass
        );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        /** 1. Get term and class names **/
        $stmt = $conn->prepare("SELECT name FROM tbl_terms WHERE id = ?");
        $stmt->execute([$term]);
        $termName = $stmt->fetchColumn() ?? "UnknownTerm";

        $stmt = $conn->prepare("SELECT name FROM tbl_classes WHERE id = ?");
        $stmt->execute([$class]);
        $className = $stmt->fetchColumn() ?? "UnknownClass";

        /** 2. Generate file **/
        $fileName = "{$className}_{$termName}.csv";
        $_SESSION["export_file"] = $fileName;

        $filePath = "import_sheets/" . $fileName;
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        $fp = fopen($filePath, "w");

        /** 3. Get subject IDs taught by this teacher for this class **/
        $subject_ids = [];
        $stmt = $conn->prepare("SELECT subject, class FROM tbl_subject_combinations WHERE teacher = ?");
        $stmt->execute([$account_id]);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $class_ids = unserialize($row['class']);
            if (in_array($class, $class_ids)) {
                $subject_ids[] = $row['subject'];
            }
        }

        /** 4. Get subject names **/
        $subjects = [];
        if (!empty($subject_ids)) {
            $placeholders = implode(',', array_fill(0, count($subject_ids), '?'));
            $stmt = $conn->prepare("SELECT name FROM tbl_subjects WHERE id IN ($placeholders)");
            $stmt->execute($subject_ids);
            $subjects = $stmt->fetchAll(PDO::FETCH_COLUMN);
        }

        /** 5. CSV header row **/
        $headers = ["REGISTRATION NUMBER", "STUDENT NAME", "CLASS", "TERM"];
        foreach ($subjects as $subject) {
            $headers[] = $subject;
        }
        fputcsv($fp, $headers);

        /** 6. Get students in this class **/
        $stmt = $conn->prepare("SELECT id, fname, lname FROM tbl_students WHERE class = ?");
        $stmt->execute([$class]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$students) {
            // echo "Error: No students found for this class.";
        $_SESSION['reply'] = array (array("error",'No registered students for selected grade class'));
        header("Location: ../import_results");

            fclose($fp);
            exit;
        }

        /** 7. Write each student's row **/
        foreach ($students as $student) {
            $row = [
                $student["id"],
                $student["fname"] . " " . $student["lname"],
                $className,
                $termName
            ];

            // Add empty cells for subject marks
            foreach ($subjects as $_) {
                $row[] = null;
            }

            fputcsv($fp, $row);
        }

        fclose($fp);
        header("Location: ../import_results");

    } catch (PDOException $e) {
        echo "Database error: " . $e->getMessage();
    }
} else {
    header("Location: ../");
}
?>
