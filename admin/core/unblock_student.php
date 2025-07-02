<?php
chdir('../../');
session_start();
require_once('db/config.php');
require_once "auditlog/audit.php"; 

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $user_id = $_SESSION['account_id'] ?? 0;

$id = $_GET['id'];

try {
$conn = new PDO('mysql:host='.DBHost.';dbname='.DBName.';charset='.DBCharset.';collation='.DBCollation.';prefix='.DBPrefix.'', DBUser, DBPass);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $conn->prepare("DELETE FROM tbl_block_student WHERE id = ?");
$stmt->execute([$id]);
        log_activity($user_id, "Student (". $id .") unblocked");

$_SESSION['reply'] = array (array("success",'Student Unblocked successfully'));
header("location:../block");

}catch(PDOException $e)
{
echo "Connection failed: " . $e->getMessage();
}


}else{
header("location:../");
}
?>