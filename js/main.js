// ============================================
// REDLINE MAIN JAVASCRIPT
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    
    // Mobile navigation toggle
    const navToggle = document.getElementById('navToggle');
    const navMenu = document.getElementById('navMenu');
    
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });
    }
    
    // Smooth scroll for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                e.preventDefault();
                target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                if (navMenu) navMenu.classList.remove('active');
            }
        });
    });
    
    // Navbar scroll effect
    const navbar = document.querySelector('.navbar');
    if (navbar) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                navbar.style.padding = '12px 0';
            } else {
                navbar.style.padding = '16px 0';
            }
        });
    }
    
    // Counter animation for stats
    const counters = document.querySelectorAll('.stat-number');
    const counterObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting && !entry.target.dataset.counted) {
                entry.target.dataset.counted = 'true';
                const text = entry.target.innerText;
                const match = text.match(/\d+/);
                if (match) {
                    const targetNum = parseInt(match[0]);
                    let current = 0;
                    const suffix = text.replace(/\d+/, '');
                    const timer = setInterval(() => {
                        current += Math.ceil(targetNum / 50);
                        if (current >= targetNum) {
                            entry.target.innerText = targetNum + suffix;
                            clearInterval(timer);
                        } else {
                            entry.target.innerText = current + suffix;
                        }
                    }, 30);
                }
            }
        });
    }, { threshold: 0.5 });
    
    counters.forEach(el => counterObserver.observe(el));
    
    // Session check for dashboard pages
    const isDashboardPage = window.location.pathname.includes('pages/') && 
                           !window.location.pathname.includes('login.html') &&
                           !window.location.pathname.includes('register.html') &&
                           !window.location.pathname.includes('index.html');
    
    if (isDashboardPage) {
        const session = localStorage.getItem('rl_user');
        if (!session) {
            window.location.href = 'login.html';
        } else {
            try {
                const user = JSON.parse(session);
                const userNameEl = document.getElementById('userName');
                const userRoleEl = document.getElementById('userRole');
                if (userNameEl) userNameEl.innerText = user.username || 'Admin';
                if (userRoleEl) userRoleEl.innerText = user.role || 'admin';
            } catch(e) {}
        }
    }
    
    // Clock update for dashboard
    const clockEl = document.getElementById('clock');
    if (clockEl) {
        setInterval(() => {
            clockEl.innerText = new Date().toLocaleTimeString('id-ID');
        }, 1000);
    }
    
    // Sidebar toggle for dashboard
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const mainContent = document.getElementById('mainContent');
    
    if (menuToggle && sidebar && mainContent) {
        menuToggle.addEventListener('click', function() {
            if (window.innerWidth <= 992) {
                sidebar.classList.toggle('mobile-open');
            } else {
                sidebar.classList.toggle('collapsed');
                mainContent.classList.toggle('expanded');
            }
        });
    }
    
    // Toggle password visibility
    function togglePassword() {
        const passwordInput = document.getElementById('passwordInput');
        const eyeIcon = document.getElementById('eyeIcon');

        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.remove('fa-eye');
            eyeIcon.classList.add('fa-eye-slash');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('fa-eye-slash');
            eyeIcon.classList.add('fa-eye');
        }
    }
    
    console.log('REDLINE System Ready');
});