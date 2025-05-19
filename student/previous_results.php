<?php
    chdir('../');
    session_start();
    require_once('db/config.php');
    require_once('const/school.php');
    require_once('const/check_session.php');
    require_once('const/calculations.php');
    
    if ($res == "1" && $level == "3") {
        // Continue execution
    } else {
        header("location:../");
    }

    $stmt = $conn->prepare("SELECT * FROM tbl_grade_system");
    $stmt->execute();
    $grading = $stmt->fetchAll();

    // Initialize variables
    $selectedValue = "";
    $selectedText = "";
    $displayMessage = " ";
    $classChoosen = array();

    // Check if form is submitted
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        // Capture the select value if it exists in POST data
        if (isset($_POST["yearofstudy"])) {
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

            // selecting past student marks from previous classes using class id's
            $stmt = $conn->prepare("SELECT id class FROM tbl_classes GROUP BY id");
            $stmt->execute();
            $class_id_list = $stmt->fetchAll(); 

            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $classChoosen[$row['id']] = $row['id'];
            }
            $selectedValue = $_POST["yearofstudy"];
            
            // Define options array to get the text value
            // $options = [
            //     "option1" => "10",
            //     "option2" => "11"
            // ];
            
            // Get the text corresponding to the selected value
            $selectedText = isset($classChoosen[$selectedValue]) ? $classChoosen[$selectedValue] : "";
            
            // Create display message
            $displayMessage = $selectedValue;
        }
    }
