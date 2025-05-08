<?php
chdir("../");
session_start();

require_once "db/config.php";
require_once "const/school.php";
require_once "const/check_session.php";
require_once "tcpdf/tcpdf.php";
require_once "const/calculations.php";

if ($res != "1" || $level != "3" || !isset($_GET["term"])) {
    header("location:../");
    exit;
}

$term = $_GET["term"];

try {
    $conn = new PDO(
        "mysql:host=" . DBHost . ";dbname=" . DBName . ";charset=" . DBCharset,
        DBUser,
        DBPass
    );
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $grading = $conn->query("SELECT * FROM tbl_grade_system")->fetchAll();

    $stmt = $conn->prepare("SELECT * FROM tbl_terms WHERE id = ?");
    $stmt->execute([$term]);
    $result = $stmt->fetchAll();

    if (count($result) < 1) {
        header("location:./");
        exit;
    }

    $title = $result[0][1] . " Examination Report Card";

    $stmt = $conn->prepare("
        SELECT * FROM tbl_exam_results
        LEFT JOIN tbl_classes ON tbl_exam_results.class = tbl_classes.id
        WHERE tbl_exam_results.term = ? AND tbl_exam_results.student = ?
    ");
    $stmt->execute([$term, $account_id]);
    $result2 = $stmt->fetchAll();

    if (count($result2) < 1) {
        header("location:./");
        exit;
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
    exit;
}

$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, "UTF-8", false);
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor(WBName);
$pdf->SetTitle($title);
$pdf->SetSubject($title);
$pdf->SetKeywords("SRMS", WBName);
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);
$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
$pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

if (@file_exists(dirname(__FILE__) . "/lang/eng.php")) {
    require_once dirname(__FILE__) . "/lang/eng.php";
    $pdf->setLanguageArray($l);
}

$pdf->setFontSubsetting(true);
$pdf->SetFont("helvetica", "", 14, "", true);
$pdf->AddPage();

$pdf->setTextShadow([
    "enabled" => true,
    "depth_w" => 0.2,
    "depth_h" => 0.2,
    "color" => [196, 196, 196],
    "opacity" => 1,
    "blend_mode" => "Normal",
]);

$th_img = ($img == "DEFAULT")
    ? '<img width="90" height="90" src="images/students/' . $gender . '.png">'
    : '<img width="90" height="90" src="images/students/' . $img . '">';

$html = '
    <table width="100%">
        <tr>
            <td width="15%"><img src="images/logo/' . WBLogo . '"></td>
            <td width="70%" style="text-align:center;">
                <h5><b style="font-size:18px;">' . WBName . '</b><br>
                Student Examination Report Card<br>' . $result[0][1] . '<br>' . $result2[0][7] . '</h5>
            </td>
            <td width="15%">' . $th_img . '</td>
        </tr>
    </table>';

$pdf->writeHTMLCell(0, 0, "", "", $html, 0, 1, 0, true, "", true);
$pdf->cell(0, 0, "", 0, 1, "C");

$html = '<b style="font-size:10pt;">Student Profile</b>';
$pdf->writeHTMLCell(0, 0, "", "", $html, 0, 1, 0, true, "", true);

$html = '
    <table cellpadding="3" style="margin-bottom:10px; font-size:10px; border-collapse: collapse;" width="100%">
        <tr>
            <td width="20%"><b>REGISTRATION NUMBER</b></td>
            <td width="80%">' . $account_id . '</td>
        </tr>
        <tr>
            <td><b>STUDENT NAME</b></td>
            <td colspan="5">' . $fname . ' ' . $mname . ' ' . $lname . '</td>
        </tr>
        <tr>
            <td><b>EXAMINATION TERM</b></td>
            <td colspan="5">' . $result[0][1] . '</td>
        </tr>
        <tr>
            <td><b>EXAMINATION CLASS</b></td>
            <td colspan="5">' . $result2[0][7] . '</td>
        </tr>
    </table>';

$pdf->writeHTMLCell(0, 0, "", "", $html, 0, 1, 0, true, "", true);
$pdf->cell(0, 0, "", 0, 1, "C");

$html = '<b style="font-size:10pt;">Examination Results</b>';
$pdf->writeHTMLCell(0, 0, "", "", $html, 0, 1, 0, true, "", true);

