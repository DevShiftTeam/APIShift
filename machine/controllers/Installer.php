<?php
/**
 * APIShift Engine v1.0.0
 * (c) 2020-present Sapir Shemer, DevShift (devshift.biz)
 * Released under the MIT License with the additions present in the LICENSE.md
 * file in the root folder of the APIShift Engine original release source-code
 * @author Sapir Shemer
 */

namespace APIShift\Controllers;

use APIShift\Core;
use APIShift\Core\Configurations;
use \APIShift\Core\Status;
use \APIShift\Models;

class Installer {
    public static function runInstallation() {
        // Allow installation only for admins or when system installed
        if(Configurations::INSTALLED) Core\Authorizer::authorizeByState();
        

        // Validate data
        if((!isset($_POST['login']) || strlen($_POST['login']) < 5)
        || (!isset($_POST['password']) || strlen($_POST['password']) <= 8)
        || (!isset($_POST['db_host']) || $_POST['db_host'] == "")
        || (!isset($_POST['db_port']) || $_POST['db_port'] == "")
        || (!isset($_POST['db_user']) || $_POST['db_user'] == "")
        || (!isset($_POST['db_pass']) || $_POST['db_pass'] == "")
        || (!isset($_POST['db_name']) || $_POST['db_name'] == "")
        || (!isset($_POST['cc_system']) || $_POST['cc_system'] == "")
        || (!isset($_POST['cc_host']))
        || (!isset($_POST['cc_port']))
        || (!isset($_POST['cc_pass']))
        || (!isset($_POST['site_name']) || $_POST['site_name'] == "")
        || (!isset($_POST['site_desc']) || $_POST['site_desc'] == "")
        || (!isset($_POST['site_keys']) || $_POST['site_keys'] == ""))
        {
            Status::message(Status::ERROR, "Invalid data");
        }

        // Create DB
        Models\Installer::createDB($_POST['db_host'], $_POST['db_name'], $_POST['db_user'], $_POST['db_pass'], $_POST['db_port'], $_POST['login'], $_POST['password']);

        // Create Config File
        Models\Installer::createConfigFile($_POST['db_host'], $_POST['db_name'], $_POST['db_user'], $_POST['db_pass'], $_POST['db_port'], "MySQL", $_POST['cc_system'],
        $_POST['cc_host'], $_POST['cc_port'], $_POST['cc_pass']);

        // Done
        Status::message(Status::SUCCESS, "Installation Done!");
    }
}
?>