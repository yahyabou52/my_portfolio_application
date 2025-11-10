/**
 * Modern Admin Dashboard JavaScript
 * Handles sidebar management, responsive behavior, and UI interactions
 */

// Modern Sidebar Management Class
class ModernSidebarManager {
    constructor() {
        this.sidebar = document.getElementById('modernSidebar');
        this.mainContent = document.getElementById('mainContent');
        this.overlay = document.getElementById('sidebarOverlay');
        this.toggleBtn = document.getElementById('sidebarToggle');
        this.isDesktop = window.innerWidth >= 992;
        this.isSidebarOpen = this.isDesktop;
        this.sidebarState = localStorage.getItem('sidebarState') || 'open';

        this.init();
    }

    init() {
        // Set initial state based on localStorage
        if (this.isDesktop && this.sidebarState === 'closed') {
            this.isSidebarOpen = false;
        }

        // Toggle button event
        if (this.toggleBtn) {
            this.toggleBtn.addEventListener('click', (e) => {
                e.preventDefault();
                this.toggle();
            });
        }

        // Overlay click to close on mobile
        if (this.overlay) {
            this.overlay.addEventListener('click', () => {
                if (!this.isDesktop) {
                    this.close();
                }
            });
        }

        // Handle window resize with debouncing
        let resizeTimeout;
        window.addEventListener('resize', () => {
            clearTimeout(resizeTimeout);
            resizeTimeout = setTimeout(() => this.handleResize(), 150);
        });

        // ESC key to close sidebar on mobile
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !this.isDesktop && this.isSidebarOpen) {
                this.close();
            }
        });

        // Prevent body scroll when sidebar is open on mobile
        this.handleBodyScroll();

        // Initial setup
        this.updateLayout();
    }

    toggle() {
        if (this.isSidebarOpen) {
            this.close();
        } else {
            this.open();
        }
    }

    open() {
        this.isSidebarOpen = true;
        if (this.isDesktop) {
            localStorage.setItem('sidebarState', 'open');
        }
        this.updateLayout();
    }

    close() {
        this.isSidebarOpen = false;
        if (this.isDesktop) {
            localStorage.setItem('sidebarState', 'closed');
        }
        this.updateLayout();
    }

    updateLayout() {
        if (!this.sidebar || !this.mainContent || !this.overlay) return;

        // Remove all existing classes first
        this.sidebar.classList.remove('sidebar-collapsed', 'show');
        this.mainContent.classList.remove('sidebar-collapsed');
        this.overlay.classList.remove('show');

        if (this.isDesktop) {
            // Desktop behavior - push content
            if (this.isSidebarOpen) {
                // Sidebar open - content has margin
                this.mainContent.style.marginLeft = '280px';
            } else {
                // Sidebar closed - content full width
                this.sidebar.classList.add('sidebar-collapsed');
                this.mainContent.classList.add('sidebar-collapsed');
                this.mainContent.style.marginLeft = '0';
            }
            // Never show overlay on desktop
            document.body.style.overflow = '';
        } else {
            // Mobile behavior - overlay content
            this.mainContent.style.marginLeft = '0';
            if (this.isSidebarOpen) {
                this.sidebar.classList.add('show');
                this.overlay.classList.add('show');
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }

        // Update toggle button ARIA label
        if (this.toggleBtn) {
            this.toggleBtn.setAttribute('aria-expanded', this.isSidebarOpen.toString());
            this.toggleBtn.setAttribute(
                'aria-label', 
                this.isSidebarOpen ? 'Close sidebar' : 'Open sidebar'
            );
        }
    }

    handleResize() {
        const wasDesktop = this.isDesktop;
        this.isDesktop = window.innerWidth >= 992;

        if (wasDesktop !== this.isDesktop) {
            if (this.isDesktop) {
                // Switched to desktop - restore saved state
                this.isSidebarOpen = this.sidebarState === 'open';
                document.body.style.overflow = '';
            } else {
                // Switched to mobile - close sidebar
                this.isSidebarOpen = false;
            }
            this.updateLayout();
        }
    }

    handleBodyScroll() {
        // Prevent body scroll when sidebar is open on mobile
        const preventScroll = (e) => {
            if (!this.isDesktop && this.isSidebarOpen) {
                e.preventDefault();
            }
        };

        document.addEventListener('touchmove', preventScroll, { passive: false });
        document.addEventListener('wheel', preventScroll, { passive: false });
    }
}

// Dashboard Utilities
class DashboardUtils {
    constructor() {
        this.init();
    }

