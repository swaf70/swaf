</div>
        </div>
    </main>
    
    <?php if (!($hide_nav ?? false)): ?>
    <!-- Footer -->
    <footer class="footer-custom">
        <div class="container py-5">
            <div class="row">
                <div class="col-md-6 col-lg-3 mb-4 mb-md-0">
                    <h5>سكيور تيك (SecureTech)</h5>
                    <p class="text-secondary small">حلول أمن المعلومات وتقنية الشبكات<br>جمهورية اليمن - صنعاء</p>
                </div>
                <div class="col-md-6 col-lg-3 mb-4 mb-md-0">
                    <h5>قوائم التنقل</h5>
                    <ul class="list-unstyled">
                        <li><a class="text-secondary small" href="<?= site_url('index.php') ?>">الرئيسية</a></li>
                        <li><a class="text-secondary small" href="<?= site_url('works.php') ?>">أعمالي</a></li>
                        <li><a class="text-secondary small" href="<?= site_url('services.php') ?>">خدماتي</a></li>
                    </ul>
                </div>
                <div class="col-md-6 col-lg-3 mb-4 mb-md-0">
                    <h5>خدمات</h5>
                    <ul class="list-unstyled">
                        <li><a class="text-secondary small" href="<?= site_url('services.php') ?>">اختبار الاختراق</a></li>
                        <li><a class="text-secondary small" href="<?= site_url('services.php') ?>">إعداد البنية التحتية</a></li>
                        <li><a class="text-secondary small" href="<?= site_url('services.php') ?>">التطوير الكامل</a></li>
                    </ul>
                </div>
                <div class="col-md-6 col-lg-3 mb-4 mb-md-0">
                    <h5>اتصال</h5>
                    <ul class="list-unstyled">
                        <li><a class="text-secondary small" href="mailto:swaf8315@gmail.com"><i class="fas fa-envelope me-2"></i> swaf8315@gmail.com</a></li>
                        <li><a class="text-secondary small" href="tel:+967771792570"><i class="fas fa-phone me-2"></i> +967 771792570</a></li>
                    </ul>
                </div>
            </div>
            <div class="mt-4 pt-4 border-top border-secondary small">
                <p class="text-secondary">© <?= date('Y') ?> SecureTech (سكيور تيك). جميع الحقوق محفوظة.</p>
            </div>
        </div>
    </footer>
    <?php endif; ?>
    
    <!-- Bootstrap 5 RTL JS (local) -->
    <script src="<?= site_url('assets/js/bootstrap.bundle.min.js') ?>"></script>
    </body>
</html>