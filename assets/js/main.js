// c:\xampp\htdocs\project\assets\js\main.js
// Main JavaScript - AJAX Dependent Dropdowns & Core Functions

$(document).ready(function() {
    
    // ============================================
    // DEPENDENT DROPDOWN: Board -> Standard -> Subject -> Chapter
    // ============================================
    
    // When board changes, load standards
    $(document).on('change', '#board_id, select[name="board_id"]', function() {
        const boardId = $(this).val();
        const standardSelect = $('#standard_id, select[name="standard_id"]');
        const subjectSelect = $('#subject_id, select[name="subject_id"]');
        const chapterSelect = $('#chapter_id, select[name="chapter_id"]');
        
        // Reset dependent dropdowns
        standardSelect.html('<option value="">Select Standard</option>').prop('disabled', true);
        subjectSelect.html('<option value="">Select Subject</option>').prop('disabled', true);
        chapterSelect.html('<option value="">Select Chapter</option>').prop('disabled', true);
        
        if (boardId) {
            // Show loading
            standardSelect.html('<option value="">Loading...</option>');
            
            // Fetch standards
            $.ajax({
                url: '/project/modules/ajax/get_standards.php',
                method: 'GET',
                data: { board_id: boardId },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        let options = '<option value="">Select Standard</option>';
                        response.data.forEach(function(item) {
                            options += `<option value="${item.standard_id}">${item.standard_name}</option>`;
                        });
                        standardSelect.html(options).prop('disabled', false);
                    } else {
                        standardSelect.html('<option value="">No standards available</option>');
                    }
                },
                error: function() {
                    standardSelect.html('<option value="">Error loading standards</option>');
                    showAlert('error', 'Failed to load standards');
                }
            });
        }
    });
    
    // When standard changes, load subjects
    $(document).on('change', '#standard_id, select[name="standard_id"]', function() {
        const standardId = $(this).val();
        const subjectSelect = $('#subject_id, select[name="subject_id"]');
        const chapterSelect = $('#chapter_id, select[name="chapter_id"]');
        
        // Reset dependent dropdowns
        subjectSelect.html('<option value="">Select Subject</option>').prop('disabled', true);
        chapterSelect.html('<option value="">Select Chapter</option>').prop('disabled', true);
        
        if (standardId) {
            // Show loading
            subjectSelect.html('<option value="">Loading...</option>');
            
            // Fetch subjects
            $.ajax({
                url: '/project/modules/ajax/get_subjects.php',
                method: 'GET',
                data: { standard_id: standardId },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        let options = '<option value="">Select Subject</option>';
                        response.data.forEach(function(item) {
                            options += `<option value="${item.subject_id}">${item.subject_name}</option>`;
                        });
                        subjectSelect.html(options).prop('disabled', false);
                    } else {
                        subjectSelect.html('<option value="">No subjects available</option>');
                    }
                },
                error: function() {
                    subjectSelect.html('<option value="">Error loading subjects</option>');
                    showAlert('error', 'Failed to load subjects');
                }
            });
        }
    });
    
    // When subject changes, load chapters
    $(document).on('change', '#subject_id, select[name="subject_id"]', function() {
        const subjectId = $(this).val();
        const chapterSelect = $('#chapter_id, select[name="chapter_id"]');
        
        // Reset dependent dropdown
        chapterSelect.html('<option value="">Select Chapter</option>').prop('disabled', true);
        
        if (subjectId) {
            // Show loading
            chapterSelect.html('<option value="">Loading...</option>');
            
            // Fetch chapters
            $.ajax({
                url: '/project/modules/ajax/get_chapters.php',
                method: 'GET',
                data: { subject_id: subjectId },
                dataType: 'json',
                success: function(response) {
                    if (response.success && response.data.length > 0) {
                        let options = '<option value="">Select Chapter</option>';
                        response.data.forEach(function(item) {
                            options += `<option value="${item.chapter_id}">${item.chapter_number ? item.chapter_number + ': ' : ''}${item.chapter_name}</option>`;
                        });
                        chapterSelect.html(options).prop('disabled', false);
                    } else {
                        chapterSelect.html('<option value="">No chapters available</option>');
                    }
                },
                error: function() {
                    chapterSelect.html('<option value="">Error loading chapters</option>');
                    showAlert('error', 'Failed to load chapters');
                }
            });
        }
    });
    
    // ============================================
    // FORM VALIDATION & SUBMISSION
    // ============================================
    
    // Add form validation class to required fields
    $('form').find('[required]').addClass('is-required');
    
    // Confirm before delete
    $(document).on('click', '.btn-delete, .delete-btn', function(e) {
        if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
            e.preventDefault();
            return false;
        }
    });
    
    // Auto-dismiss alerts after 5 seconds
    setTimeout(function() {
        $('.alert').fadeOut('slow');
    }, 5000);
    
    // ============================================
    // UTILITY FUNCTIONS
    // ============================================
    
    // Show alert message
    window.showAlert = function(type, message) {
        const alertHtml = `
            <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;
        
        // Find or create alert container
        let container = $('.alert-container');
        if (container.length === 0) {
            container = $('<div class="alert-container"></div>').prependTo('.container:first');
        }
        
        container.html(alertHtml);
        
        // Auto dismiss
        setTimeout(function() {
            container.find('.alert').fadeOut('slow');
        }, 5000);
    };
    
    // Loading overlay
    window.showLoading = function() {
        if ($('#loadingOverlay').length === 0) {
            $('body').append(`
                <div id="loadingOverlay" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);z-index:9999;display:flex;align-items:center;justify-content:center;">
                    <div class="spinner-border text-light" role="status">
                        <span class="visually-hidden">Loading...</span>
                    </div>
                </div>
            `);
        }
    };
    
    window.hideLoading = function() {
        $('#loadingOverlay').remove();
    };
    
    // Format number
    window.formatNumber = function(num) {
        return parseFloat(num).toFixed(2);
    };
    
    // Confirm action
    window.confirmAction = function(message) {
        return confirm(message || 'Are you sure?');
    };
    
    // ============================================
    // DATA TABLES (if needed)
    // ============================================
    
    // Initialize DataTables if available
    if ($.fn.DataTable) {
        $('.data-table').DataTable({
            "pageLength": 25,
            "ordering": true,
            "searching": true,
            "responsive": true
        });
    }
    
    // ============================================
    // IMAGE PREVIEW
    // ============================================
    
    $(document).on('change', 'input[type="file"].image-upload', function(e) {
        const file = e.target.files[0];
        const preview = $(this).data('preview');
        
        if (file && preview) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $(preview).attr('src', e.target.result).show();
            };
            reader.readAsDataURL(file);
        }
    });
    
    // ============================================
    // PRINT FUNCTIONALITY
    // ============================================
    
    $(document).on('click', '.btn-print', function() {
        window.print();
    });
    
    // ============================================
    // TOOLTIP & POPOVER INITIALIZATION
    // ============================================
    
    // Initialize Bootstrap tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Initialize Bootstrap popovers
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
});
