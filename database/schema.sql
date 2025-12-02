CREATE DATABASE IF NOT EXISTS spa_erika_meza CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE spa_erika_meza;

-- ============================================
-- TABLA: usuarios
-- Descripción: Almacena todos los usuarios del sistema
-- ============================================
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    rol ENUM('cliente', 'especialista', 'administrador') NOT NULL DEFAULT 'cliente',
    nombre VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    direccion VARCHAR(255),
    verificado BOOLEAN DEFAULT FALSE,
    codigo_verificacion VARCHAR(6),
    expira_verificacion DATETIME,
    token_restablecido VARCHAR(100),
    expira_restablecido DATETIME,
    creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_role (rol)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: especialistas
-- Descripción: Información adicional de especialistas
-- ============================================
CREATE TABLE especialistas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    especialista VARCHAR(255),
    descripcion TEXT,
    experiencia INT DEFAULT 0,
    disponible BOOLEAN DEFAULT TRUE,
    evaluacion DECIMAL(3,2) DEFAULT 0.00,
    total_opiniones INT DEFAULT 0,
    imagen_perfil VARCHAR(255),
    creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_available (disponible),
    INDEX idx_rating (evaluacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: servicios
-- Descripción: Catálogo de servicios del SPA
-- ============================================
CREATE TABLE servicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(255) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    duracion INT NOT NULL COMMENT 'Duración en minutos',
    imagen VARCHAR(255),
    categoria VARCHAR(100),
    activo BOOLEAN DEFAULT TRUE,
    creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (activo),
    INDEX idx_category (categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: citas
-- Descripción: Citas agendadas
-- ============================================
CREATE TABLE citas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    especialista_id INT NOT NULL,
    servicio_id INT NOT NULL,
    fecha_cita DATE NOT NULL,
    hora_cita TIME NOT NULL,
    estado ENUM('pendiente', 'confirmada', 'completada', 'cancelada') DEFAULT 'pendiente',
    notas TEXT,
    razon_cancelacion TEXT,
    creada TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizada TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (especialista_id) REFERENCES especialistas(id) ON DELETE CASCADE,
    FOREIGN KEY (servicio_id) REFERENCES servicios(id) ON DELETE CASCADE,
    INDEX idx_date (fecha_cita),
    INDEX idx_status (estado),
    INDEX idx_user (usuario_id),
    INDEX idx_specialist (especialista_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: carrito
-- Descripción: Carrito de compras temporal
-- ============================================
CREATE TABLE carrito (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    servicio_id INT NOT NULL,
    cantidad INT DEFAULT 1,
    creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (servicio_id) REFERENCES servicios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_user_service (usuario_id, servicio_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: historial_servicios
-- Descripción: Historial de servicios completados con reseñas
-- ============================================
CREATE TABLE historial_servicios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cita_id INT NOT NULL,
    evaluacion INT CHECK (evaluacion BETWEEN 1 AND 5),
    opinion TEXT,
    creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cita_id) REFERENCES citas(id) ON DELETE CASCADE,
    INDEX idx_rating (evaluacion)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: notificaciones
-- Descripción: Notificaciones del sistema
-- ============================================
CREATE TABLE notificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    tipo ENUM('email', 'sms', 'system') DEFAULT 'email',
    sujeto VARCHAR(255),
    mensaje TEXT NOT NULL,
    enviado BOOLEAN DEFAULT FALSE,
    hora_enviado DATETIME,
    creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_sent (enviado),
    INDEX idx_user (usuario_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: disponibilidad_especialistas
-- Descripción: Horarios disponibles de especialistas
-- ============================================
CREATE TABLE disponibilidad_especialistas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    especialista_id INT NOT NULL,
    dia_semana TINYINT NOT NULL COMMENT '0=Domingo, 1=Lunes, ..., 6=Sábado',
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    activo BOOLEAN DEFAULT TRUE,
    creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (especialista_id) REFERENCES especialistas(id) ON DELETE CASCADE,
    INDEX idx_specialist_day (especialista_id, dia_semana),
    INDEX idx_active (activo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- TABLA: contacto_mensajes
-- Descripción: Mensajes enviados desde el formulario publico
-- ============================================
CREATE TABLE contacto_mensajes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    telefono VARCHAR(20) DEFAULT NULL,
    asunto ENUM('informacion', 'cita', 'servicio', 'queja', 'otro') NOT NULL,
    mensaje TEXT NOT NULL,
    fecha_envio DATETIME DEFAULT CURRENT_TIMESTAMP,
    estado ENUM('nuevo', 'leido', 'respondido') DEFAULT 'nuevo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ============================================
-- TABLA: configuracion_sistema
-- Descripción: Configuraciones del sistema
-- ============================================
CREATE TABLE configuracion_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) UNIQUE NOT NULL,
    valor TEXT NOT NULL,
    tipo ENUM('text', 'email', 'phone', 'number', 'textarea', 'boolean') DEFAULT 'text',
    categoria VARCHAR(50) DEFAULT 'general',
    descripcion VARCHAR(255),
    actualizado TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_clave (clave),
    INDEX idx_categoria (categoria)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


-- Vista: Citas con información completa
CREATE OR REPLACE VIEW vw_citas_full AS
SELECT 
    c.id,
    c.fecha_cita,
    c.hora_cita,
    c.estado,
    c.notas,
    c.razon_cancelacion,
    c.creada,
    u.nombre AS nombre_cliente,
    u.email AS email_cliente,
    u.telefono AS telefono_cliente,
    s.id AS servicio_id,
    s.nombre AS nombre_servicio,
    s.descripcion AS descripcion_servicio,
    s.precio AS precio_servicio,
    s.duracion AS duracion_servicio,
    uesp.nombre AS nombre_especialista,
    uesp.email AS email_especialista,
    e.especialista AS especialidad
FROM citas c
INNER JOIN usuarios u ON c.usuario_id = u.id
INNER JOIN servicios s ON c.servicio_id = s.id
INNER JOIN especialistas e ON c.especialista_id = e.id
INNER JOIN usuarios uesp ON e.usuario_id = uesp.id;


-- Trigger: Actualizar rating del especialista después de nueva reseña
DELIMITER //
CREATE TRIGGER actualizar_evaluacion_especialista
AFTER INSERT ON historial_servicios
FOR EACH ROW
BEGIN
    DECLARE especialista_id_var INT;
    
    SELECT especialista_id INTO especialista_id_var
    FROM citas
    WHERE id = NEW.cita_id;
    
    UPDATE especialistas
    SET evaluacion = (
        SELECT AVG(hs.evaluacion)
        FROM historial_servicios hs
        INNER JOIN citas c ON hs.cita_id = c.id
        WHERE c.especialista_id = especialista_id_var
    ),
    total_opiniones = (
        SELECT COUNT(*)
        FROM historial_servicios hs
        INNER JOIN citas c ON hs.cita_id = c.id
        WHERE c.especialista_id = especialista_id_var
    )
    WHERE id = especialista_id_var;
END//
DELIMITER ;