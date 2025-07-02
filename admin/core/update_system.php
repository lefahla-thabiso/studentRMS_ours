<?php
chdir('../../');
session_start();
require_once('db/config.php');
require_once "auditlog/audit.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = $_SESSION["account_id"] ?? 0;

if($_FILES['company_logo']['name'] == "")  {
try {
$conn = new PDO('mysql:host='.DBHost.';dbname='.DBName.';charset='.DBCharset.';collation='.DBCollation.';prefix='.DBPrefix.'', DBUser, DBPass);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $conn->prepare("UPDATE tbl_school SET name = ?");
$stmt->execute([$_POST['name']]);

  log_activity(
                        $user_id,
                        "Updating the system settings" 
                    );
$_SESSION['reply'] = array (array("success","System settings updated"));
header("location:../system");

}catch(PDOException $e)
{
echo "Connection failed: " . $e->getMessage();
}
}else{

$target_dir = "images/logo/";
$target_file = $target_dir . basename($_FILES["company_logo"]["name"]);
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
$destn_file = 'school_logo'.time().'.'.$imageFileType.'';
$destn_upload = $target_dir . $destn_file;
$unlink = 'images/logo/'.$_POST['old_logo'].'';

if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg") {
$_SESSION['reply'] = array (array("error","Only JPG, PNG and JPEG files are allowed"));
header("location:../system");
}else{

if (move_uploaded_file($_FILES["company_logo"]["tmp_name"], $destn_upload)) {
unlink($unlink);

try {
$conn = new PDO('mysql:host='.DBHost.';dbname='.DBName.';charset='.DBCharset.';collation='.DBCollation.';prefix='.DBPrefix.'', DBUser, DBPass);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $conn->prepare("UPDATE tbl_school SET name = ?, logo = ?");
$stmt->execute([$_POST['name'], $destn_file]);

$_SESSION['reply'] = array (array("success","System settings updated"));
header("location:../system");

}catch(PDOException $e)
{
echo "Connection failed: " . $e->getMessage();
}

}else{
$_SESSION['reply'] = array (array("danger","Could not upload file"));
header("location:../system");
}
}

}

}else{
header("location:../");
}
?>