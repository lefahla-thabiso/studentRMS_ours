<?php
chdir("../../");
session_start();
require_once "db/config.php";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_FILES["csv_file"])) {
    $file = $_FILES["csv_file"]["tmp_name"];
    $handle = fopen($file, "r");

    if (!$handle) {
        die("Failed to open uploaded file.");
    }

    try {
        $conn = new PDO("mysql:host=" . DBHost . ";dbname=" . DBName, DBUser, DBPass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $header = fgetcsv($handle); // Skip and read the first row (headings)
        print_r($header); // DEBUG: show header columns

        while (($row = fgetcsv($handle)) !== false) {
            print_r($row); // DEBUG: show each row

            $regNo = trim($row[0]);
            $studentName = trim($row[1]);
            $className = trim($row[2]);
            $termName = trim($row[3]);

            // Get student ID
            $stmt = $conn->prepare("SELECT id FROM tbl_students WHERE id = ?");
            $stmt->execute([$regNo]);
            $studentRow = $stmt->fetch();

            if (!$studentRow) {
                echo "❌ Student not found: $regNo<br>";
                continue;
            }
            $studentId = $studentRow["id"];

            // Get class ID
            $stmt = $conn->prepare("SELECT id FROM tbl_classes WHERE name = ?");
            $stmt->execute([$className]);
            $classRow = $stmt->fetch();
            if (!$classRow) {
                echo "❌ Class not found: $className<br>";
                continue;
            }
            $classId = $classRow["id"];

            // Get term ID
            $stmt = $conn->prepare("SELECT id FROM tbl_terms WHERE name = ?");
            $stmt->execute([$termName]);
            $termRow = $stmt->fetch();
            if (!$termRow) {
                echo "❌ Term not found: $termName<br>";
                continue;
            }
            $termId = $termRow["id"];

            // Loop through each subject column starting from index 4
            for ($i = 4; $i < count($header); $i++) {
                $subjectName = trim($header[$i]);
                $score = trim($row[$i]);

                if ($score === '' || strtolower($score) === 'null') {
                    continue; // Skip if no score
                }

                // Get subject ID
                $stmt = $conn->prepare("SELECT id FROM tbl_subjects WHERE name = ?");
                $stmt->execute([$subjectName]);
                $subjectRow = $stmt->fetch();

                if (!$subjectRow) {
                    echo "❌ Subject not found: $subjectName<br>";
                    continue;
                }
                $subjectId = $subjectRow["id"];

                // Get subject_combination
                $stmt = $conn->prepare("SELECT id FROM tbl_subject_combinations WHERE subject = ?");
                $stmt->execute([$subjectId]);
                $comboRow = $stmt->fetch();

                if (!$comboRow) {
                    echo "❌ No subject_combination found for subject $subjectName<br>";
                    continue;
                }
                $subjectCombinationId = $comboRow["id"];

                // DEBUG: Show what will be inserted
                echo "✅ Inserting for student: $studentId, subject: $subjectName, score: $score<br>";

                // Insert
                try {
                    $stmt = $conn->prepare("INSERT INTO tbl_exam_results (student, class,subject_combination ,term , score) VALUES (?, ?, ?, ?, ?)");
                    $stmt->execute([$studentId, $classId,$subjectCombinationId ,$termId, $score]);
                } catch (PDOException $e) {
                    echo "❌ Insert failed for student $studentId - $subjectName: " . $e->getMessage() . "<br>";
                }
            }
        }

        fclose($handle);
        echo "<br>✅ Import completed.";
       

        header("location:../import_results");
        $_SESSION['reply'] = array (array("success","Marks Captured successfully"));
    } catch (PDOException $e) {
        echo "❌ Connection failed: " . $e->getMessage();
    }

} else {
    header("location:../");
    echo "❌ Invalid request. No file uploaded.";
}
?>
