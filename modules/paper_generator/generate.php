<?php
// c:\xampp\htdocs\project\modules\paper_generator\generate.php
// Core Paper Generation Algorithm - The Engine

require_once __DIR__ . '/../../includes/functions.php';

/**
 * Paper Generator Class
 * Handles intelligent question paper generation based on blueprints
 */
class PaperGenerator {
    
    private $db;
    private $school_id;
    private $user_id;
    
    public function __construct($school_id, $user_id) {
        $this->db = getDB();
        $this->school_id = $school_id;
        $this->user_id = $user_id;
    }
    
    /**
     * Generate paper from blueprint
     * @param int $blueprint_id Blueprint ID
     * @param array $options Additional generation options
     * @return array ['success' => bool, 'paper_id' => int, 'message' => string]
     */
    public function generateFromBlueprint($blueprint_id, $options = []) {
        $result = ['success' => false, 'paper_id' => null, 'message' => ''];
        
        try {
            // Get blueprint details
            $blueprint = $this->getBlueprint($blueprint_id);
            if (!$blueprint) {
                $result['message'] = 'Blueprint not found.';
                return $result;
            }
            
            // Get blueprint sections
            $sections = $this->getBlueprintSections($blueprint_id);
            if (empty($sections)) {
                $result['message'] = 'No sections defined in blueprint.';
                return $result;
            }
            
            // Start transaction
            $this->db->beginTransaction();
            
            // Generate paper code
            $paper_code = $this->generatePaperCode();
            
            // Create paper record
            $paper_id = $this->createPaper($blueprint, $paper_code, $options);
            if (!$paper_id) {
                throw new Exception('Failed to create paper record.');
            }
            
            // Generate questions for each section
            $total_questions = 0;
            $question_order = 1;
            
            foreach ($sections as $section) {
                $questions = $this->selectQuestions($section);
                
                if (count($questions) < $section['number_of_questions']) {
                    throw new Exception("Insufficient questions for section: {$section['section_name']}. Required: {$section['number_of_questions']}, Available: " . count($questions));
                }
                
                // Add selected questions to paper
                foreach ($questions as $question) {
                    $this->addQuestionToPaper(
                        $paper_id,
                        $question['question_id'],
                        $section['section_name'],
                        $question_order,
                        $section['marks_per_question']
                    );
                    $question_order++;
                    $total_questions++;
                }
            }
            
            // Commit transaction
            $this->db->commit();
            
            // Log activity
            log_activity($this->user_id, 'generate_paper', 'paper', $paper_id, "Generated paper: {$paper_code} with {$total_questions} questions");
            
            $result['success'] = true;
            $result['paper_id'] = $paper_id;
            $result['paper_code'] = $paper_code;
            $result['total_questions'] = $total_questions;
            $result['message'] = 'Paper generated successfully!';
            
        } catch (Exception $e) {
            $this->db->rollback();
            $result['message'] = $e->getMessage();
        }
        
        return $result;
    }
    
    /**
     * Select questions based on section criteria
     * @param array $section Section configuration
     * @return array Selected questions
     */
    private function selectQuestions($section) {
        // Parse chapter IDs
        $chapter_ids = array_map('trim', explode(',', $section['chapter_ids']));
        
        // Build query with filters
        $placeholders = str_repeat('?,', count($chapter_ids) - 1) . '?';
        
        $query = "SELECT q.* 
                  FROM questions q 
                  WHERE q.chapter_id IN ($placeholders)
                  AND q.question_type = ?
                  AND q.difficulty_level = ?
                  AND q.school_id = ?
                  AND q.status = 'active'";
        
        $params = array_merge(
            $chapter_ids,
            [
                $section['question_type'],
                $section['difficulty_level'],
                $this->school_id
            ]
        );
        
        // Option to avoid frequently used questions
        if (PAPER_AVOID_DUPLICATES && PAPER_MAX_USAGE_COUNT > 0) {
            $query .= " AND q.usage_count < ?";
            $params[] = PAPER_MAX_USAGE_COUNT;
        }
        
        // Randomize if enabled
        if (PAPER_RANDOMIZE_QUESTIONS) {
            $query .= " ORDER BY RAND()";
        } else {
            $query .= " ORDER BY q.usage_count ASC, q.question_id ASC";
        }
        
        // Limit to required number
        $query .= " LIMIT ?";
        $params[] = (int)$section['number_of_questions'];
        
        return $this->db->select($query, $params);
    }
    
    /**
     * Get blueprint details
     * @param int $blueprint_id
     * @return array|false
     */
    private function getBlueprint($blueprint_id) {
        $query = "SELECT * FROM paper_blueprints 
                  WHERE blueprint_id = ? AND school_id = ? AND status = 'active'";
        return $this->db->selectOne($query, [$blueprint_id, $this->school_id]);
    }
    
