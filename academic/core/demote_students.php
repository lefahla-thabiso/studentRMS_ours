<?php
chdir('../../');
session_start();
require_once('db/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

$selected_student = $_POST['selected_student'];
$student_demoted_to = $_POST['student_demoted_to'];


try {
  $conn = new PDO('mysql:host='.DBHost.';dbname='.DBName.';charset='.DBCharset.';collation='.DBCollation.';prefix='.DBPrefix.'', DBUser, DBPass);
  $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


  $stmt = $conn->prepare("UPDATE tbl_students SET class = ? WHERE id = ?");
  $stmt->execute([$student_demoted_to, $selected_student]);

  $_SESSION['reply'] = array (array("success",'Students demoted the student'));
  header("location:../promote_students");

}catch(PDOException $e)
{
  echo "Connection failed: " . $e->getMessage();
}


}else{
header("location:../");
}
?>