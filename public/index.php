<?php

declare(strict_types = 1);

use Masyasmv\Messaging\Http\Publisher;

require __DIR__ . '/../vendor/autoload.php';


if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    echo "OK";
    return;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $pub = new Publisher();
    $pub->publish($input);
    header('Content-Type: application/json');
    echo json_encode(['status' => 'published']);
    return;
}
http_response_code(405);
