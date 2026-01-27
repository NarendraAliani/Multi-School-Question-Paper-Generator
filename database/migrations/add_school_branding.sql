-- Add branding fields to schools table
ALTER TABLE schools 
ADD COLUMN paper_header_text VARCHAR(500) DEFAULT NULL AFTER address,
ADD COLUMN paper_footer_text VARCHAR(500) DEFAULT NULL AFTER paper_header_text,
ADD COLUMN watermark_enabled TINYINT(1) DEFAULT 0 AFTER paper_footer_text,
ADD COLUMN watermark_text VARCHAR(100) DEFAULT 'CONFIDENTIAL' AFTER watermark_enabled;