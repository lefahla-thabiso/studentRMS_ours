<?php
chdir("../../");
session_start();
require_once "db/config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $class = $_POST["class"];
    $term = $_POST["term"];

    try {
        $conn = new PDO(
            "mysql:host=" . DBHost . ";dbname=" . DBName . ";charset=" . DBCharset,
            DBUser,
            DBPass
        );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Get class name
        $stmt = $conn->prepare("SELECT name FROM tbl_classes WHERE id = ?");
        $stmt->execute([$class]);
        $classResult = $stmt->fetch(PDO::FETCH_ASSOC);
        $className = $classResult['name'] ?? "UnknownClass";

        // Get term name
        $stmt = $conn->prepare("SELECT name FROM tbl_terms WHERE id = ?");
        $stmt->execute([$term]);
        $termResult = $stmt->fetch(PDO::FETCH_ASSOC);
        $termName = $termResult['name'] ?? "UnknownTerm";

        // Generate filename
        $fileName = "{$className}_{$termName}.csv";
        $_SESSION["export_file"] = $fileName;

        if (file_exists("import_sheets/" . $fileName)) {
            unlink("import_sheets/" . $fileName);
        }

        $fp = fopen("import_sheets/" . $fileName, "w");

        // Fetch all subject names
        $stmt = $conn->prepare("SELECT name FROM tbl_subjects");
        $stmt->execute();
        $subjects = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // Create header row
        $headers = ["REGISTRATION NUMBER", "STUDENT NAME", "CLASS", "TERM"];
        foreach ($subjects as $subject) {
            $headers[] = $subject;
        }
        fputcsv($fp, $headers);

        // Fetch students in selected class
        $stmt = $conn->prepare("SELECT id, fname, lname FROM tbl_students WHERE class = ?");
        $stmt->execute([$class]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if (!$students) {
            echo "Error : No student data found for this class and term.";
            exit;
        }

        // Write student rows with empty subject marks
        foreach ($students as $student) {
            $row = [
                $student["id"],
                $student["fname"] . " " . $student["lname"],
                $className,
                $termName
            ];

            // Add NULLs for all subject columns
            foreach ($subjects as $_) {
                $row[] = null;
            }

            fputcsv($fp, $row);
        }

        fclose($fp);
        header("Location: ../import_results");

    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
} else {
    header("Location: ../");
}
?>
