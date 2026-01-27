<?php
// c:\xampp\htdocs\project\includes\footer.php
// Common HTML Footer
?>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?php echo assets_url('js/main.js'); ?>"></script>
    
    <?php if (isset($_additional_js)): ?>
        <?php echo $_additional_js; ?>
    <?php endif; ?>
</body>
</html>
