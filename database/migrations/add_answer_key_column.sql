-- Add answer key support to generated_papers table
ALTER TABLE generated_papers 
ADD COLUMN has_answer_key TINYINT(1) DEFAULT 0 AFTER footer_text;
