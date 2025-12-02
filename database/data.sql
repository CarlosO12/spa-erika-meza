use spa_erika_meza;

-- Usuario Administrador por defecto
-- Email: admin@spa.com
-- Password: Admin123
INSERT INTO usuarios (email, password_hash, rol, nombre, telefono, verificado) VALUES
('admin@spa.com', '$2y$10$PBXsayM.vYMHVoI8M1nEKOGBl8phnIc3.ReQQJ1BsrUKQH8meZrs2', 'administrador', 'Administrador SPA', '3001234567', TRUE);

-- Servicios iniciales
INSERT INTO servicios (nombre, descripcion, precio, duracion, categoria, activo) VALUES
('Manicura Clásica', 'Limpieza, corte, limado y esmaltado de uñas', 35000.00, 45, 'Manos', TRUE),
('Pedicura Spa', 'Tratamiento completo para pies con exfoliación y masaje', 45000.00, 60, 'Pies', TRUE),
('Masaje Relajante', 'Masaje corporal completo para relajación', 80000.00, 90, 'Masajes', TRUE),
('Tratamiento Facial', 'Limpieza profunda y tratamiento facial', 65000.00, 60, 'Faciales', TRUE),
('Depilación con Cera', 'Depilación profesional con cera', 40000.00, 30, 'Depilación', TRUE);

-- Configuraciones del sistema
INSERT INTO configuracion_sistema (llave_ajuste, valor_ajuste, descripcion) VALUES
('nombre_negocio', 'SPA Erika Meza', 'Nombre del negocio'),
('email_negocio', 'contacto@spaerikameza.com', 'Email de contacto'),
('telefono_negocio', '3001234567', 'Teléfono de contacto'),
('direccion_negocio', 'Medellín, Antioquia, Colombia', 'Dirección del SPA'),
('horas_cancelar_citas', '24', 'Horas mínimas para cancelar cita'),
('dias_anticipacion', '30', 'Días máximos de anticipación para reservar');