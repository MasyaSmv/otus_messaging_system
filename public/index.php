<?php

declare(strict_types = 1);

// 1) Подключаем автозагрузку и bootstrap (IoC и т.д.)
require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/../bootstrap.php';

use Masyasmv\Messaging\Http\Controller\PublishController;

// 2) Простая «здоровая» проверка
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // health-check
    header('Content-Type: text/plain');
    echo "OK";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 3) Читаем и декодируем JSON-тело
    $raw = file_get_contents('php://input');
    $payload = json_decode($raw, true);

    if (!is_array($payload)) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode(['error' => 'Invalid JSON']);
        exit;
    }

    // 4) Публикуем в RabbitMQ
    $controller = new PublishController();
    $result = $controller->send($payload);

    // 5) Отдаём клиенту статус
    header('Content-Type: application/json');
    echo json_encode($result);
    exit;
}

// 6) Другие методы не поддерживаем
http_response_code(405);
header('Allow: GET, POST');
echo 'Method Not Allowed';
