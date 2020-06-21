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

namespace APIShift\Controllers\Admin\Access;

use APIShift\Core\CacheManager;
use APIShift\Core\DatabaseManager;
use APIShift\Core\Status;

/**
 * Interface containing the available request to manipulate access rules to controllers
 */
class Controller
{
    /**
     * Returns all the controllers and their access tasks
     */
    public static function getControllersTasks()
    {
        $res = [];
        if (DatabaseManager::fetchInto("main", $res, "SELECT * FROM tasks JOIN request_authorization ON tasks.id = request_authorization.task") === false)
            Status::message(Status::ERROR, "Couldn't retrieve controller tasks");;
        Status::message(Status::SUCCESS, $res);
    }

    /**
     * Create a new access rule
     */
    public static function createAccessRule()
    {
        // Determine the method of authentication
        switch ($_POST['type']) {
            case "State":
                // Check if state exists
                $states = CacheManager::get("session_states");
                if (!isset($states[$_POST['rule']['val']]) && $_POST['rule']['val'] != 0) Status::message(Status::ERROR, "State doesn't exist");

                // TODO: Create input list
                // TODO: Create entry in request_authorization with task 3 and input group

                // Check if state task exists
                $name = 'state_' . $_POST['rule']['text'];
                $task_res = [];
                $input_id = 0;
                DatabaseManager::fetchInto("main", $task_res, "SELECT id FROM inputs WHERE name = :name", ['name' => $name]);
                if (!is_array($task_res) || count($task_res) == 0) {
                    // Add input collection with the session state name
                    DatabaseManager::query("main", "INSERT INTO inputs (name) VALUES (:name)", ['name' => $name]);
                    DatabaseManager::fetchInto("main", $task_res, "SELECT id FROM inputs WHERE name = :name", ['name' => $name]);
                    $input_id = $task_res[0]['id'];

                    // Add session state ID value
                    DatabaseManager::query("main", "INSERT INTO input_values (id, `value`, is_source, name) VALUES (:input_id, :state_id, 0, :name)", [
                        'input_id' => $input_id,
                        'state_id' => $_POST['rule']['val'],
                        'name' => 'state_id'
                    ]);
                } else $input_id = $task_res[0]['id'];

                // Assign task to controller
                $result = DatabaseManager::query(
                    "main",
                    "INSERT INTO request_authorization (controller, method, task, input) VALUES (:controller, :method, :auth, :input)",
                    [
                        'controller' => $_POST['controller'],
                        'method' => $_POST['method'],
                        'auth' => 3, // The 'state_auth' task
                        'input' => $input_id
                    ]
                );
                if (!$result) Status::message(Status::ERROR, "Couldn't create request authorization in DB");
                break;
            case "Function":
                // TODO: check if function exists
                // TODO: add function as task
                // TODO: assign task to controller
                break;
            case "Task":
                // TODO: check if task exists
                // TODO: assign task to controller
                break;
            default:
                Status::message(Status::ERROR, "Unknown rule type, please select a function, state or task.");
        }

        Status::message(Status::SUCCESS, "Created Successfully!");
    }

    /**
     * Edit an acess rule
     */
    public static function editAccessRule()
    {
        // TODO: Create/Get access rule as task if changed
        // TODO: assign task to designated element
    }

    /**
     * Remove an access rule.
     * The structure of the request should like like this:
     * [
     *  'elem' => To which element to apply the auth rule (session, controller, database)
     *  'id' => Element ID to remove from
     * ]
     */
    public static function removeAccessRule()
    {
        $res = DatabaseManager::query("main", "DELETE FROM request_authorization WHERE id = :id", ['id' => $_POST['id']]);
        if (!$res) Status::message(Status::ERROR, "Couldn't remove rule from DB");
        Status::message(Status::SUCCESS, "Updated Successfully!");
    }
}
