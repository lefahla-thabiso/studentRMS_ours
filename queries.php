 
 // list all subject student in class ,term with empty marks
 
-- Step 1: Build subject column part
SELECT GROUP_CONCAT(
    CONCAT('NULL AS `', subject_name, '`')
) INTO @columns
FROM (
    SELECT DISTINCT s.name AS subject_name
    FROM tbl_exam_results er
    JOIN tbl_subject_combinations sc ON er.subject_combination = sc.id
    JOIN tbl_subjects s ON sc.subject = s.id
    WHERE er.class = 12 AND er.term = 7
) AS subject_list;

-- Step 2: Assemble full query
SET @sql = CONCAT('
    SELECT
        stu.id AS student_id,
        stu.fname,
        stu.lname,
        cls.name AS class,
        trm.name AS term,
        ', @columns, '
    FROM tbl_students stu
    JOIN tbl_classes cls ON cls.id = 12
    JOIN tbl_terms trm ON trm.id = 7
    WHERE stu.id IN (
        SELECT DISTINCT er.student
        FROM tbl_exam_results er
        WHERE er.class = 12 AND er.term = 7
    )
');

-- Step 3: Execute
PREPARE stmt FROM @sql;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;


