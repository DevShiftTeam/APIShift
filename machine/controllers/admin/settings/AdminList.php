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
 * @author Ilan Dazanashvili
 */

namespace APIShift\Controllers\Admin\Settings;

use APIShift\Core\CacheManager;
use APIShift\Core\DataManager;
use APIShift\Core\DatabaseManager;
use APIShift\Core\Process;
use APIShift\Core\Status;
use APIShift\Core\Task;

/**
 * Interface containing the available request to manipulate access rules to controllers
 */
class AdminList
{
    /**
     * Returns all the controllers and their access tasks
     */
    public static function getAdminsList()
    {
        $data_to_load = [];
        if(DatabaseManager::fetchInto("main", $data_to_load, "SELECT * FROM `admin_users`") === false)
                Status::message(Status::ERROR, "Couldn't retrieve `databasesgit a` from DB");
        
        Status::message(Status::SUCCESS, $data_to_load);
    }

    /**
     * Create a new admin 
     */
    public static function createAdmin()
    {
        // Name is required
        if(!isset($_POST['name'])) Status::message(Status::ERROR, "At least specify a name");
        if(!isset($_POST['path'])) Status::message(Status::ERROR, "At least specify a path");
        if(!isset($_POST['icon'])) Status::message(Status::ERROR, "At least specify a icon");
        if(!isset($_POST['parent'])) Status::message(Status::ERROR, "At least specify a parent");

        // Add the session state
        $res = DatabaseManager::query("main",
            "INSERT INTO `admin_users` (name, path, icon, parent) VALUES (:name, :path, :icon, :parent)", $_POST);
        if(!$res) Status::message(Status::ERROR, "Couldn't insert into the DB");

        // Refresh Cache to apply changes
        CacheManager::loadDefaults(true);
        Status::message(Status::SUCCESS, "Added Successfully! :)");
    }

    /**
     * Edit a admin
     */
    public static function editPage()
    {
        if(!isset($_POST['id'])) Status::message(Status::ERROR, "Didn't set element to modify");
        // Construct Query string
        $qstr = [];
        foreach($_POST as $key => $value) {
            if($key == 'id') continue;
            $qstr[] = $key . " = " . ":" . $key;
        }
        $qstr = implode(", ", $qstr);

        // Update the data
        $res = DatabaseManager::query("main", "UPDATE `admin_pages` SET " . $qstr . " WHERE id = :id", $_POST);
        if(!$res) Status::message(Status::ERROR, "Couldn't update the DB");

        // Refresh Cache to apply changes
        CacheManager::loadDefaults(true);
        Status::message(Status::SUCCESS, "Edited Successfully! :)");
    }
    /**
     * Remove a page .
     */
    public static function removePage()
    {
        if(!isset($_POST['id'])) Status::message(Status::ERROR, "Didn't set element to remove");
        // Remove the element
        $res = DatabaseManager::query("main", "DELETE FROM `admin_pages` WHERE id = :id", $_POST);
        if(!$res) Status::message(Status::ERROR, "Couldn't update the DB");

        // Refresh Cache to apply changes
        CacheManager::loadDefaults(true);
        Status::message(Status::SUCCESS, "Updated Successfully! :)");
    }
}
