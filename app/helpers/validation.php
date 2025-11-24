<?php
/**
 * Helper de Validación de Datos
 * SPA Erika Meza
 */

/**
 * Validar email
 */
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Validar contraseña
 */
function validatePassword($password) {
    $result = ['valid' => true, 'message' => ''];
    
    if (strlen($password) < PASSWORD_MIN_LENGTH) {
        $result['valid'] = false;
        $result['message'] = 'La contraseña debe tener al menos ' . PASSWORD_MIN_LENGTH . ' caracteres.';
        return $result;
    }
    
    if (!preg_match('/[A-Z]/', $password)) {
        $result['valid'] = false;
        $result['message'] = 'La contraseña debe contener al menos una letra mayúscula.';
        return $result;
    }
    
    if (!preg_match('/[a-z]/', $password)) {
        $result['valid'] = false;
        $result['message'] = 'La contraseña debe contener al menos una letra minúscula.';
        return $result;
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $result['valid'] = false;
        $result['message'] = 'La contraseña debe contener al menos un número.';
        return $result;
    }
    
    return $result;
}

/**
 * Validar teléfono
 */
function validatePhone($telefono) {
    $telefono = preg_replace('/[^0-9]/', '', $telefono);
    return preg_match('/^3[0-9]{9}$/', $telefono);
}

/**
 * Validar fecha
 */
function validateDate($fecha) {
    $d = DateTime::createFromFormat('Y-m-d', $fecha);
    return $d && $d->format('Y-m-d') === $fecha;
}

/**
 * Validar que la fecha sea futura
 */
function isFutureDate($fecha) {
    $inputDate = strtotime($fecha);
    $today = strtotime(date('Y-m-d'));
    return $inputDate >= $today;
}

/**
 * Validar hora en formato HH:MM
 */
function validateTime($time) {
    if (empty($time)) {
        return false;
    }
    
    $pattern = '/^([01]?[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/';
    
    return preg_match($pattern, $time) === 1;
}

/**
 * Validar campo requerido
 */
function required($value) {
    if (is_string($value)) {
        return trim($value) !== '';
    }
    return !empty($value);
}

/**
 * Validar longitud mínima
 */
function minLength($value, $min) {
    return strlen(trim($value)) >= $min;
}

/**
 * Validar longitud máxima
 */
function maxLength($value, $max) {
    return strlen(trim($value)) <= $max;
}

/**
 * Validar que sea un número
 */
function isNumeric($value) {
    return is_numeric($value);
}

/**
 * Validar que sea un número positivo
 */
function isPositive($value) {
    return is_numeric($value) && $value > 0;
}

/**
 * Validar rango numérico
 */
function inRange($value, $min, $max) {
    return is_numeric($value) && $value >= $min && $value <= $max;
}

/**
 * Validar extensión de archivo
 */
function validateFileExtension($filename, $allowedExtensions = ALLOWED_EXTENSIONS) {
    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
    return in_array($extension, $allowedExtensions);
}

/**
 * Validar tamaño de archivo
 */
function validateFileSize($fileSize, $maxSize = MAX_FILE_SIZE) {
    return $fileSize <= $maxSize;
}

/**
 * Validar formato de imagen
 */
function validateImage($file) {
    $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
    return in_array($file['type'], $allowedTypes);
}

/**
 * Validar rating
 */
function validateRating($evaluacion) {
    return inRange($evaluacion, MIN_RATING, MAX_RATING);
}

/**
 * Sanitizar y validar datos de formulario
 */
function validateForm($data, $rules) {
    $errors = [];
    
    foreach ($rules as $field => $fieldRules) {
        $value = $data[$field] ?? '';
        
        foreach ($fieldRules as $rule => $param) {
            switch ($rule) {
                case 'required':
                    if (!required($value)) {
                        $errors[$field][] = ucfirst($field) . ' es requerido.';
                    }
                    break;
                    
                case 'email':
                    if (!empty($value) && !validateEmail($value)) {
                        $errors[$field][] = 'Email inválido.';
                    }
                    break;
                    
                case 'min':
                    if (!minLength($value, $param)) {
                        $errors[$field][] = ucfirst($field) . ' debe tener al menos ' . $param . ' caracteres.';
                    }
                    break;
                    
                case 'max':
                    if (!maxLength($value, $param)) {
                        $errors[$field][] = ucfirst($field) . ' no puede exceder ' . $param . ' caracteres.';
                    }
                    break;
                    
                case 'phone':
                    if (!empty($value) && !validatePhone($value)) {
                        $errors[$field][] = 'Teléfono inválido. Debe ser un número colombiano válido.';
                    }
                    break;
                    
                case 'date':
                    if (!empty($value) && !validateDate($value)) {
                        $errors[$field][] = 'Fecha inválida.';
                    }
                    break;
                    
                case 'future_date':
                    if (!empty($value) && !isFutureDate($value)) {
                        $errors[$field][] = 'La fecha debe ser futura.';
                    }
                    break;
            }
        }
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors
    ];
}
?>