?>
	<!DOCTYPE html>
	<html lang="en">
	<meta http-equiv="content-type" content="text/html;charset=utf-8" />

	<head>
		<title>SRMS - Vieiwng past Resultss</title>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<base href="../">
		<link rel="stylesheet" type="text/css" href="css/main.css">
		<link rel="icon" href="images/icon.ico">
		<link rel="stylesheet" type="text/css" href="cdn.jsdelivr.net/npm/bootstrap-icons%401.10.5/font/bootstrap-icons.css">
		<link rel="stylesheet" href="cdn.datatables.net/v/bs5/dt-1.13.4/datatables.min.css">
		<link type="text/css" rel="stylesheet" href="loader/waitMe.css"> </head>

	<body class="app sidebar-mini">
		<header class="app-header"> <a class="app-header__logo" href="javascript:void(0);">SRMS</a>
			<a class="app-sidebar__toggle" href="#" data-toggle="sidebar" aria-label="Hide Sidebar"></a>
			<ul class="app-nav">
				<li class="dropdown">
					<a class="app-nav__item" href="#" data-bs-toggle="dropdown" aria-label="Open Profile Menu"> <i class="bi bi-person fs-4"></i> </a>
					<ul class="dropdown-menu settings-menu dropdown-menu-right">
						<li>
							<a class="dropdown-item" href="student/settings"> <i class="bi bi-person me-2 fs-5"></i> Change Password </a>
						</li>
						<li>
							<a class="dropdown-item" href="logout"> <i class="bi bi-box-arrow-right me-2 fs-5"></i> Logout </a>
						</li>
					</ul>
				</li>
			</ul>
		</header>
		<div class="app-sidebar__overlay" data-toggle="sidebar"></div>
		<aside class="app-sidebar">
			<div class="app-sidebar__user">
				<div>
					<p class="app-sidebar__user-name">
						<?php echo $fname.' '.$lname; ?>
					</p>
					<p class="app-sidebar__user-designation">Student</p>
				</div>
			</div>
			<ul class="app-menu">
				<li>
					<a class="app-menu__item" href="student"> <i class="app-menu__icon feather icon-monitor"></i> <span class="app-menu__label">Dashboard</span> </a>
				</li>
				<li>
					<a class="app-menu__item" href="student/view"> <i class="app-menu__icon feather icon-user"></i> <span class="app-menu__label">My Profile</span> </a>
				</li>
				<li>
					<a class="app-menu__item" href="student/subjects"> <i class="app-menu__icon feather icon-book-open"></i> <span class="app-menu__label">My Subjects</span> </a>
				</li>
				<!-- <li>
                <a class="app-menu__item active" href="student/results">
                    <i class="app-menu__icon feather icon-file-text"></i>
                    <span class="app-menu__label">My Examination Results</span>
                </a>
            </li> -->
				<li class="treeview"><a class="app-menu__item" href="javascript:void(0);" data-toggle="treeview"><i class="app-menu__icon feather icon-file-text"></i><span class="app-menu__label">Examination Results</span><i class="treeview-indicator bi bi-chevron-right"></i></a>
					<ul class="treeview-menu">
						<li><a class="treeview-item" href = "student/results"><i class="icon bi bi-circle-fill"></i>My <?php echo date("Y");?> Results</a></li>
						<li><a class="treeview-item" href = "student/previous_results"><i class="icon bi bi-circle-fill"></i> Previous Years Results</a></li>
					</ul>
				</li>
				<li>
					<a class="app-menu__item" href="student/grading-system"> <i class="app-menu__icon feather icon-award"></i> <span class="app-menu__label">Grading System</span> </a>
				</li>
				<!-- <li>
                <a class="app-menu__item" href="student/division-system">
                    <i class="app-menu__icon feather icon-layers"></i>
                    <span class="app-menu__label">Division System</span>
                </a>
            </li> -->
        </ul>
    </aside>

    <main class="app-content">
        <div class="app-title">
            <div>
                <h1>Vieiwng past Results</h1>
            </div>
        </div>

        <div class="row" style="margin-top: -1%;">
            <div class="col-md-12 center_form" >
                <div class="tile">
                    <h4 class="tile-title">Vieiwng past Results</h4>
                    
                    <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" id="autoForm">
                                   <div class="mb-2">
                                            <label for="autoSelect" class="form-label"><b>Select Class</b></label>
                                            <select id="autoSelect" class="form-control select2" name="yearofstudy" required style="width: 100%;" onchange="this.form.submit()">
                                                <option value="" selected disabled> Select class</option>
                                                <?php try {
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

                                    // selecting past student marks from previous classes using class id's
                                    $stmt = $conn->prepare("SELECT  class FROM tbl_exam_results WHERE student = ? AND class != ? GROUP BY class");
                                    $stmt->execute([$account_id,$class]);
                                    $class_id_list = $stmt->fetchAll(); 
                                    
                                    if(count($class_id_list) > 0){                     
                                    foreach ($class_id_list as $id) {
                                        // taking the names of classes using their id's
                                        $stmt = $conn->prepare("SELECT name from tbl_classes WHERE id = ?");
                                        $stmt->execute([$id[0]]);
                                        $result = $stmt->fetchAll(); 

                                        foreach ($result as $row) { ?>
                                            <option value="<?php echo $id[0];?>" <?php  if($selectedValue == $row[0]) echo "selected";?>>
                                                <!-- populating the names of the classes in dropdown bow -->
                                            <?php  echo $row[0] ?>
                                        
                                        </option>
                                        <?php
                                        }
                                        ?> 
                                        
                                        <?php 
                                    }
                                    }
                                    } catch (PDOException $e) {
                                    echo "Connection failed: " . $e->getMessage();
                                    } ?>
                                            </select>
                                        </div>
