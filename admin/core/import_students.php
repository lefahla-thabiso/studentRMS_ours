<?php
chdir("../../");
session_start();
require_once "db/config.php";

// Generate registration number
function generateStudentRegNo($conn) {
    $year = date('Y');
    $stmt = $conn->query("SELECT id FROM tbl_students ORDER BY id DESC LIMIT 1");

    if ($stmt->rowCount() > 0) {
        $lastId = (int) str_replace("REG" . $year, "", $stmt->fetchColumn());
    } else {
        $lastId = 0;
    }

    $newId = $lastId +1;
    return "REG" . $year . str_pad($newId, 4, "0", STR_PAD_LEFT);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $file = $_FILES["file"]["tmp_name"];
    $st_rec = 0;

    try {
        $conn = new PDO(
            "mysql:host=" . DBHost . ";dbname=" . DBName . ";charset=" . DBCharset,
            DBUser,
            DBPass
        );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        if (($handle = fopen($file, "r")) !== false) {
            while (($data = fgetcsv($handle, 1000, ",")) !== false) {
                // Skip the header
                if ($st_rec === 0) {
                    $st_rec++;
                    continue;
                }

                // Expecting columns: fname, mname, lname, gender, email
                $fname = ucfirst(trim($data[0]));
                $mname = ucfirst(trim($data[1]));
                $lname = ucfirst(trim($data[2]));
                $gender = trim($data[3]);
                $email = trim($data[4]);
                $class = $_POST["class"];
                $reg_no = generateStudentRegNo($conn);
                $pass = password_hash($reg_no, PASSWORD_DEFAULT);
                $img = "DEFAULT";

                // Check for existing student/staff
                $stmt = $conn->prepare("SELECT id FROM tbl_staff WHERE email = ? OR id = ? 
                                        UNION 
                                        SELECT id FROM tbl_students WHERE email = ? OR id = ?");
                $stmt->execute([$email, $reg_no, $email, $reg_no]);

                // if ($stmt->rowCount() === 0) {
                    // Validate name (no digits)
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
                            $class,
                            $pass,
                            $img
                        ]);
                    }
                // }

                $st_rec++;
            }

            fclose($handle);
            $_SESSION["reply"] = [["success", "CSV import completed"]];
            header("location:../import_students");
        } else {
            echo "Could not open the file.";
        }
    } catch (PDOException $e) {
        echo "Connection failed: " . $e->getMessage();
    }
} else {
    header("location:../");
}
?>
