<?php
/**
 * APIShift Engine v1.0.0
 * 
 * Copyright 2020-present Sapir Shemer, DevShift (devshift.biz)
 * 
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *  http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * 
 * @author Sapir Shemer
 */

namespace APIShift\Controllers\Main;

use APIShift\Core;
use APIShift\Core\Configurations;
use APIShift\Core\Status;
use APIShift\Models;

/**
 * Controller interface that holds all the request triggers that help install the system
 */
class Installer {
    /**
     * Request that runs the full installation process
     */
    public static function runInstallation() {
        // Allow installation only for admins or when system installed
        if(Configurations::INSTALLED) Core\Authorizer::authorizeByState();
        

        // Validate data
        Models\Main\Installer::validate();
        // Create DB
        Models\Main\Installer::createDB($_POST['db_host'], $_POST['db_name'], $_POST['db_user'], $_POST['db_pass'], $_POST['db_port'], $_POST['login'], $_POST['password']);

        // Create Config File
        Models\Main\Installer::createConfigFile($_POST['db_host'], $_POST['db_name'], $_POST['db_user'], $_POST['db_pass'], $_POST['db_port'], "MySQL", $_POST['cc_system'],
        $_POST['cc_host'], $_POST['cc_port'], $_POST['cc_pass']);

        // Done
        Status::message(Status::SUCCESS, "Installation Done!");
    }

}
?>