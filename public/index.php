<?php
/**
 * Archivo Principal - Enrutador
 * SPA Erika Meza
 */

require_once '../config/config.php';
require_once '../config/constants.php';

$page = $_GET['page'] ?? 'home';
$hideLayout = in_array($page, ['login', 'register', 'forgot-password', 'reset-password', 'verify']);

// Definir rutas públicas (no requieren autenticación)
$publicPages = [
    'home',
    'login',
    'register',
    'verify',
    'forgot-password',
    'reset-password',
    'services',
    'service-detail',
    'about',
    'contact'
];

// Rutas de clientes
$clientPages = [
    'user-dashboard',
    'profile',
    'my-appointments',
    'book-appointment',
    'cart',
    'history'
];

// Rutas de especialistas
$specialistPages = [
    'specialist-dashboard',
    'specialist-schedule',
    'specialist-appointments'
];

// Rutas de administrador
$adminPages = [
    'admin-dashboard',
    'admin-users',
    'admin-services',
    'admin-appointments',
    'admin-specialists',
    'admin-reports'
];

// Procesar acciones de controladores
if (isset($_GET['action'])) {
    $action = $_GET['action'];
    
    switch ($action) {
        // Acciones de autenticación
        case 'register':
            require_once APP_PATH . '/controllers/AuthController.php';
            $controller = new AuthController();
            $controller->register();
            break;
            
        case 'login':
            require_once APP_PATH . '/controllers/AuthController.php';
            $controller = new AuthController();
            $controller->login();
            break;
            
        case 'logout':
            require_once APP_PATH . '/controllers/AuthController.php';
            $controller = new AuthController();
            $controller->logout();
            break;
            
        case 'verify':
            require_once APP_PATH . '/controllers/AuthController.php';
            $controller = new AuthController();
            $controller->verify();
            break;
            
        case 'forgot-password':
            require_once APP_PATH . '/controllers/AuthController.php';
            $controller = new AuthController();
            $controller->forgotPassword();
            break;
            
        case 'reset-password':
            require_once APP_PATH . '/controllers/AuthController.php';
            $controller = new AuthController();
            $controller->resetPassword();
            break;
        
        // Acciones de usuario
        case 'update-profile':
            require_once APP_PATH . '/controllers/UserController.php';
            $controller = new UserController();
            $controller->updateProfile();
            break;
            
        case 'change-password':
            require_once APP_PATH . '/controllers/UserController.php';
            $controller = new UserController();
            $controller->changePassword();
            break;
            
        case 'cancel-appointment':
            require_once APP_PATH . '/controllers/UserController.php';
            $controller = new UserController();
            $controller->cancelAppointment();
            break;
        
        // Acciones de servicios
        case 'add-to-cart':
            require_once APP_PATH . '/controllers/ServiceController.php';
            $controller = new ServiceController();
            $controller->addToCart();
            break;
            
        case 'remove-from-cart':
            require_once APP_PATH . '/controllers/ServiceController.php';
            $controller = new ServiceController();
            $controller->removeFromCart();
            break;
            
        case 'clear-cart':
            require_once APP_PATH . '/controllers/ServiceController.php';
            $controller = new ServiceController();
            $controller->clearCart();
            break;
        
        // Acciones de citas
        case 'create-appointment':
            require_once APP_PATH . '/controllers/AppointmentController.php';
            $controller = new AppointmentController();
            $controller->create();
            break;
            
        case 'update-appointment-status':
            require_once APP_PATH . '/controllers/AppointmentController.php';
            $controller = new AppointmentController();
            $controller->updateStatus();
            break;
            
        case 'get-available-slots':
            require_once APP_PATH . '/controllers/AppointmentController.php';
            $controller = new AppointmentController();
            $controller->getAvailableSlots();
            break;
        
        
        // Acciones de administrador
        case 'create-service':
            require_once APP_PATH . '/controllers/ServiceController.php';
            $controller = new ServiceController();
            $controller->create();
            break;
            
        case 'update-service':
            require_once APP_PATH . '/controllers/ServiceController.php';
            $controller = new ServiceController();
            $controller->update();
            break;
            
        case 'delete-service':
            require_once APP_PATH . '/controllers/ServiceController.php';
            $controller = new ServiceController();
            $controller->delete();
            break;
            
        case 'create-user':
            require_once APP_PATH . '/controllers/AdminController.php';
            $controller = new AdminController();
            $controller->createUser();
            break;
            
        case 'update-user':
            require_once APP_PATH . '/controllers/AdminController.php';
            $controller = new AdminController();
            $controller->updateUser();
            break;
            
        case 'delete-user':
            require_once APP_PATH . '/controllers/AdminController.php';
            $controller = new AdminController();
            $controller->deleteUser();
            break;

        case 'update-settings':
            require_once APP_PATH . '/controllers/AdminController.php';
            $controller = new AdminController();
            $controller->updateSettings();
            break;

        case 'export-csv':
            require_once APP_PATH . '/controllers/AdminController.php';
            $controller = new AdminController();
            $type = $_GET['type'] ?? 'users';
            $controller->exportToCSV($type);
            break;

        case 'export-pdf':
            require_once APP_PATH . '/controllers/AdminController.php';
            $controller = new AdminController();
            $type = $_GET['type'] ?? 'users';
            $controller->exportToPDF($type);
            break;

        case 'contact':
            require_once APP_PATH . '/controllers/ContactController.php';
            $controller = new ContactController();
            $controller->sendMessage();
            break;

        case 'update-message-status':
            require_once APP_PATH . '/controllers/ContactController.php';
            $controller = new ContactController();
            $controller->updateStatus();
            break;

        case 'delete-message':
            require_once APP_PATH . '/controllers/ContactController.php';
            $controller = new ContactController();
            $controller->deleteMessage();
            break;

        case 'reply-message':
            require_once APP_PATH . '/controllers/ContactController.php';
            $controller = new ContactController();
            $controller->replyMessage();
            break;
        
        case 'update-specialist-schedule':
            require_once APP_PATH . '/controllers/AdminController.php';
            $adminController = new AdminController();
            $adminController->updateSpecialistSchedule();
            break;

        case 'toggle-schedule-status':
            require_once APP_PATH . '/controllers/AdminController.php';
            $adminController = new AdminController();
            $adminController->toggleScheduleStatus();
            break;

        case 'deactivate-specialist':
            require_once APP_PATH . '/controllers/AdminController.php';
            $adminController = new AdminController();
            $adminController->deactivateSpecialist();
            break;

        case 'activate-specialist':
            require_once APP_PATH . '/controllers/AdminController.php';
            $adminController = new AdminController();
            $adminController->activateSpecialist();
            break;

        case 'get-specialist-data':
            require_once APP_PATH . '/controllers/AdminController.php';
            $adminController = new AdminController();
            $adminController->getSpecialistData();
            break;
        
        // Acciones de reseña

        case 'create-review':
            require_once APP_PATH . '/controllers/ReviewController.php';
            $reviewController = new ReviewController();
            $reviewController->create();
            break;

        case 'update-review':
            require_once APP_PATH . '/controllers/ReviewController.php';
            $reviewController = new ReviewController();
            $reviewController->update();
            break;

        case 'delete-review':
            require_once APP_PATH . '/controllers/ReviewController.php';
            $reviewController = new ReviewController();
            $reviewController->delete();
            break;
    }
    exit();
}

