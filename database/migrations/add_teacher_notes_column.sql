-- Add teacher_notes column to generated_papers table
ALTER TABLE generated_papers 
ADD COLUMN teacher_notes TEXT AFTER has_answer_key;
