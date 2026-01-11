<?php
declare(strict_types=1);

// Minimal ping endpoint for latency measurement
header('Content-Type: application/json');

echo json_encode([
    'success' => true,
    'pong' => true,
    'ts' => microtime(true),
]);
exit;