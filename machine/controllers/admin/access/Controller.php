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
        if (DatabaseManager::fetchInto("main", $res,
            "SELECT inputs.name as input_name, tasks.*, request_authorization.* FROM tasks
                JOIN request_authorization ON tasks.id = request_authorization.task
                LEFT JOIN inputs ON request_authorization.input = inputs.id") === false)
            Status::message(Status::ERROR, "Couldn't retrieve controller tasks");
        Status::message(Status::SUCCESS, $res);
    }

    /**
     * Create a new access rule
     */
    public static function createAccessRule()
    {
        if(!isset($_POST['type']) || !isset($_POST['rule'])) Status::message(Status::ERROR, "Please provide the type and the rule access rule");
        // Determine the method of authentication
        switch ($_POST['type']) {
            case "State":
                // Check if state exists
                $states = CacheManager::get("session_states");
                if (!isset($states[$_POST['rule']['val']]) && $_POST['rule']['val'] != 0) Status::message(Status::ERROR, "State doesn't exist");

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
                // Check if function exists
                // if(!is_callable($_POST['rule'])) Status::message(Status::ERROR, "Function not found");
                // TODO: Add function as task if not exists
                // TODO: Create function inputs as instructed
                // TODO: Assign task to controller
                Status::message(Status::ERROR, "Comming Soon!");
                break;
            case "Task":
                // Check if task exists
                $check_task = [];
                DatabaseManager::fetchInto("main", $check_task, "SELECT * FROM tasks WHERE id = :id", [ 'id' => $_POST['rule']['val'] ]);
                if(!is_array($check_task) || count($check_task) == 0) Status::message(Status::ERROR, "Task wasn't found");
                // Assign task to controller
                $result = DatabaseManager::query(
                    "main",
                    "INSERT INTO request_authorization (controller, method, task, input) VALUES (:controller, :method, :auth, NULL)",
                    [
                        'controller' => $_POST['controller'],
                        'method' => $_POST['method'],
                        'auth' => $_POST['rule']['val']
                    ]
                );
                if (!$result) Status::message(Status::ERROR, "Couldn't create request authorization in DB");
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
        if(!isset($_POST['id'])) Status::message(Status::ERROR, "Please provide a rule ID to edit");
        if(count($_POST) == 1) Status::message(Status::ERROR, "No data to edit provided");

        // Stores the new values assigned to the rule
        $new_values = [];

        // Check if rule exists
        $check_rule = [];
        DatabaseManager::fetchInto("main", $check_rule, "SELECT * FROM request_authorization WHERE id = :id", [ 'id' => $_POST['id'] ]);
        if(!is_array($check_rule) || count($check_rule) == 0) Status::message(Status::ERROR, "Rule ID not found");

        // Check if data is new
        if(isset($_POST['type'])) {
            switch($_POST['type']) {
                case "State":
                    // Check if state exists
                    $states = CacheManager::get("session_states");
                    if (!isset($states[$_POST['rule']['val']]) && $_POST['rule']['val'] != 0) Status::message(Status::ERROR, "State doesn't exist");
                    $new_values['task'] = 3; // The 'state_auth' task ID

                    // Check if state task exists
                    $name = 'state_' . $_POST['rule']['text'];
                    $task_res = [];
                    DatabaseManager::fetchInto("main", $task_res, "SELECT id FROM inputs WHERE name = :name", ['name' => $name]);
                    if (!is_array($task_res) || count($task_res) == 0) {
                        // Add input collection with the session state name
                        DatabaseManager::query("main", "INSERT INTO inputs (name) VALUES (:name)", ['name' => $name]);
                        DatabaseManager::fetchInto("main", $task_res, "SELECT id FROM inputs WHERE name = :name", ['name' => $name]);
                        $new_values['input'] = $task_res[0]['id'];

                        // Add session state ID value
                        DatabaseManager::query("main", "INSERT INTO input_values (id, `value`, is_source, name) VALUES (:input_id, :state_id, 0, :name)", [
                            'input_id' => $new_values['input'],
                            'state_id' => $_POST['rule']['val'],
                            'name' => 'state_id'
                        ]);
                    } else $new_values['input'] = $task_res[0]['id'];
                    break;
                case "Function":
                    // TODO: Check if function exists
                    // if(!is_callable($_POST['rule'])) Status::message(Status::ERROR, "Function not found");
                    // TODO: Add function as task if not exists
                    // TODO: Create function inputs as instructed
                    $new_values['input'] = 0;
                    Status::message(Status::ERROR, "Comming Soon!");
                    break;
                case "Task":
                    $new_values['input'] = 0;
                    if(isset($_POST['rule']['val'])) $new_values['task'] = $_POST['rule']['val'];
                    break;
                default: Status::message(Status::ERROR, "Unkown rule type");
            }

            // Unset input values if they are already set
            if($new_values['input'] == $check_rule[0]['input']) unset($new_values['input']);
        }

        // Add controller and method if they are set
        if(isset($_POST['controller']) && $_POST['controller'] != $check_rule[0]['controller']) $new_values['controller'] = $_POST['controller'];
        if(isset($_POST['method']) && $_POST['method'] != $check_rule[0]['method']) $new_values['method'] = $_POST['method'];

        if(count($new_values) == 1) Status::message(Status::ERROR, "Nothing to change");

        // Create query string dynamically
        $query = "UPDATE request_authorization SET ";
        foreach ($new_values as $name => $value) $query .= "`" . $name . "` = :" . $name . ", ";
        $query = mb_strimwidth($query, 0, strlen($query) - 2); // Get rid of last comma
        $query .= " WHERE id = :id";
        $new_values['id'] = $_POST['id'];

        // Update in database
        $result = DatabaseManager::query("main", $query, $new_values);
        if (!$result) Status::message(Status::ERROR, "Couldn't edit request authorization in DB");

        Status::message(Status::SUCCESS, "Edited Successfully!");
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
