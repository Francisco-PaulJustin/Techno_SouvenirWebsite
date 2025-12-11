        </div><!-- /.admin-content -->
    </div><!-- /.admin-wrapper -->
    
    <script src="../assets/js/popup.js"></script>
    <script>
    // Sidebar toggle functionality (mobile and desktop collapse)
    document.addEventListener('DOMContentLoaded', function() {
        const sidebarToggle = document.querySelector('.sidebar-toggle:not(.sidebar-toggle-inline)');
        const sidebarToggleInline = document.querySelector('.sidebar-toggle-inline');
        const sidebar = document.querySelector('.admin-sidebar');
        const overlay = document.querySelector('.sidebar-overlay');
        const isMobile = window.matchMedia('(max-width: 992px)').matches;
        
        // Function to toggle sidebar
        function toggleSidebar(isMobileDevice) {
            if (isMobileDevice) {
                // Mobile: Toggle open/close with overlay
                sidebar.classList.toggle('open');
                document.body.classList.toggle('sidebar-open');
                
                // Update aria-expanded
                const isExpanded = sidebar.classList.contains('open');
                if (sidebarToggle) {
                    sidebarToggle.setAttribute('aria-expanded', isExpanded);
                }
            } else {
                // Desktop: Toggle collapsed/expanded
                sidebar.classList.toggle('collapsed');
                document.body.classList.toggle('sidebar-collapsed');
                
                // Save preference to localStorage
                if (sidebar.classList.contains('collapsed')) {
                    localStorage.setItem('adminSidebarCollapsed', 'true');
                } else {
                    localStorage.setItem('adminSidebarCollapsed', 'false');
                }
                
                // Update inline toggle aria-expanded
                if (sidebarToggleInline) {
                    sidebarToggleInline.setAttribute('aria-expanded', !sidebar.classList.contains('collapsed'));
                }
            }
        }
        
        // Mobile toggle button
        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleSidebar(true);
            });
        }
        
        // Desktop inline toggle button
        if (sidebarToggleInline && sidebar) {
            sidebarToggleInline.addEventListener('click', function(e) {
                e.stopPropagation();
                toggleSidebar(false);
            });
        }
        
        // Close sidebar when clicking overlay (mobile only)
        if (overlay) {
            overlay.addEventListener('click', function() {
                if (isMobile && sidebar) {
                    sidebar.classList.remove('open');
                    document.body.classList.remove('sidebar-open');
                    sidebarToggle.setAttribute('aria-expanded', 'false');
                }
            });
        }
        
        // Restore collapsed state on desktop (from localStorage)
        if (!isMobile && sidebar) {
            const wasCollapsed = localStorage.getItem('adminSidebarCollapsed') === 'true';
            if (wasCollapsed) {
                sidebar.classList.add('collapsed');
                document.body.classList.add('sidebar-collapsed');
            }
        }
        
        // Handle window resize
        window.addEventListener('resize', function() {
            const newIsMobile = window.matchMedia('(max-width: 992px)').matches;
            if (newIsMobile !== isMobile) {
                // Reset sidebar state when crossing breakpoint
                if (newIsMobile) {
                    sidebar.classList.remove('collapsed');
                    document.body.classList.remove('sidebar-collapsed');
                } else {
                    sidebar.classList.remove('open');
                    document.body.classList.remove('sidebar-open');
                }
            }
        });
        
        // Close mobile sidebar when clicking a link
        if (isMobile && sidebar) {
            const sidebarLinks = sidebar.querySelectorAll('a');
            sidebarLinks.forEach(function(link) {
                link.addEventListener('click', function() {
                    sidebar.classList.remove('open');
                    document.body.classList.remove('sidebar-open');
                    if (sidebarToggle) {
                        sidebarToggle.setAttribute('aria-expanded', 'false');
                    }
                });
            });
        }
        
        
        // Auto-dismiss alerts after 5 seconds
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            setTimeout(function() {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-10px)';
                setTimeout(function() {
                    alert.style.display = 'none';
                }, 300);
            }, 5000);
        });
    });
    </script>
</body>
</html>
