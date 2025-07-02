<?php
function log_activity($user_id, $description) {

    $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'UNKNOWN';

    try {
        $conn = new PDO("mysql:host=" . DBHost . ";dbname=" . DBName . ";charset=" . DBCharset, DBUser, DBPass);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $result =[];
        
        if(strlen((string)$user_id) > 2){
            $studentinfo = $conn->prepare("SELECT fname, lname, gender, email from tbl_students WHERE id = ?");
            $studentinfo->execute([$user_id]);
            $result = $studentinfo->fetch(PDO::FETCH_ASSOC);
        }else{
            $staffinfo = $conn->prepare("SELECT fname, lname, gender, email from tbl_staff WHERE id = ?");
            $staffinfo->execute([$user_id]);
          $result = $staffinfo->fetch(PDO::FETCH_ASSOC);

        //   echo $result['fname']; // Safer and clearer
        }
        
        $stmt = $conn->prepare("INSERT INTO audit_trail (fname, lname, gender, email, user_id, activity_description) VALUES (?,?,?,?,?,?)");
        $stmt->execute([ $result['fname'], $result['lname'], $result['gender'], $result['email'], $user_id, $description]);

    } catch (PDOException $e) {
        error_log("AUDIT ERROR: " . $e->getMessage());
    }
}