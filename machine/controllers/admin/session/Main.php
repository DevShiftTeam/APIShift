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

namespace APIShift\Controllers\Admin\Session;

use APIShift\Core;
use APIShift\Core\CacheManager;
use APIShift\Core\DatabaseManager;
use APIShift\Core\Status;

/**
 * Provides a set of request handlers that allows users to manipulate the session state element
 */
class Main {
    /**
     * Get all the session states available
     */
    public static function getAllSessionStates() {
        Status::message(Status::SUCCESS, CacheManager::get("session_states"));
    }

    /**
     * Get the current session state used
     */
    public static function getCurrentSessionState() {
       Status::message(Status::SUCCESS, $_SESSION['state']);
    }

    /**
     * Change the current state to a new one.
     * Automatically run authorization process defined for the state
     */
    public static function changeState() {
        Core\SessionState::changeState($_POST['state']);
        Status::message(Status::SUCCESS, "State Changed!");
    }
    
    /**
     * Add a new session state to DB
     */
    public static function addSessionState() {
        // Name is required
        if(!isset($_POST['name'])) Status::message(Status::ERROR, "At least specify a name");
        // Set defaults if not provided
        if(!isset($_POST['active_timeout'])) $_POST['active_timeout'] = 0;
        if(!isset($_POST['inactive_timeout'])) $_POST['inactive_timeout'] = 0;
        if(!isset($_POST['parent'])) $_POST['parent'] = 0;
        // Add the session state
        $res = DatabaseManager::query("main",
            "INSERT INTO session_states (name, active_timeout, inactive_timeout, parent) VALUES (:name, :active_timeout, :inactive_timeout, :parent)", $_POST);
        if(!$res) Status::message(Status::ERROR, "Couldn't insert into the DB");

        // Refresh Cache to apply changes
        CacheManager::loadDefaults(true);
        Status::message(Status::SUCCESS, "Added Successfully! :)");
    }
    
    /**
     * Remove session state from DB
     */
    public static function removeSessionState() {
        if(!isset($_POST['id'])) Status::message(Status::ERROR, "Didn't set element to remove");
        // Remove the element
        $res = DatabaseManager::query("main", "DELETE FROM session_states WHERE id = :id", $_POST);
        if(!$res) Status::message(Status::ERROR, "Couldn't update the DB");

        // Refresh Cache to apply changes
        CacheManager::loadDefaults(true);
        Status::message(Status::SUCCESS, "Updated Successfully! :)");
    }
    
    /**
     * Update session state in DB
     */
    public static function updateSessionState() {
        if(!isset($_POST['id'])) Status::message(Status::ERROR, "Didn't set element to modify");
        // Construct Query string
        $qstr = [];
        foreach($_POST as $key => $value) {
            if($key == 'id') continue;
            $qstr[] = $key . " = " . ":" . $key;
        }
        $qstr = implode(", ", $qstr);

        // Update the data
        $res = DatabaseManager::query("main", "UPDATE session_states SET " . $qstr . " WHERE id = :id", $_POST);
        if(!$res) Status::message(Status::ERROR, "Couldn't update the DB");

        // Refresh Cache to apply changes
        CacheManager::loadDefaults(true);
        Status::message(Status::SUCCESS, "Updated Successfully! :)");
    }
}
?>