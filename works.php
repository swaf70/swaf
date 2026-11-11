<?php
session_start();
require_once __DIR__ . '/config/db.php';

// Require login to access the site
if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$page_title = 'أعمالي - portfolio';
$active_page = 'works';
?>
<?php require_once __DIR__ . '/includes/header.php' ?>
    <?php $active_page = 'works'; ?>
    
    <!-- Portfolio Filter & Search -->
    <section class="portfolio-filter py-4 bg-secondary-slate">
        <div class="container">
            <div class="row g-3">
                <div class="col-12">
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        <!-- Category Filters -->
                        <button class="filter-btn btn btn-outline-primary-cyan px-3 py-2 small" data-filter="all">
                            جميع التصنيفات
                        </button>
                        <button class="filter-btn btn btn-outline-secondary px-3 py-2 small" data-filter="Web Development">
                            Web Development
                        </button>
                        <button class="filter-btn btn btn-outline-secondary px-3 py-2 small" data-filter="Network Architecture">
                            Network Architecture
                        </button>
                        <button class="filter-btn btn btn-outline-secondary px-3 py-2 small" data-filter="Cybersecurity">
                            Cybersecurity
                        </button>
                        <button class="filter-btn btn btn-outline-secondary px-3 py-2 small" data-filter="Full-Stack">
                            Full-Stack
                        </button>
                    </div>
                </div>
                <div class="col-12">
                    <div class="d-flex gap-2">
                        <input type="text" class="form-control form-control-custom" id="portfolioSearch" 
                               placeholder="ابحث في المشاريع..." maxlength="100">
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Portfolio Grid -->
    <section class="portfolio-section py-5">
        <div class="container">
            <div class="row g-4 portfolio-grid" id="portfolioGrid">
                <!-- Projects will be loaded via JavaScript -->
                <div class="col-12 text-center py-5">
                    <div class="spinner-border text-primary-cyan" role="status">
                        <span class="visually-hidden">جاري التحميل...</span>
                    </div>
                    <p class="mt-3 text-secondary">جاري تحميل المشاريع...</p>
                </div>
            </div>
        </div>
    </section>
    
    <!-- JavaScript for portfolio filtering and search -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sample projects data - in production, fetch from API
            const projects = [
                {
                    id: 1,
                    title: 'Secure Web Portal',
                    category: 'Web Development',
                    description: 'A full-featured web application with authentication and role-based access control.',
                    tech_stack: ['PHP', 'MySQL', 'Linux', 'Docker', 'HTML5', 'CSS3'],
                    image: 'project1.jpg'
                },
                {
                    id: 2,
                    title: 'Network Infrastructure Monitor',
                    category: 'Network Architecture',
                    description: 'Real-time network monitoring and traffic analysis dashboard for enterprise networks.',
                    tech_stack: ['PHP', 'MySQL', 'Python', 'Grafana', 'Docker'],
                    image: 'project2.jpg'
                },
                {
                    id: 3,
                    title: 'Vulnerability Assessment Tool',
                    category: 'Cybersecurity',
                    description: 'Automated security scanning tool for detecting common web vulnerabilities and misconfigurations.',
                    tech_stack: ['Python', 'Flask', 'MySQL', 'OpenCV', 'Bootstrap'],
                    image: 'project3.jpg'
                },
                {
                    id: 4,
                    title: 'E-commerce Platform',
                    category: 'Full-Stack',
                    description: 'Complete online store with payment gateway integration, admin panel, and inventory management.',
                    tech_stack: ['PHP', 'MySQL', 'Stripe API', 'React', 'Bootstrap'],
                    image: 'project4.jpg'
                }
            ];
            
            // Render portfolio items
            function renderProjects(filter = 'all', searchTerm = '') {
                const grid = document.getElementById('portfolioGrid');
                if (!grid) return;
                
                let html = '';
                
                projects.forEach(project => {
                    // Apply category filter
                    if (filter !== 'all' && project.category !== filter) {
                        return;
                    }
                    
                    // Apply search filter
                    const searchLower = searchTerm.toLowerCase();
                    const matchesSearch = 
                        project.title.toLowerCase().includes(searchLower) ||
                        project.category.toLowerCase().includes(searchLower) ||
                        project.description.toLowerCase().includes(searchLower);
                    
                    if (!matchesSearch) return;
                    
                    const techBadges = project.tech_stack.map(t => 
                        `<span class="project-badge me-1 small">${t}</span>`
                    ).join('');
                    
                    html += `
                        <div class="col-md-6 col-lg-4 project-item" data-category="${project.category}">
                            <div class="project-card h-100">
                                <img src="uploads/projects/${project.image}" 
                                     alt="${project.title}" 
                                     class="project-img img-fluid">
                                <div class="project-body p-3">
                                    <h4 class="project-title mb-2">${project.title}</h4>
                                    <p class="project-category text-secondary small mb-1">${project.category}</p>
                                    <div class="tech-tags mb-3">${techBadges}</div>
                                    <p class="project-desc text-secondary small line-clamp-2">${project.description}</p>
                                    <a href="#" class="stretched-link">
                                        <i class="fas fa-arrow-up-right-small text-primary-cyan small"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                grid.innerHTML = html || '<p class="text-center py-5">لا توجد مشاريع مطابقة للبحث.</p>';
            }
            
            // Initialize with all projects
            renderProjects();
            
            // Category filter buttons
            const filterBtns = document.querySelectorAll('.filter-btn');
            filterBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    // Update active state
                    filterBtns.forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                    
                    const filter = this.getAttribute('data-filter');
                    renderProjects(filter);
                });
            });
            
            // Search input
            const searchInput = document.getElementById('portfolioSearch');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const searchTerm = this.value.trim();
                    renderProjects('all', searchTerm);
                });
            }
            
            // Modal trigger for project details
            const projectCards = document.querySelectorAll('.project-card');
            projectCards.forEach(card => {
                card.addEventListener('click', function(e) {
                    // Don't trigger if clicking on links or buttons inside
                    if (e.target.closest('a') || e.target.closest('button')) return;
                    
                    // In a full implementation, this would open a modal
                    // For now, we'll just show an alert
                    const title = this.querySelector('.project-title')?.innerText || '';
                    alert('اسم المشروع: ' + title);
                });
            });
        });
    </script>
<?php require_once __DIR__ . '/includes/footer.php' ?>