<!--                                     
                                <div class="text-center" style = "margin-top: 20px;">
                                    <button  class="btn btn-primary app_btn hidden" type="submit">View Results</button>
                                </div> -->
                                <!-- <button type="submit" class="hidden">Submit</button> -->
            </form>

            <?php
                    // if (WBResAvi == "1") {
                        try {
                            $conn = new PDO(
                                'mysql:host='.DBHost.';dbname='.DBName.';charset='.DBCharset.';collation='.DBCollation.';prefix='.DBPrefix.'', 
                                DBUser, 
                                DBPass
                            );
                            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

                            $stmt = $conn->prepare("SELECT class FROM tbl_exam_results GROUP BY class");
                            $stmt->execute();
                            $_classes = $stmt->fetchAll(); //take all classes id from tbl_exam_results
                           
                            // foreach ($_classes as $key => $class) { 
                                $stmt = $conn->prepare("SELECT * FROM tbl_classes WHERE id = ?");  
                                $stmt->execute([$displayMessage]);
                                $class_de = $stmt->fetchAll(); // take rows of corresponding 

                                $stmt = $conn->prepare("SELECT * FROM tbl_exam_results WHERE class = ? AND student = ? LIMIT 1");
                                $stmt->execute([$displayMessage, $account_id]);
                                // take all the mark of the logged student for his/her class(form one, form two......)
                                $student_has_marks_for_exams = $stmt->fetchAll();  

                                if (count($student_has_marks_for_exams) > 0) {
                                    $stmt = $conn->prepare("SELECT term FROM tbl_exam_results WHERE class = ? GROUP BY term");
                                    $stmt->execute([$displayMessage]);
                                    $_terms = $stmt->fetchAll(); // take all the terms of which the student has results for
                                    
                                    ?>
                                    <div class="col-md-12" >
                                        <div class="tile" >
                                            <div class="tile-title-w-btn">
                                                <!-- heading display which class is the student in -->
                                                <h5 class="title"><?php echo$class_de[0][1]; ?></h5>
                                            </div>
                                            <div class="tile-body">
                                                <div class="bs-component">
                                                    <!-- tabs that show the terms in report ( term  | term 2 | term 3 . . . ) -->
                                                     <!-- when you click My examination Results on the navigation panel -->
                                                    <ul class="nav nav-tabs" role="tablist">
                                                        <?php
                                                        $t = 1;
                                                        // id's for terms
                                                        foreach ($_terms as $key => $_term) {
                                                            $stmt = $conn->prepare("SELECT name FROM tbl_terms WHERE id = ?");
                                                            $stmt->execute([$_term[0]]);
                                                            $_term_data = $stmt->fetchAll(); 

                                                            if ($t == "1") {
                                                                ?>
                                                                <li class="nav-item" role="presentation">
                                                                    <a class="nav-link active" data-bs-toggle="tab"
                                                                       href="#term_<?php echo $_term[0]; ?>" aria-selected="true" role="tab">
                                                                       <!-- terms names (Jan -Mar) -->
                                                                        <?php echo $_term_data[0][0]; ?>
                                                                    </a>
                                                                </li>
                                                                <?php
                                                            }
                                                             else {
                                                                ?>
														<li class="nav-item" role="presentation">
															<a class="nav-link" data-bs-toggle="tab" href="#term_<?php echo $_term[0]; ?>" aria-selected="false" tabindex="-1" role="tab">
																<?php echo $_term_data[0][0]; ?>
															</a>
														</li>
														<?php
                                                            }
                                                            $t++;
                                                        }
                                                        ?>
											</ul>
											<div class="tab-content" id="myTabContent">
												<?php
                                                        $t = 1;
                                                        foreach ($_terms as $key => $_term) {
                                                            if ($t == "1") {
                                                                ?>
                                                                <div class="mt-3 tab-pane fade active show" id="term_<?php echo $_term[0]; ?>"
                                                                     role="tabpanel">
                                                                    <table class="table table-bordered table-striped table-sm">
                                                                        <thead>
                                                                        <tr style="text-align: center;" >
                                                                                <th width="40">#</th>
                                                                                <th width = "60">SUBJECT</th>
                                                                                <th>SCORE (%)</th>
                                                                                <th>GRADE</th>
                                                                                <th>REMARK</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <?php
                                                                            $stmt = $conn->prepare("SELECT * FROM tbl_subject_combinations LEFT JOIN tbl_subjects ON tbl_subject_combinations.subject = tbl_subjects.id");
                                                                            $stmt->execute();
                                                                            $result = $stmt->fetchAll();
                                                                            $n = 1;
                                                                            $tscore = 0;
                                                                            $t_subjects = 0;
                                                                            $subssss = array();

                                                                            foreach ($result as $key => $row) {
                                                                               $class_list = unserialize($row[1]);

                                                                                if (in_array($displayMessage,$class_list)) {
                                                                                    $t_subjects++;
                                                                                    $score = 0;
                                                                                    $grd = "N/A";
                                                                                    $rm = "N/A";

                                                                                    $stmt = $conn->prepare("SELECT * FROM tbl_exam_results WHERE class = ? AND subject_combination = ? AND term = ? AND student = ?");
                                                                                    $stmt->execute([$displayMessage, $row[0], $_term[0], $account_id]);
                                                                                    $ex_result = $stmt->fetchAll();

                                                                                    if (!empty($ex_result[0][5])) {
                                                                                        $score = $ex_result[0][5];
                                                                                    }
                                                                                    array_push($subssss, $score);

                                                                                    $tscore = $tscore + $score;
                                                                                    foreach ($grading as $grade) {
                                                                                        if ($score >= $grade[2] && $score <= $grade[3]) {
                                                                                            $grd = $grade[1];
                                                                                            $rm = $grade[4];
                                                                                        }
                                                                                    }
                                                                                    ?>
                                                                                    <tr>
                                                                                        <td><?php echo $n; ?></td>
                                                                                        <td><?php echo $row[6]; ?></td>
                                                                                        <td align="center" width="100"><?php if($score != 0) echo $score; ?></td>
                                                                                        <td align="center" width="100"><?php if($score != 0) echo $grd; ?></td>
                                                                                        <td align="center" width="200"><?php if($score != 0) echo $rm; ?></td>
                                                                                    </tr
                                                                                    <?php
                                                                                }
                                                                                $n++;
                                                                            }
                                                                            ?>
                                                                        </tbody>
                                                                    </table>

                                                                    <?php
                                                                    if ($t_subjects == "0") {
                                                                        $av = '0';
                                                                    } else {
                                                                        $av = round($tscore/$t_subjects);
                                                                    }
                                                                    foreach ($grading as $grade) {
                                                                        if ($av >= $grade[2] && $av <= $grade[3]) {
                                                                            $grd_ = $grade[1];
                                                                            $rm_ = $grade[4];
                                                                        }
                                                                    }
                                                                    ?>

                                                                    <p>
                                                                        TOTAL SCORE <span
                                                                            class="badge bg-secondary rounded-pill"><?php if($tscore != 0) echo $tscore; else echo " - "; ?></span>
                                                                        AVERAGE <span
                                                                            class="badge bg-secondary rounded-pill"><?php if($tscore != 0) echo $av; else echo " - ";  ?></span>
                                                                        GRADE <span
                                                                            class="badge bg-secondary rounded-pill"><?php if($tscore != 0)  echo $grd_; else echo " - "; ?></span>
                                                                        REMARK <span
                                                                            class="badge bg-secondary rounded-pill"><?php if($tscore != 0) echo $rm_; else echo " - ";  ?></span>
                                                                        <!-- DIVISION <span
                                                                            class="badge bg-secondary rounded-pill"><?php echo get_division($subssss); ?></span>
                                                                        POINTS <span
                                                                            class="badge bg-secondary rounded-pill"><?php echo get_points($subssss); ?></span> -->
                                                                    </p>

                                                                    <a target="_blank" href="student/save_pdf?term=<?php echo $_term[0]; ?>&currentClass=<?=urlencode($displayMessage); ?>"
                                                                       class="btn btn-primary btn-sm">DOWNLOAD</a>
                                                                </div>
                                                                <?php
                                                             } else {
                                                                ?>
                                                                <div class="mt-3 tab-pane fade" id="term_<?php echo $_term[0]; ?>"
                                                                     role="tabpanel">
                                                                    <table class="table table-bordered table-striped table-sm">
                                                                        <thead>
                                                                            <tr style="text-align: center;" >
                                                                                <th width="40">#</th>
                                                                                <th width = "60">SUBJECT</th>
                                                                                <th>SCORE (%)</th>
                                                                                <th>GRADE</th>
                                                                                <th>REMARK</th>
                                                                            </tr>
                                                                        </thead>
                                                                        <tbody>
                                                                            <?php
                                                                            $stmt = $conn->prepare("SELECT * FROM tbl_subject_combinations LEFT JOIN tbl_subjects ON tbl_subject_combinations.subject = tbl_subjects.id");
                                                                            $stmt->execute();
                                                                            $result = $stmt->fetchAll();
                                                                            $n = 1;
                                                                            $tscore = 0;
                                                                            $t_subjects = 0;
                                                                            $subssss = array();

                                                                            foreach ($result as $key => $row) {
                                                                               $class_list = unserialize($row[1]);

                                                                                if (in_array($displayMessage,$class_list)) {
                                                                                    $t_subjects++;
                                                                                    $score = 0;
                                                                                    $grd = "N/A";
                                                                                    $rm = "N/A";

                                                                                    $stmt = $conn->prepare("SELECT * FROM tbl_exam_results WHERE class = ? AND subject_combination = ? AND term = ? AND student = ?");
                                                                                    $stmt->execute([$displayMessage, $row[0], $_term[0], $account_id]);
                                                                                    $ex_result = $stmt->fetchAll();

                                                                                    if (!empty($ex_result[0][5])) {
                                                                                        $score = $ex_result[0][5];
                                                                                    }
                                                                                    array_push($subssss, $score);

                                                                                    $tscore = $tscore + $score;
                                                                                    foreach ($grading as $grade) {
                                                                                        if ($score >= $grade[2] && $score <= $grade[3]) {
                                                                                            $grd = $grade[1];
                                                                                            $rm = $grade[4];
                                                                                        }
                                                                                    }
                                                                                    ?>
                                                                                    <tr>
                                                                                        <td><?php echo $n; ?></td>
                                                                                        <td><?php echo $row[6]; ?></td>
                                                                                         
                                                                                        <td align="center" width="100"><?php if($score != 0) echo $score;?></td>
                                                                                        <td align="center" width="100"><?php if($score) echo $grd; ?></td>
                                                                                        <td align="center" width="200"><?php if($score) echo $rm; ?></td>
                                                                                    </tr>
                                                                                    <?php
                                                                                }
                                                                                $n++;
                                                                            }
                                                                            ?>
																</tbody>
															</table>
															<?php
                                                                    if ($t_subjects == "0") {
                                                                        $av = '0';
                                                                    } else {
                                                                        $av = round($tscore/$t_subjects);
                                                                    }
                                                                    foreach ($grading as $grade) {
                                                                        if ($av >= $grade[2] && $av <= $grade[3]) {
                                                                            $grd_ = $grade[1];
                                                                            $rm_ = $grade[4];
                                                                        }
                                                                    }
                                                                    ?>
																<p> TOTAL SCORE <span class="badge bg-secondary rounded-pill"><?php if($tscore != 0) echo $tscore; else echo " - ";?></span> AVERAGE <span class="badge bg-secondary rounded-pill"><?php if($tscore != 0)  echo $av; else echo " - "; ?></span> GRADE <span class="badge bg-secondary rounded-pill"><?php if($tscore != 0)  echo $grd_; else echo " - ";  ?></span> REMARK <span class="badge bg-secondary rounded-pill"><?php if($tscore != 0)  echo strtoupper($rm_); else echo " - "; ?></span>
																	<!-- DIVISION <span
                                                                            class="badge bg-secondary rounded-pill"><?php echo get_division($subssss); ?></span>
                                                                        POINTS <span
                                                                            class="badge bg-secondary rounded-pill"><?php echo get_points($subssss); ?></span> --></p>
																<?php
                                                                    if($tscore != 0){
                                                                        ?>  
                                                                         <a target="_blank" href="student/save_pdf?term=<?php echo $_term[0]; ?>&currentClass=<?=urlencode($displayMessage); ?>"
                                                                         class="btn btn-primary btn-sm">DOWNLOAD</a>
                                                                        <?php
                                                                    }else{
                                                                        ?> 
                                                                         <a target="_blank" href="student/save_pdf?term=<?php echo $_term[0]; ?>&currentClass=<?=urlencode($displayMessage); ?>"
                                                                         class="btn btn-primary btn-sm">DOWNLOAD</a>
																		<?php
                                                                    }
                                                                    ?>
                                                                    
														</div>
														<?php
                                                            }
                                                            $t++;
                                                        }
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <?php
                                }else{
                                    ?>
                                      <!-- <p style ="font-size = '90';"> RESULTS NOT AVAILABLE, CHECK ANNOUNCEMENTS ONCE AVAILABLE</p> -->
                                <?php
                                }
                             //END FOREACH
                        } catch(PDOException $e) {
                            echo "Connection failed: " . $e->getMessage();
                        }
                    // }
                    ?>
            </div>
       
       
       <!-- thabiso nthako col-md-12 -->
        </div>
</main>
<script src="js/jquery-3.7.0.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/main.js"></script>
<script src="loader/waitMe.js"></script>
<script src="js/forms.js"></script>
<script type="text/javascript" src="js/plugins/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="js/plugins/dataTables.bootstrap.min.html"></script>
<script type="text/javascript">
$('#srmsTable').DataTable({
    "sort": false
});
</script>
<script src="js/sweetalert2@11.js"></script>
</body>

</html>