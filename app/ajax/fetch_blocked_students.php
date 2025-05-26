<?php
session_start();
chdir('../../');
require_once('db/config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

$id = $_POST['id'];

try {
$conn = new PDO('mysql:host='.DBHost.';dbname='.DBName.';charset='.DBCharset.';collation='.DBCollation.';prefix='.DBPrefix.'', DBUser, DBPass);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

$stmt = $conn->prepare("SELECT * FROM tbl_block_student WHERE id = ?");
$stmt->execute([$id]);
$result = $stmt->fetchAll();

foreach($result as $row)
{
?>

<form class="app_frm" method="POST" autocomplete="OFF" action="admin/core/update_student_blocked">
								
							 
								<div class="mb-3">
									<label class="form-label">Select Class</label>
									<select class="form-control select2" name="studentClass" required style="width: 100%;">
									<option value="" selected disabled> Select One</option>
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

										$stmt = $conn->prepare("SELECT * FROM tbl_classes");
										$stmt->execute();
										$studenClass = $stmt->fetchAll();
										
    									$stmt = $conn->prepare("SELECT * FROM tbl_students");
										$stmt->execute();
										$students = $stmt->fetchAll();

										// $thisClass = " ";
										foreach ($studenClass as $thisClass) { ?>
											<option value="<?php echo $thisClass[0]; ?>"><?php echo $thisClass[1]; ?> </option> 
											<!-- $thisClass =  -->
									<!-- <?php }
									// } catch (PDOException $e) {
										// echo "Connection failed: " . $e->getMessage();
									// } ?> -->
									</select>
									</div>

									<div class="mb-2">
									<label class="form-label">Select Student</label>
									<select class="form-control select2" name="selected_student" required>
										<option value="" selected disabled>Select One</option>
										<?php

										
										foreach ($students as $studentDetails) {
											$student_id = $studentDetails[0];
											$student_name  = $studentDetails[1] . " " . $studentDetails[2] . " " . $studentDetails[3];
												echo "<option value='$student_id'>$student_name</option>";
											}
										// }
										} catch (PDOException $e) {
										echo "Connection failed: " . $e->getMessage();
									} 
									?>
									</select>
									</div>
									
								<div class="mb-2">
									<label class="form-label" sytle="margin-top: 12px;"> <B> Reason </B> </label>
								</div>
								<div class="mb-3">
									<!-- <label class="form-label">Reason</label> -->
									<textarea name="reason" id="summernote" required></textarea>
									<script>
									$('#summernote').summernote({
										tabsize: 2,
										height: 120,
										fontNames: ['Comic Sans MS']
									});
									</script>
								</div>
								<button type="submit" name="submit" value="1" class="btn btn-primary app_btn">Add</button>
								<button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
							</form>

<?php
}
}catch(PDOException $e)
{
echo "Connection failed: " . $e->getMessage();
}

}
?>
