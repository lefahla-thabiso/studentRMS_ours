<?php
chdir("../../");
session_start();
require_once "db/config.php";
require 'vendor/autoload.php'; // PhpSpreadsheet
use PhpOffice\PhpSpreadsheet\IOFactory;

// Generate registration number
function generateStudentRegNo($conn) {
    $year = date('Y');
    $stmt = $conn->query("SELECT id FROM tbl_students ORDER BY id DESC LIMIT 1");

    if ($stmt->rowCount() > 0) {
        $lastId = (int) str_replace("ST" . $year, "", $stmt->fetchColumn());
    } else {
        $lastId = 0;
    }

    $newId = $lastId + 1;
    return "ST" . $year . str_pad($newId, 4, "0", STR_PAD_LEFT);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
     $selectedClass = $_POST['selected_class'];
    $file = $_FILES["file"]["tmp_name"];
    $st_rec = 0;

    try {
        $conn = new PDO(
            "mysql:host=" . DBHost . ";dbname=" . DBName . ";charset=" . DBCharset,
            DBUser,
            DBPass
        );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $spreadsheet = IOFactory::load($file);
        $sheetData = $spreadsheet->getActiveSheet()->toArray();


      
        foreach ($sheetData as $i => $row) {
            if ($i === 0) continue; // Skip header

            $fname = ucfirst(trim($row[0]));
            $mname = ucfirst(trim($row[1]));
            $lname = ucfirst(trim($row[2]));
            $gender = trim($row[3]);
            $email = trim($row[4]);
            $reg_no = generateStudentRegNo($conn);
            $pass = password_hash($reg_no, PASSWORD_DEFAULT);
            $img = "DEFAULT";

                // Check for existing student/staff
                $stmt = $conn->prepare("SELECT id FROM tbl_staff WHERE email = ? OR id = ? 
                                        UNION 
                                        SELECT id FROM tbl_students WHERE email = ? OR id = ?");
                $stmt->execute([$email, $reg_no, $email, $reg_no]);

                    if (
                        !preg_match("~[0-9]+~", $fname) &&
                        !preg_match("~[0-9]+~", $mname) &&
                        !preg_match("~[0-9]+~", $lname)
                    ) {
                        $stmt = $conn->prepare("INSERT INTO tbl_students 
                            (id, fname, mname, lname, gender, email, class, password, display_image) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                        $stmt->execute([
                            $reg_no,
                            $fname,
                            $mname,
                            $lname,
                            $gender,
                            $email,
                            $selectedClass,
                            $pass,
                            $img
                        ]);
                    }

                $st_rec++;
            }
            $_SESSION["reply"] = [["success", "Students imported completed"]];
            header("location:../import_students");
    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
} else {
    header("location:../");
}
?>