    init() {
        // Auto-dismiss alerts
        this.handleAlerts();
        
        // Initialize tooltips if Bootstrap is available
        this.initTooltips();
        
        // Handle smooth scrolling
        this.initSmoothScrolling();
        
        // Initialize charts if Chart.js is available
        this.initCharts();
        
        // Add loading states
        this.handleLoadingStates();
    }

    handleAlerts() {
        const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
        alerts.forEach((alert) => {
            // Auto-dismiss after 5 seconds
            setTimeout(() => {
                if (alert && alert.parentNode && typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                    const bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            }, 5000);

            // Add close button if missing
            if (!alert.querySelector('.btn-close')) {
                const closeBtn = document.createElement('button');
                closeBtn.type = 'button';
                closeBtn.className = 'btn-close';
                closeBtn.setAttribute('data-bs-dismiss', 'alert');
                closeBtn.setAttribute('aria-label', 'Close');
                alert.appendChild(closeBtn);
            }
        });
    }

    initTooltips() {
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    }

    initSmoothScrolling() {
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    }

    initCharts() {
        // Initialize Chart.js charts if available
        const chartCanvas = document.getElementById('analyticsChart');
        if (chartCanvas && typeof Chart !== 'undefined') {
            this.createAnalyticsChart(chartCanvas);
        }
    }

    createAnalyticsChart(ctx) {
        // Get data from PHP variables or API
        const chartData = {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Page Views',
                data: [1200, 1900, 3000, 5000, 4200, 3000, 4500, 6000, 7200, 8100, 9500, 12847],
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#667eea',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8
            }, {
                label: 'Messages',
                data: [65, 78, 90, 81, 95, 85, 92, 98, 87, 105, 115, 120],
                borderColor: '#f093fb',
                backgroundColor: 'rgba(240, 147, 251, 0.1)',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#f093fb',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8
            }]
        };

        new Chart(ctx, {
            type: 'line',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                size: 12,
                                weight: '500'
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: '#f1f5f9',
                            drawBorder: false
                        },
                        ticks: {
                            color: '#718096',
                            font: {
                                size: 11
                            }
                        }
                    },
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            color: '#718096',
                            font: {
                                size: 11
                            }
                        }
                    }
                },
                elements: {
                    point: {
                        hoverBackgroundColor: '#ffffff'
                    }
                }
            }
        });
    }

    handleLoadingStates() {
        // Loading state enhancements removed per updated requirements; keep buttons untouched.
    }
}

// Performance optimizations
class PerformanceOptimizer {
    constructor() {
        this.init();
    }

    init() {
        // Preload critical resources
        this.preloadCriticalResources();
        
        // Lazy load images
        this.initLazyLoading();
        
        // Debounce scroll events
        this.optimizeScrollEvents();
    }

    preloadCriticalResources() {
        const criticalImages = [
            // Add any critical images here
        ];

        criticalImages.forEach(src => {
            const img = new Image();
            img.src = src;
        });
    }

    initLazyLoading() {
        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src;
                        img.classList.remove('lazy-load');
                        observer.unobserve(img);
                    }
                });
            });

            document.querySelectorAll('img[data-src]').forEach(img => {
                imageObserver.observe(img);
            });
        }
    }

    optimizeScrollEvents() {
        let scrollTimeout;
        window.addEventListener('scroll', () => {
            if (scrollTimeout) {
                clearTimeout(scrollTimeout);
            }
            scrollTimeout = setTimeout(() => {
                // Handle scroll events here
                this.handleScroll();
            }, 16); // ~60fps
        }, { passive: true });
    }

    handleScroll() {
        // Handle scroll-based animations or effects
        const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        // Add scroll-based functionality here
        if (scrollTop > 100) {
            document.body.classList.add('scrolled');
        } else {
            document.body.classList.remove('scrolled');
        }
    }
}

// Initialize everything when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize sidebar management
    const sidebarManager = new ModernSidebarManager();
    
    // Initialize dashboard utilities
    const dashboardUtils = new DashboardUtils();
    
    // Initialize performance optimizations
    const performanceOptimizer = new PerformanceOptimizer();
    
    // Add fade-in animation to main content
    const mainContent = document.getElementById('mainContent');
    if (mainContent) {
        mainContent.classList.add('fade-in');
    }
    
    // Handle page visibility changes for analytics
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            console.log('Page hidden');
        } else {
            console.log('Page visible');
        }
    });
    
    console.log('Modern Admin Dashboard initialized successfully');
});

// Export for use in other scripts if needed
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        ModernSidebarManager,
        DashboardUtils,
        PerformanceOptimizer
    };
}