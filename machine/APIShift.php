<?php
/**
 * APIShift Engine v1.0.0
 * (c) 2020-present Sapir Shemer, DevShift (devshift.biz)
 * Released under the MIT License with the additions present in the LICENSE.md
 * file in the root folder of the APIShift Engine original release source-code
 * @author Sapir Shemer
 */

// Step 1: Define the autoloader to load the needed functions without hardcoding
require_once "core/Autoloader.php";

// Step 2: Define main dependencies for the API to function

use APIShift\Core\CacheManager;
use APIShift\Core\DatabaseManager;
use APIShift\Core\SessionState;
use APIShift\Core\Configurations;

// Step 3: Redirect to HTTPS connection only
if (Configurations::USE_HTTPS && empty($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === "off") {
    $location = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    header('HTTP/1.1 301 Moved Permanently');
    header('Location: ' . $location);
    exit;
}

// Step 4: Start main connection & load default cache & session if not present
if(Configurations::INSTALLED) {
    DatabaseManager::startConnection("main");
    CacheManager::loadDefaults();
    SessionState::loadDefaults();
}