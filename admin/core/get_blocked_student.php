
<?php
require_once "../../db/config.php";

if (isset($_POST['id'])) {
    $id = $_POST['id'];

    // var_dump($id);

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
    $stmt = $conn->prepare("SELECT * FROM tbl_block_student WHERE id = ?");
    $stmt->execute([$id]);
    $data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($data) {
        ?>
        <form method="POST" action="admin/core/update_student_blocked.php">
            <input type="hidden" name="id" value="<?= $data['id']; ?>">

            <div class="mb-3">
                <label class="form-label">Student</label>
                <input type="text" readonly class="form-control"
                    value="<?= $data['firstName'] . ' ' . $data['lastName']; ?>">
            </div>

            <div class="mb-3">
                <label class="form-label">Reason</label>
                <textarea name="reason" class="form-control" rows="4" required><?= htmlspecialchars($data['reason']); ?></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Update</button>
            <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
        </form>
        <?php
    } else {
        echo "<p class='text-danger'>Student not found.</p>";
    }
} else {
    echo "<p class='text-danger'>No student ID provided.</p>";
}
?>
