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
 * @author Sagi Weizmann
 */

namespace APIShift\Controllers\Admin\Database;

use APIShift\Core\CacheManager;
use APIShift\Core\DataManager;
use APIShift\Core\DatabaseManager;
use APIShift\Core\Process;
use APIShift\Core\Status;
use APIShift\Core\Task;

/**
 * Interface containing the available request to manipulate access rules to controllers
 */
class DatabaseList
{
    /**
     * Returns all the controllers and their access tasks
     */
    public static function getDatabaseList()
    {
        $data_to_load = [];
        if(DatabaseManager::fetchInto("main", $data_to_load, "SELECT * FROM `databases`") === false)
                Status::message(Status::ERROR, "Couldn't retrieve `databasesgit a` from DB");
        
        Status::message(Status::SUCCESS, $data_to_load);
    }

    /**
     * Create a new database 
     */
    public static function createDatabase()
    {
        // Name is required
        if(!isset($_POST['name'])) Status::message(Status::ERROR, "At least specify a name");
        if(!isset($_POST['host'])) Status::message(Status::ERROR, "At least specify a host");
        if(!isset($_POST['user'])) Status::message(Status::ERROR, "At least specify a user");
        if(!isset($_POST['pass'])) Status::message(Status::ERROR, "At least specify a password");
        if(!isset($_POST['db'])) Status::message(Status::ERROR, "At least specify a databse");
        // Set defaults if not provided
        if(!isset($_POST['port'])) $_POST['port'] = 3306;

        // Add the session state
        $res = DatabaseManager::query("main",
            "INSERT INTO `databases` (name, host, user, pass, db, port) VALUES (:name, :host, :user, :pass, :db, :port)", $_POST);
        if(!$res) Status::message(Status::ERROR, "Couldn't insert into the DB");

        // Refresh Cache to apply changes
        CacheManager::loadDefaults(true);
        Status::message(Status::SUCCESS, "Added Successfully! :)");
    }

    /**
     * Edit a database
     */
    public static function editDatabase()
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
        $res = DatabaseManager::query("main", "UPDATE `databases` SET " . $qstr . " WHERE id = :id", $_POST);
        if(!$res) Status::message(Status::ERROR, "Couldn't update the DB");

        // Refresh Cache to apply changes
        CacheManager::loadDefaults(true);
        Status::message(Status::SUCCESS, "Edited Successfully! :)");
    }

    /**
     * Remove a database .
     */
    public static function removeDatabase()
    {
        if(!isset($_POST['id'])) Status::message(Status::ERROR, "Didn't set element to remove");
        // Remove the element
        $res = DatabaseManager::query("main", "DELETE FROM `databases` WHERE id = :id", $_POST);
        if(!$res) Status::message(Status::ERROR, "Couldn't update the DB");

        // Refresh Cache to apply changes
        CacheManager::loadDefaults(true);
        Status::message(Status::SUCCESS, "Updated Successfully! :)");
    }
}
