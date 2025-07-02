<?php
session_start();
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

$stmt = $conn->prepare("UPDATE tbl_staff SET STATUS=3 WHERE id = ?");
$stmt->execute([$id]);

$staffinfo = $conn->prepare("SELECT fname, lname from tbl_staff WHERE id = ?");
$staffinfo->execute([$id]);
$result = $staffinfo->fetch(PDO::FETCH_ASSOC);

log_activity($user_id, "Academic teacher deleted : " . $result['fname'] .' '. $result['lname']);

$_SESSION['reply'] = array (array("success",'Academic deleted successfully'));
header("location:../academic");

}catch(PDOException $e)
{
echo "Connection failed: " . $e->getMessage();
}


}else{
header("location:../");
}
?>