// Verificar autenticación para páginas protegidas
if (!in_array($page, $publicPages)) {
    requireAuth();
    
    // Verificar permisos según rol
    if (in_array($page, $adminPages) && !isAdmin()) {
        setFlashMessage('error', MSG_ERROR_UNAUTHORIZED);
        header('Location: ' . BASE_URL . '/index.php');
        exit();
    }
    
    if (in_array($page, $specialistPages) && !isSpecialist()) {
        setFlashMessage('error', MSG_ERROR_UNAUTHORIZED);
        header('Location: ' . BASE_URL . '/index.php');
        exit();
    }
}

// Incluir header
if (!$hideLayout) {
    include APP_PATH . '/views/layouts/header.php';
}

// Enrutar a la vista correspondiente
switch ($page) {
    // Páginas públicas
    case 'home':
        include APP_PATH . '/views/home.php';
        break;
        
    case 'login':
        if (isLoggedIn()) {
            header('Location: ' . BASE_URL . '/index.php?page=user-dashboard');
            exit();
        }
        include APP_PATH . '/views/auth/login.php';
        break;
        
    case 'register':
        if (isLoggedIn()) {
            header('Location: ' . BASE_URL . '/index.php?page=user-dashboard');
            exit();
        }
        include APP_PATH . '/views/auth/register.php';
        break;
        
    case 'verify':
        include APP_PATH . '/views/auth/verify.php';
        break;
        
    case 'forgot-password':
        include APP_PATH . '/views/auth/forgot-password.php';
        break;
        
    case 'reset-password':
        include APP_PATH . '/views/auth/reset-password.php';
        break;
        
    case 'services':
        include APP_PATH . '/views/services/catalog.php';
        break;
        
    case 'service-detail':
        include APP_PATH . '/views/services/detail.php';
        break;
        
    case 'about':
        include APP_PATH . '/views/about.php';
        break;
        
    case 'contact':
        include APP_PATH . '/views/contact.php';
        break;
    
    // Páginas de cliente
    case 'user-dashboard':
        requireRole(ROLE_CLIENT);
        include APP_PATH . '/views/user/dashboard.php';
        break;
        
    case 'profile':
        include APP_PATH . '/views/user/profile.php';
        break;
        
    case 'my-appointments':
        requireRole(ROLE_CLIENT);
        include APP_PATH . '/views/user/appointments.php';
        break;
        
    case 'book-appointment':
        requireRole(ROLE_CLIENT);
        include APP_PATH . '/views/user/book-appointment.php';
        break;
        
    case 'cart':
        requireRole(ROLE_CLIENT);
        include APP_PATH . '/views/user/cart.php';
        break;
        
    case 'history':
        requireRole(ROLE_CLIENT);
        include APP_PATH . '/views/user/history.php';
        break;

    case 'specialist-reviews':
        include APP_PATH . '/views/user/specialist-reviews.php';
        break;

    case 'specialists':
        include APP_PATH . '/views/user/specialists.php';
        break;
    
    // Páginas de especialista
    case 'specialist-dashboard':
        requireRole(ROLE_SPECIALIST);
        include APP_PATH . '/views/specialist/dashboard.php';
        break;
        
    case 'specialist-schedule':
        requireRole(ROLE_SPECIALIST);
        include APP_PATH . '/views/specialist/schedule.php';
        break;
        
    case 'specialist-appointments':
        requireRole(ROLE_SPECIALIST);
        include APP_PATH . '/views/specialist/appointments.php';
        break;
    
    // Páginas de administrador
    case 'admin-dashboard':
        requireAdmin();
        include APP_PATH . '/views/admin/dashboard.php';
        break;
        
    case 'admin-users':
        requireAdmin();
        include APP_PATH . '/views/admin/users.php';
        break;
        
    case 'admin-services':
        requireAdmin();
        include APP_PATH . '/views/admin/services.php';
        break;
        
    case 'admin-appointments':
        requireAdmin();
        include APP_PATH . '/views/admin/appointments.php';
        break;
        
    case 'admin-specialists':
        requireAdmin();
        include APP_PATH . '/views/admin/specialists.php';
        break;
        
    case 'admin-reports':
        requireAdmin();
        include APP_PATH . '/views/admin/reports.php';
        break;

    case 'admin-settings':
        requireAdmin();
        include APP_PATH . '/views/admin/settings.php';
        break;

    case 'admin-messages':
        requireAdmin();
        include APP_PATH . '/views/admin/messages.php';
        break;

    case 'admin-reviews':
        include APP_PATH . '/views/admin/reviews.php';
        break;
    
    default:
        http_response_code(404);
        include APP_PATH . '/views/404.php';
        break;
}

if (!$hideLayout) {
    include APP_PATH . '/views/layouts/footer.php';
}
?>