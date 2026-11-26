<?php

declare(strict_types=1);

// Os valores vêm do Compose; o banco é acessado pela rede interna do Docker.
return [
    'host' => 'db',
    'port' => 3306,
    'database' => 'blog',
    'username' => getenv('DB_USER'),
    'password' => getenv('DB_PASSWORD'),
    'charset' => 'utf8mb4',
];
