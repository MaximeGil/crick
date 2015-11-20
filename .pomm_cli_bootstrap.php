<?php

use PommProject\Foundation\Pomm;


$loader = require __DIR__.'/vendor/autoload.php';
$loader->add(null, __DIR__);

return new Pomm(['DbGreen' =>
    [
        'dsn' => 'pgsql://maxime:maxime@localhost:5432/green',
        'class:session_builder' => '\PommProject\ModelManager\SessionBuilder',
    ]
]);