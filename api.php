<?php
header('Content-Type: application/json');

$file = 'chat_store.json';
if (!file_exists($file)) {
    file_put_contents($file, json_encode(['messages' => [], 'users' => []]));
}

$data = json_decode(file_get_contents($file), true);
$action = $_GET['action'] ?? $_POST['action'] ?? '';
$username = $_POST['username'] ?? $_GET['username'] ?? '';

$now = time();

// Registrar latido del usuario actual si manda su nombre
if (!empty($username)) {
    $data['users'][$username] = $now;
}

// Limpiar usuarios inactivos (si llevan más de 4 segundos sin reportarse)FROM php:8.4-apache
COPY . /var/www/html/
EXPOSE 80

foreach ($data['users'] as $user => $lastSeen) {
    if ($now - $lastSeen > 4) {
        unset($data['users'][$user]);
    }
}

// Guardar nuevo mensaje
if ($action === 'send' && !empty($_POST['message'])) {
    $texto = trim($_POST['message']);
    if ($texto !== '') {
        $data['messages'][] = [
            'username' => htmlspecialchars($username),
            'texto' => htmlspecialchars($texto),
            'hora' => date('H:i')
        ];
        
        // Mantener solo los últimos 100 mensajes
        if (count($data['messages']) > 100) {
            array_shift($data['messages']);
        }
    }
}

file_put_contents($file, json_encode($data));

// Devolver datos al script de JavaScript
echo json_encode([
    'messages' => $data['messages'],
    'active_users' => count($data['users'])
]);
?>