    /**
     * Get blueprint sections
     * @param int $blueprint_id
     * @return array
     */
    private function getBlueprintSections($blueprint_id) {
        $query = "SELECT * FROM blueprint_sections 
                  WHERE blueprint_id = ? 
                  ORDER BY section_order ASC";
        return $this->db->select($query, [$blueprint_id]);
    }
    
    /**
     * Generate unique paper code
     * @return string
     */
    private function generatePaperCode() {
        do {
            $code = PAPER_CODE_PREFIX . '-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            $exists = $this->db->exists('generated_papers', 'paper_code = ?', [$code]);
        } while ($exists);
        
        return $code;
    }
    
    /**
     * Create paper record
     * @param array $blueprint Blueprint data
     * @param string $paper_code Generated paper code
     * @param array $options Additional options
     * @return int|false Paper ID
     */
    private function createPaper($blueprint, $paper_code, $options) {
        $paper_title = $options['paper_title'] ?? $blueprint['blueprint_name'];
        $instructions = $options['instructions'] ?? $blueprint['instructions'];
        $header_text = $options['header_text'] ?? '';
        $footer_text = $options['footer_text'] ?? '';
        $has_answer_key = $options['generate_answer_key'] ?? 0;
        $teacher_notes = $options['teacher_notes'] ?? '';
        
        $query = "INSERT INTO generated_papers 
                  (school_id, blueprint_id, generated_by, paper_title, paper_code, 
                   board_id, standard_id, subject_id, total_marks, duration_minutes, 
                   instructions, header_text, footer_text, has_answer_key, teacher_notes, status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'draft')";
        
        return $this->db->insert($query, [
            $this->school_id,
            $blueprint['blueprint_id'],
            $this->user_id,
            $paper_title,
            $paper_code,
            $blueprint['board_id'],
            $blueprint['standard_id'],
            $blueprint['subject_id'],
            $blueprint['total_marks'],
            $blueprint['duration_minutes'],
            $instructions,
            $header_text,
            $footer_text,
            $has_answer_key,
            $teacher_notes
        ]);
    }
    
    /**
     * Add question to paper
     * @param int $paper_id
     * @param int $question_id
     * @param string $section_name
     * @param int $question_order
     * @param float $marks
     * @return bool
     */
    private function addQuestionToPaper($paper_id, $question_id, $section_name, $question_order, $marks) {
        $query = "INSERT INTO generated_paper_questions 
                  (paper_id, question_id, section_name, question_order, marks) 
                  VALUES (?, ?, ?, ?, ?)";
        
        return $this->db->insert($query, [
            $paper_id,
            $question_id,
            $section_name,
            $question_order,
            $marks
        ]) !== false;
    }
    
    /**
     * Get paper with all questions
     * @param int $paper_id
     * @return array|null
     */
    public function getPaperWithQuestions($paper_id) {
        // Get paper details
        $query = "SELECT gp.*, 
                         b.board_name, 
                         s.standard_name, 
                         sub.subject_name,
                         sch.school_name
                  FROM generated_papers gp
                  INNER JOIN boards b ON gp.board_id = b.board_id
                  INNER JOIN standards s ON gp.standard_id = s.standard_id
                  INNER JOIN subjects sub ON gp.subject_id = sub.subject_id
                  INNER JOIN schools sch ON gp.school_id = sch.school_id
                  WHERE gp.paper_id = ? AND gp.school_id = ?";
        
        $paper = $this->db->selectOne($query, [$paper_id, $this->school_id]);
        
        if (!$paper) {
            return null;
        }
        
        // Get questions
        $query = "SELECT gpq.*, 
                         q.question_text, 
                         q.question_image, 
                         q.question_type,
                         q.option_a, 
                         q.option_b, 
                         q.option_c, 
                         q.option_d,
                         ch.chapter_name
                  FROM generated_paper_questions gpq
                  INNER JOIN questions q ON gpq.question_id = q.question_id
                  INNER JOIN chapters ch ON q.chapter_id = ch.chapter_id
                  WHERE gpq.paper_id = ?
                  ORDER BY gpq.question_order ASC";
        
        $paper['questions'] = $this->db->select($query, [$paper_id]);
        
        // Group questions by section
        $paper['sections'] = [];
        foreach ($paper['questions'] as $question) {
            $section = $question['section_name'];
            if (!isset($paper['sections'][$section])) {
                $paper['sections'][$section] = [];
            }
            $paper['sections'][$section][] = $question;
        }
        
        return $paper;
    }
    
    /**
     * Regenerate paper (replace specific questions)
     * @param int $paper_id
     * @param array $question_ids_to_replace
     * @return array Result
     */
    public function regenerateQuestions($paper_id, $question_ids_to_replace) {
        // Implementation for regenerating specific questions
        // This allows teachers to replace questions they don't like
        $result = ['success' => false, 'message' => ''];
        
        // TODO: Implement logic to replace specific questions while maintaining section requirements
        
        return $result;
    }
}

/**
 * Helper function to create paper generator instance
 * @return PaperGenerator
 */
function getPaperGenerator() {
    return new PaperGenerator(get_school_id(), get_user_id());
}
