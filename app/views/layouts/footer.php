</main>
    
    <footer class="footer bg-dark text-white mt-5 py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <h5><img src="img/favicon.png" alt="icono" class="bi" width="24" height="24"> <?php echo APP_NAME; ?></h5>
                    <p class="text-muted">Tu espacio de belleza y bienestar en Medellín</p>
                    <div class="social-links">
                        <a href="<?= config('facebook_url') ?>" class="text-white me-3" target="_blank"><i class="bi bi-facebook"></i></a>
                        <a href="<?= config('instagram_url') ?>" class="text-white me-3" target="_blank"><i class="bi bi-instagram"></i></a>
                        <a href="https://api.whatsapp.com/send?phone=<?= preg_replace('/\D/', '', config('whatsapp_number')) ?>" class="text-white me-3" target="_blank"><i class="bi bi-whatsapp"></i></a>
                    </div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <h5>Enlaces Rápidos</h5>
                    <ul class="list-unstyled">
                        <li><a href="<?php echo BASE_URL; ?>/index.php" class="text-muted">Inicio</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/index.php?page=services" class="text-muted">Servicios</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/index.php?page=about" class="text-muted">Nosotros</a></li>
                        <li><a href="<?php echo BASE_URL; ?>/index.php?page=contact" class="text-muted">Contacto</a></li>
                    </ul>
                </div>
                
                <div class="col-md-4 mb-3">
                    <h5>Contacto</h5>
                    <ul class="list-unstyled text-muted">
                        <li><i class="bi bi-geo-alt"></i> <?php echo APP_ADDRESS; ?></li>
                        <li><i class="bi bi-telephone"></i> <?php echo APP_PHONE; ?></li>
                        <li><i class="bi bi-envelope"></i> <?php echo APP_EMAIL; ?></li>
                        <li><i class="bi bi-clock"></i> <?= config('business_schedule') ?>: <?= config('business_hours_start') ?> - <?= config('business_hours_end') ?></li>
                    </ul>
                </div>
            </div>
            
            <hr class="bg-secondary">
            
            <div class="row">
                <div class="col-md-12 text-center">
                    <p class="mb-0 text-muted">
                        &copy; <?php echo date('Y'); ?> <?php echo APP_NAME; ?>. Todos los derechos reservados. 
                        | Versión <?php echo APP_VERSION; ?>
                    </p>
                </div>
            </div>
        </div>
    </footer>
    
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery (opcional, para funcionalidades adicionales) -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    
    <!-- Custom JS -->
    <script src="<?php echo ASSETS_URL; ?>js/main.js"></script>
    
    <?php if (isset($page) && $page === 'book-appointment'): ?>
    <script src="<?php echo ASSETS_URL; ?>js/calendar.js"></script>
    <?php endif; ?>
    
    <?php if (isset($page) && $page === 'cart'): ?>
    <script src="<?php echo ASSETS_URL; ?>js/cart.js"></script>
    <?php endif; ?>
    
    <script>
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>