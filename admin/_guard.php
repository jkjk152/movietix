<?php
require __DIR__ . '/../db.php';
if (!isAdmin()) {
    http_response_code(403);
    exit('Forbidden');
}
