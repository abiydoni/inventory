<!-- --- FILE: views/layouts/footer.php --- -->
  </div>
</div>
            </main>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Mobile sidebar toggle
        function toggleMobileSidebar() {
            const sidebar = document.getElementById('mobileSidebar');
            sidebar.classList.toggle('hidden');
        }

        // Close mobile sidebar when clicking outside
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('mobileSidebar');
            if (!sidebar.contains(event.target) && !event.target.closest('[onclick*="toggleMobileSidebar"]')) {
                sidebar.classList.add('hidden');
            }
        });

        // Add smooth scrolling
        document.addEventListener('DOMContentLoaded', function() {
            // Smooth scroll for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Add loading states to buttons
            document.querySelectorAll('button[type="submit"]').forEach(button => {
                button.addEventListener('click', function() {
                    if (!this.disabled) {
                        this.disabled = true;
                        const originalText = this.innerHTML;
                        this.innerHTML = '<i class="bx bx-loader-alt bx-spin mr-2"></i>Loading...';
                        
                        // Re-enable after 3 seconds as fallback
                        setTimeout(() => {
                            this.disabled = false;
                            this.innerHTML = originalText;
                        }, 3000);
                    }
                });
            });
        });

        // Global error handler
        window.addEventListener('error', function(e) {
            console.error('Global error:', e.error);
            Swal.fire('Error', 'Terjadi kesalahan sistem. Silakan coba lagi.', 'error');
        });

        // Global unhandled promise rejection handler
        window.addEventListener('unhandledrejection', function(e) {
            console.error('Unhandled promise rejection:', e.reason);
            Swal.fire('Error', 'Terjadi kesalahan sistem. Silakan coba lagi.', 'error');
        });
    </script>

    <!-- Custom page scripts -->
    <?php if (isset($pageScripts)): ?>
        <?php foreach ($pageScripts as $script): ?>
            <script src="<?php echo $script; ?>"></script>
        <?php endforeach; ?>
    <?php endif; ?>
</body>
</html>
