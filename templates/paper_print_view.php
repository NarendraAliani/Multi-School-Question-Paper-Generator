<?php
// c:\xampp\htdocs\project\templates\paper_print_view.php
// A4 Printable Question Paper Template

// This file should be included from paper preview/print page
// Expected variables: $paper (array with paper data and questions)

if (!isset($paper)) {
    die('Paper data not provided');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($paper['paper_title']); ?> - Print View</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        /* A4 Print Styles */
        @page {
            size: A4;
            margin: 15mm;
        }
        
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.6;
            color: #000;
            background: white;
        }
        
        .paper-container {
            max-width: 210mm;
            margin: 0 auto;
            padding: 20px;
            background: white;
        }
        
        /* Header */
        .paper-header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 3px double #000;
        }
        
        .school-name {
            font-size: 20pt;
            font-weight: bold;
            margin-bottom: 5px;
        }
        
        .paper-title {
            font-size: 16pt;
            font-weight: bold;
            margin: 10px 0;
            text-transform: uppercase;
        }
        
        .paper-info {
            display: flex;
            justify-content: space-between;
            margin: 15px 0;
            font-size: 11pt;
        }
        
        .paper-info-item {
            flex: 1;
        }
        
        /* Instructions */
        .instructions {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        
        .instructions h3 {
            font-size: 13pt;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .instructions ul {
            margin: 0;
            padding-left: 20px;
        }
        
        .instructions li {
            margin-bottom: 5px;
        }
        
        /* Section Headers */
        .section-header {
            font-size: 14pt;
            font-weight: bold;
            margin-top: 25px;
            margin-bottom: 15px;
            padding: 8px 12px;
            background: #e9ecef;
            border-left: 4px solid #007bff;
        }
        
        /* Questions */
        .question {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        
        .question-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 8px;
        }
        
        .question-number {
            font-weight: bold;
            font-size: 11pt;
            margin-right: 8px;
        }
        
        .question-marks {
            font-weight: bold;
            white-space: nowrap;
            font-size: 10pt;
        }
        
        .question-text {
            font-size: 11pt;
            line-height: 1.8;
            margin-left: 0;
        }
        
        /* MCQ Options */
        .mcq-options {
            margin: 10px 0 10px 20px;
            font-size: 11pt;
        }
        
        .mcq-option {
            margin-bottom: 8px;
            line-height: 1.6;
        }
        
        /* Question Image */
        .question-image {
            max-width: 400px;
            max-height: 250px;
            margin: 10px 0;
            border: 1px solid #ddd;
            padding: 5px;
        }
        
        /* Answer Space */
        .answer-space {
            border-top: 1px dotted #999;
            min-height: 80px;
            margin-top: 10px;
        }
        
        .answer-space-long {
            min-height: 150px;
        }
        
        /* Footer */
        .paper-footer {
            margin-top: 40px;
            padding-top: 15px;
            border-top: 2px solid #000;
            text-align: center;
            font-size: 10pt;
        }
        
        /* Page Break */
        .page-break {
            page-break-after: always;
        }
        
        /* Print Specific */
        @media print {
            body {
                margin: 0;
                padding: 0;
            }
            
            .paper-container {
                max-width: none;
                padding: 0;
            }
            
            .no-print {
                display: none !important;
            }
            
            .instructions {
                background: white;
                border: 1px solid #000;
            }
            
            .section-header {
                background: white;
                border: 1px solid #000;
            }
        }
        
        /* No Print Elements */
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1000;
        }
        
        @media print {
            .print-button {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Print Button -->
    <div class="print-button no-print">
        <button onclick="window.print()" class="btn btn-primary">
            <i class="fas fa-print"></i> Print Paper
        </button>
        <button onclick="window.close()" class="btn btn-secondary">
            <i class="fas fa-times"></i> Close
        </button>
    </div>

    <div class="paper-container">
        <!-- Paper Header -->
        <div class="paper-header">
            <div class="school-name"><?php echo e($paper['school_name']); ?></div>
            <div class="paper-title"><?php echo e($paper['paper_title']); ?></div>
            
            <?php if (!empty($paper['header_text'])): ?>
                <div style="font-size: 10pt; margin-top: 10px;">
                    <?php echo nl2br(e($paper['header_text'])); ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Paper Information -->
        <div class="paper-info">
            <div class="paper-info-item">
                <strong>Class:</strong> <?php echo e($paper['standard_name']); ?>
            </div>
            <div class="paper-info-item">
                <strong>Subject:</strong> <?php echo e($paper['subject_name']); ?>
            </div>
            <div class="paper-info-item">
                <strong>Time:</strong> <?php echo $paper['duration_minutes']; ?> minutes
            </div>
            <div class="paper-info-item">
                <strong>Total Marks:</strong> <?php echo $paper['total_marks']; ?>
            </div>
        </div>

        <div class="paper-info">
            <div class="paper-info-item">
                <strong>Board:</strong> <?php echo e($paper['board_name']); ?>
            </div>
            <div class="paper-info-item">
                <strong>Paper Code:</strong> <?php echo e($paper['paper_code']); ?>
            </div>
            <div class="paper-info-item">
                <strong>Date:</strong> _______________
            </div>
        </div>

        <!-- Candidate Information -->
        <div style="margin: 20px 0; padding: 15px; border: 2px solid #000;">
            <div style="display: flex; justify-content: space-between;">
                <div><strong>Name:</strong> ____________________________</div>
                <div><strong>Roll No:</strong> ____________________________</div>
            </div>
        </div>

        <!-- Instructions -->
        <?php if (!empty($paper['instructions'])): ?>
        <div class="instructions">
            <h3>General Instructions:</h3>
            <?php echo nl2br(e($paper['instructions'])); ?>
        </div>
        <?php else: ?>
        <div class="instructions">
            <h3>General Instructions:</h3>
            <ul>
                <li>All questions are compulsory.</li>
                <li>Read each question carefully before answering.</li>
                <li>Marks are indicated against each question.</li>
                <li>Write your answers in the space provided.</li>
                <li>Use of calculators is not permitted.</li>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Questions by Section -->
        <?php 
        $question_counter = 1;
        foreach ($paper['sections'] as $section_name => $questions): 
        ?>
            <div class="section-header">
                <?php echo e($section_name); ?>
                (<?php echo count($questions); ?> Questions)
            </div>

            <?php foreach ($questions as $question): ?>
                <div class="question">
                    <div class="question-header">
                        <div style="flex: 1;">
                            <span class="question-number">Q<?php echo $question_counter; ?>.</span>
                            <span class="question-text"><?php echo e($question['question_text']); ?></span>
                        </div>
                        <div class="question-marks">[<?php echo $question['marks']; ?> Mark<?php echo $question['marks'] > 1 ? 's' : ''; ?>]</div>
                    </div>

                    <!-- Question Image -->
                    <?php if (!empty($question['question_image'])): ?>
                        <div>
                            <img src="<?php echo QUESTIONS_UPLOAD_URL . '/' . $question['question_image']; ?>" 
                                 alt="Question Image" 
                                 class="question-image">
                        </div>
                    <?php endif; ?>

                    <!-- MCQ Options -->
                    <?php if ($question['question_type'] === 'mcq'): ?>
                        <div class="mcq-options">
                            <?php if (!empty($question['option_a'])): ?>
                                <div class="mcq-option">(A) <?php echo e($question['option_a']); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($question['option_b'])): ?>
                                <div class="mcq-option">(B) <?php echo e($question['option_b']); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($question['option_c'])): ?>
                                <div class="mcq-option">(C) <?php echo e($question['option_c']); ?></div>
                            <?php endif; ?>
                            <?php if (!empty($question['option_d'])): ?>
                                <div class="mcq-option">(D) <?php echo e($question['option_d']); ?></div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Answer Space -->
                    <?php if ($question['question_type'] !== 'mcq'): ?>
                        <div class="answer-space <?php echo $question['question_type'] === 'long_answer' ? 'answer-space-long' : ''; ?>">
                            <!-- Answer area -->
                        </div>
                    <?php endif; ?>
                </div>
                <?php $question_counter++; ?>
            <?php endforeach; ?>
        <?php endforeach; ?>

        <!-- Footer -->
        <div class="paper-footer">
            <?php if (!empty($paper['footer_text'])): ?>
                <div><?php echo nl2br(e($paper['footer_text'])); ?></div>
            <?php else: ?>
                <div>*** END OF QUESTION PAPER ***</div>
            <?php endif; ?>
            <div style="margin-top: 10px;">
                <small>Generated by <?php echo APP_NAME; ?> | <?php echo date('d-m-Y'); ?></small>
            </div>
        </div>
    </div>

    <script>
        // Auto print on load (optional)
        // window.onload = function() { window.print(); };
    </script>
</body>
</html>
