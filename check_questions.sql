-- ============================================
-- SQL Queries to Check Question Bank Status
-- ============================================

-- 1. Count questions by type
SELECT 
    question_type,
    COUNT(*) as total_questions
FROM questions
WHERE status = 'active'
GROUP BY question_type
ORDER BY question_type;

-- 2. Count questions by difficulty level
SELECT 
    difficulty_level,
    COUNT(*) as total_questions
FROM questions
WHERE status = 'active'
GROUP BY difficulty_level
ORDER BY difficulty_level;

-- 3. Count questions by type AND difficulty (most useful)
SELECT 
    question_type,
    difficulty_level,
    COUNT(*) as total_questions
FROM questions
WHERE status = 'active'
GROUP BY question_type, difficulty_level
ORDER BY question_type, difficulty_level;

-- 4. Detailed breakdown by chapter
SELECT 
    b.board_name,
    std.standard_name,
    sub.subject_name,
    ch.chapter_name,
    q.question_type,
    q.difficulty_level,
    COUNT(*) as total_questions
FROM questions q
INNER JOIN chapters ch ON q.chapter_id = ch.chapter_id
INNER JOIN subjects sub ON ch.subject_id = sub.subject_id
INNER JOIN standards std ON sub.standard_id = std.standard_id
INNER JOIN boards b ON std.board_id = b.board_id
WHERE q.status = 'active'
GROUP BY b.board_name, std.standard_name, sub.subject_name, ch.chapter_name, q.question_type, q.difficulty_level
ORDER BY b.board_name, std.standard_name, sub.subject_name, ch.chapter_name;

-- 5. Check questions for specific blueprint (Replace blueprint_id = 4 with your ID)
SELECT 
    bs.section_name,
    bs.question_type,
    bs.difficulty_level,
    bs.number_of_questions as required,
    bs.chapter_ids,
    COUNT(q.question_id) as available_questions
FROM blueprint_sections bs
LEFT JOIN questions q ON 
    FIND_IN_SET(q.chapter_id, bs.chapter_ids) > 0
    AND q.question_type = bs.question_type
    AND q.difficulty_level = bs.difficulty_level
    AND q.status = 'active'
WHERE bs.blueprint_id = 4
GROUP BY bs.section_id, bs.section_name, bs.question_type, bs.difficulty_level, bs.number_of_questions, bs.chapter_ids;

-- 6. Find which chapters have NO questions
SELECT 
    ch.chapter_id,
    ch.chapter_name,
    sub.subject_name,
    COUNT(q.question_id) as question_count
FROM chapters ch
INNER JOIN subjects sub ON ch.subject_id = sub.subject_id
LEFT JOIN questions q ON ch.chapter_id = q.chapter_id AND q.status = 'active'
WHERE ch.status = 'active'
GROUP BY ch.chapter_id, ch.chapter_name, sub.subject_name
HAVING question_count = 0
ORDER BY sub.subject_name, ch.chapter_name;

-- 7. Quick check: Total questions vs Blueprint requirements
SELECT 
    'Total Active Questions' as description,
    COUNT(*) as count
FROM questions
WHERE status = 'active'
UNION ALL
SELECT 
    CONCAT('Blueprint ', blueprint_id, ' - ', blueprint_name) as description,
    SUM(number_of_questions) as count
FROM paper_blueprints bp
JOIN blueprint_sections bs ON bp.blueprint_id = bs.blueprint_id
WHERE bp.status = 'active'
GROUP BY bp.blueprint_id, bp.blueprint_name;
