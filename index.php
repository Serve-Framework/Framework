<?php

use serve\application\web\Application;
use function dirname;

/**
 * Include the application init file.
 */
include dirname(__DIR__) . '/app/init.php';

/*
 * Start and run the application.
 */
Application::start(SERVE_APPLICATION_PATH)->run();
