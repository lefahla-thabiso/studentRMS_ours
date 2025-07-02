<?php
chdir("../../");
session_start();
require_once "db/config.php";
require_once "auditlog/audit.php"; 

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

    $user_id = $_SESSION['account_id'] ?? 0;
    $reg_no = $_POST["regno"];
    $fname = ucfirst($_POST["fname"]);
    $mname = ucfirst($_POST["mname"]);
    $lname = ucfirst($_POST["lname"]);
    $email = $_POST["email"];
    $gender = $_POST["gender"];
    $class = $_POST["class"];
    $role = "3";
    $pass = password_hash($_POST["regno"], PASSWORD_DEFAULT);
    $status = "1";
    $photo = serialize($_FILES["image"]);

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
        $reg_no = generateStudentRegNo($conn);

        $stmt = $conn->prepare(
            "SELECT id, email FROM tbl_staff WHERE email = ? OR id = ? UNION SELECT id, email FROM tbl_students WHERE email = ? OR id = ?"
        );
        $stmt->execute([$email, $reg_no, $email, $reg_no]);
        $result = $stmt->fetchAll();

        if (count($result) > 0) { 
            $_SESSION["reply"] = [
                ["error", "Email or registration number is used " ],
            ];
        log_activity($user_id, "email (". $email .") already used for registering " . ' ' . $fname . ' ' . $lname );

            header("location:../register_students");
        } else {
            if ($_FILES["image"]["name"] == "") {
                $img = "DEFAULT";
            } else {
                $target_dir = "images/students/";
                $img_ = unserialize($photo);
                $target_file = $target_dir . basename($img_["name"]);
                $imageFileType = strtolower(
                    pathinfo($target_file, PATHINFO_EXTENSION)
                );
                $destn_file = "avator_" . time() . "." . $imageFileType . "";
                $destn_upload = $target_dir . $destn_file;

                if (
                    $imageFileType != "jpg" &&
                    $imageFileType != "png" &&
                    $imageFileType != "jpeg"
                ) {
                    $img = "DEFAULT";
                } else {
                    if (move_uploaded_file($img_["tmp_name"], $destn_upload)) {
                        $img = $destn_file;
                    } else {
                        $img = "DEFAULT";
                    }
                }
            }

            $stmt = $conn->prepare(
                "INSERT INTO tbl_students (id, fname, mname, lname, gender, email, class, password, display_image) VALUES (?,?,?,?,?,?,?,?,?)"
            );
            $stmt->execute([
                $reg_no,
                $fname,
                $mname,
                $lname,
                $gender,
                $email,
                $class,
                $pass,
                $img,
            ]);
            log_activity($user_id, "Registered student ". $reg_no ." successfully ");

            $_SESSION["reply"] = [
                ["success", "Student registered successfully"],
            ];
            header("location:../register_students");
        }
    } catch (PDOException $e) {
        
        echo "Connection failed: " . $e->getMessage();
    }
} else {
    header("location:../");
}
?>