$htmls = '
    <table cellpadding="3" border="1" style="margin-bottom:10px; font-size:10px; border-collapse: collapse;" width="100%">
        <tr>
            <th width="5%"><b>#</b></th>
            <th width="35%"><b>SUBJECT</b></th>
            <th width="20%"><b>SCORE</b></th>
            <th width="20%"><b>GRADE</b></th>
            <th width="20%"><b>REMARK</b></th>
        </tr>';

$stmt = $conn->prepare("
    SELECT * FROM tbl_subject_combinations 
    LEFT JOIN tbl_subjects ON tbl_subject_combinations.subject = tbl_subjects.id
");
$stmt->execute();
$subjects = $stmt->fetchAll();

$number = 1;
$tscore = 0;
$t_subjects = 0;

foreach ($subjects as $row) {
    $class_list = unserialize($row[1]);
    if (in_array($result2[0][6], $class_list)) {
        $t_subjects++;
        $score = 0;
        $final_grade = "N/A";
        $remark = "N/A";

        $stmt = $conn->prepare("
            SELECT * FROM tbl_exam_results 
            WHERE class = ? AND subject_combination = ? AND term = ? AND student = ?
        ");
        $stmt->execute([$class, $row[0], $term, $account_id]);
        $ex_result = $stmt->fetchAll();

        if (!empty($ex_result[0][5])) {
            $score = $ex_result[0][5];
        }

        $tscore += $score;

        foreach ($grading as $grade) {
            if ($score >= $grade[2] && $score <= $grade[3]) {
                $final_grade = $grade[1];
                $remark = $grade[4];
            }
        }

        $htmls .= "
            <tr>
                <td align='center'>{$number}</td>
                <td>{$row[6]}</td>
                <td align='center'>{$score}%</td>
                <td align='center'>{$final_grade}</td>
                <td align='center'>{$remark}</td>
            </tr>";
        $number++;
    }
}

$htmls .= "</table>";
$pdf->writeHTMLCell(0, 0, "", "", $htmls, 0, 1, 0, true, "", true);

$average = ($t_subjects > 0) ? round($tscore / $t_subjects) : 0;

foreach ($grading as $grade) {
    if ($average >= $grade[2] && $average <= $grade[3]) {
        $grd_ = $grade[1];
        $rm_ = $grade[4];
    }
}

$html = '
    <table border="1" cellpadding="3" style="margin-bottom:10px; font-size:10px; border-collapse: collapse;" width="100%">
        <tr>
            <td><b>TOTAL SCORE</b></td>
            <td><b>AVERAGE</b></td>
            <td><b>GRADE</b></td>
            <td><b>REMARK</b></td>
        </tr>
        <tr>
            <td align="center">' . $tscore . '</td>
            <td align="center">' . $average . '</td>
            <td align="center">' . $grd_ . '</td>
            <td align="center">' . $rm_ . '</td>
        </tr>
    </table>';
$pdf->writeHTMLCell(0, 0, "", "", $html, 0, 1, 0, true, "", true);

$pdf->cell(0, 0, "", 0, 2, "C");
$html = '<b style="font-size:10pt;">Grading System</b>';
$pdf->writeHTMLCell(0, 0, "", "", $html, 0, 1, 0, true, "", true);

$html = '<table border="1" cellpadding="3" style="margin-bottom:10px; font-size:10px; border-collapse: collapse;" width="100%">';

// Grades
$html .= "<tr>";
foreach ($grading as $value) {
    $html .= '<td align="center">' . $value[1] . '</td>';
}
$html .= "</tr>";

// Ranges
$html .= "<tr>";
foreach ($grading as $value) {
    $html .= '<td align="center">' . $value[2] . '% - ' . $value[3] . '%</td>';
}
$html .= "</tr>";

// Remarks
$html .= "<tr>";
foreach ($grading as $value) {
    $html .= '<td align="center">' . $value[4] . '</td>';
}
$html .= "</tr>";

$html .= "</table>";
$pdf->writeHTMLCell(0, 0, "", "", $html, 0, 1, 0, true, "", true);

ob_end_clean();
$pdf->Output($title . ".pdf", "I");
