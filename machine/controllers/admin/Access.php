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

namespace APIShift\Controllers\Admin;

use APIShift\Core\DatabaseManager;
use APIShift\Core\Status;

/**
 * Interface containing the available main request of the system's control panel
 */
class Access {
    /**
     * Returns all the tasks available
     */
    public static function getAllTasks() {
        $res = [];
        if(DatabaseManager::fetchInto("main", $res, "SELECT * FROM tasks") === false) Status::message(Status::ERROR, "Couldn't retrieve tasks");;
        Status::message(Status::SUCCESS, $res);
    }

    /**
     * Returns all the controllers and their access tasks
     */
    public static function getControllersTasks() {
        $res = [];
        if(DatabaseManager::fetchInto("main", $res, "SELECT * FROM tasks JOIN request_authorization ON tasks.id = request_authorization.task") === false)
            Status::message(Status::ERROR, "Couldn't retrieve controller tasks");;
        Status::message(Status::SUCCESS, $res);
    }

    /**
     * Create a new access rule
     */
    public static function createAccessRule() {
        // TODO: Create/get access rule as task
        // TODO: assign task to designated element
        if(!isset($_POST['elem']) || $_POST['elem'] == "")
            Status::message(Status::ERROR, "No element to assign access rule was specified");
        
        // Determine to which system elemect the rule applies
        switch($_POST['elem']) {
            case 'session':
                // TODO: finish here
                break;
            case 'controller':
                // Determine to method of authentication
                switch($_POST['rule']['type']) {
                    case "State":
                        // TODO: check if state exists
                        // TODO: add state as task
                        // TODO: assign task to controller
                        break;
                    case "Function":
                        // TODO: check if function exists
                        // TODO: add function as task
                        // TODO: assign task to controller
                        break;
                    default:
                        // TODO: check if task exists
                        // TODO: assign task to controller
                        break;
                }
                break;
            case 'database':
                // TODO: Finish database access rule interface
                break;
            default: Status::message(Status::ERROR, "Unknown system element");
        }

        Status::message(Status::SUCCESS, "Created Successfully!");
    }
    
    /**
     * Edit an acess rule
     */
    public static function editAccessRule() {
        // TODO: Create/Get access rule as task if changed
        // TODO: assign task to designated element
    }

    /**
     * Remove an access rule
     */
    public static function removeAccessRule() {
        if(!isset($_POST['elem']) || $_POST['elem'] == "")
            Status::message(Status::ERROR, "No element to assign access rule was specified");
        
        // Determine to which system elemect the rule applies
        switch($_POST['elem']) {
            case 'session':
                $res = DatabaseManager::query("main", "UPDATE session_states SET auth_task = NULL WHERE id = :id", $_POST['rule']);
                if(!$res) Status::message(Status::ERROR, "Couldn't remove rule from DB");
                break;
            case 'controller':
                $res = DatabaseManager::query("main", "DELETE FROM request_authorization WHERE id = :id", [ 'id' => $_POST['id']]);
                if(!$res) Status::message(Status::ERROR, "Couldn't remove rule from DB");
                break;
            case 'database':
                // TODO: Finish database access rule interface
                break;
            default: Status::message(Status::ERROR, "Unknown system element");
        }

        Status::message(Status::SUCCESS, "Updated Successfully!");
    }
}
?>