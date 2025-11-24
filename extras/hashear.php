<?php

$password = 'Admin123';
$passwordHash = password_hash($password, PASSWORD_DEFAULT);

echo "Password plano: $password\n";
echo "Hash generado: $passwordHash\n";

if (password_verify('Admin123', $passwordHash)) {
    echo "Verificación OK: la contraseña coincide.\n";
} else {
    echo "Verificación FALLIDA.\n";
}