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
use APIShift\Core\DataManager;
use APIShift\Core\DatabaseManager;
use APIShift\Core\Process;
use APIShift\Core\Status;
use APIShift\Core\Task;

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
        $tasks = CacheManager::get('tasks');
        $req_auth = CacheManager::get('request_authorization');
        $inputs = CacheManager::get('inputs');
        $counter = 0;

        foreach($req_auth as $key => $val) {
            $res[$counter] = $val;
            $res[$counter]['id'] = $key;
            $res[$counter]['task_name'] = $tasks[$val['task']]['name'];
            $res[$counter]['input_name'] = $inputs[$val['input']]['name'];
            $counter++;
        }

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
                $inputs = CacheManager::get('inputs');
                $input_test = false;
                $input_id = 0;

                // Check by inputs
                foreach($inputs as $key => $val) {
                    if ($val['name'] == $name) { 
                        $input_test = true;
                        $input_id = $key;
                        break;
                    }
                }

                // Add corresponding input is missing
                if (!$input_test) {
                    // Add input collection with the session state name
                    DatabaseManager::query("main", "INSERT INTO inputs (name) VALUES (:name)", ['name' => $name]);
                    CacheManager::getTable('inputs', true); // Refresh cache
                    $inputs = CacheManager::get('inputs');

                    // Get the ID of the newly added input
                    foreach($inputs as $key => $val) {
                        if ($val['name'] == $name) { 
                            $input_id = $key;
                            break;
                        }
                    }

                    // Add session state ID value
                    DatabaseManager::query("main", "INSERT INTO input_values (id, `value`, is_source, name) VALUES (:input_id, :state_id, 0, :name)", [
                        'input_id' => $input_id,
                        // Will create a new entry if not exists
                        'state_id' => DataManager::createEntry($_POST['rule']['val'], 3, 0),
                        'name' => 'state_id'
                    ]);
                    CacheManager::getTable('input_values', true); // Refresh cache
                    CacheManager::getTable('inputs', true); // Refresh cache
                }

                // Assign task to controller
                $result = DatabaseManager::query(
                    "main",
                    "INSERT INTO request_authorization (controller, method, task, input) VALUES (:controller, :method, :auth, :input)",
                    [
                        'controller' => $_POST['controller'],
                        'method' => $_POST['method'],
                        'auth' => 2, // The 'state_auth' task
                        'input' => $input_id
                    ]
                );
                if (!$result) Status::message(Status::ERROR, "Couldn't create request authorization in DB");
                break;
            case "Function":
                // Check if function exists
                if(!is_callable($_POST['rule'])) Status::message(Status::ERROR, "Function not found");

                // Get task ID
                $task_name = 'function_' . $_POST['rule'];
                $task_id = 0;
                $tasks = CacheManager::get('tasks');
                foreach($tasks as $id => $task) if($task['name'] == $task_name) $task_id = $id;
                // Add function as task if not exists
                if($task_id == 0) {
                    // Add connection and get its ID
                    $connection_id = Process::createConnection($_POST['rule'], 0, null, null, null, null);
                    // Add process and get it's ID
                    $process_id = Process::createProcess($task_name, [ $connection_id ]);
                    // Create task and get it's ID
                    $task_id = Task::createTask($task_name, [ $process_id ]);
                }
                
                // Assign task to controller
                $result = DatabaseManager::query(
                    "main",
                    "INSERT INTO request_authorization (controller, method, task, input) VALUES (:controller, :method, :auth, :input)",
                    [
                        'controller' => $_POST['controller'],
                        'method' => $_POST['method'],
                        'auth' => $task_id,
                        'input' => 0
                    ]
                );
                if (!$result) Status::message(Status::ERROR, "Couldn't create request authorization in DB");
                break;
            case "Task":
                // Check if task exists
                if(!isset(CacheManager::get('tasks')[$_POST['rule']['val']])) Status::message(Status::ERROR, "Task wasn't found");
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

        CacheManager::getTable('request_authorization', true); // Refresh cache
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
        $check_rule = CacheManager::get('request_authorization');
        if(!isset($check_rule[$_POST['id']])) Status::message(Status::ERROR, "Rule ID not found");
        $check_rule = $check_rule[$_POST['id']];

        // Check if data is new
        if(isset($_POST['type'])) {
            switch($_POST['type']) {
                case "State":
                    // Check if state exists
                    $states = CacheManager::get("session_states");
                    if (!isset($states[$_POST['rule']['val']]) && $_POST['rule']['val'] != 0) Status::message(Status::ERROR, "State doesn't exist");
                    $new_values['task'] = 2; // The 'state_auth' task ID

                    // Check if state task exists
                    $name = 'state_' . $_POST['rule']['text'];
                    $inputs = CacheManager::get('inputs');
                    $input_test = false;
                    $input_id = 0;
    
                    // Check by inputs
                    foreach($inputs as $key => $val) {
                        if ($val['name'] == $name) { 
                            $input_test = true;
                            $new_values['input'] = $key;
                            break;
                        }
                    }
    
                    // Add corresponding input is missing
                    if (!$input_test) {
                        // Add input collection with the session state name
                        DatabaseManager::query("main", "INSERT INTO inputs (name) VALUES (:name)", ['name' => $name]);
                        CacheManager::getTable('inputs', true); // Refresh cache
                        $inputs = CacheManager::get('inputs');
                        
                        // Get the ID of the newly added input
                        foreach($inputs as $key => $val) {
                            if ($val['name'] == $name) { 
                                $new_values['input'] = $key;
                                break;
                            }
                        }

                        // Add session state ID value
                        DatabaseManager::query("main", "INSERT INTO input_values (id, `value`, is_source, name) VALUES (:input_id, :state_id, 0, :name)", [
                            'input_id' => $new_values['input'],
                            'state_id' => DataManager::createEntry($_POST['rule']['val'], 3, 0),
                            'name' => 'state_id'
                        ]);
                        CacheManager::getTable('input_values', true); // Refresh cache
                        CacheManager::getTable('inputs', true); // Refresh cache
                    }
                    break;
                case "Function":
                    // Check if function exists
                    if(!is_callable($_POST['rule'])) Status::message(Status::ERROR, "Function not found");

                    // Get task ID
                    $task_name = 'function_' . $_POST['rule'];
                    $task_id = 0;
                    $task_id = Task::taskExists($task_name);
                    // Add function as task if not exists
                    if(!$task_id) {
                        // Add connection and get its ID
                        $connection_id = Process::createConnection($_POST['rule'], 4, null, null, null, null);
                        // Add process and get it's ID
                        $process_id = Process::createProcess($task_name, [ $connection_id ]);
                        // Create task and get it's ID
                        $task_id = Task::createTask($task_name, [ $process_id ]);
                    }
                    
                    // TODO: Create function inputs as instructed
                    $new_values['input'] = 0;
                    $new_values['task'] = $task_id;
                    break;
                case "Task":
                    $new_values['input'] = 0;
                    if(isset($_POST['rule']['val'])) $new_values['task'] = $_POST['rule']['val'];
                    break;
                default: Status::message(Status::ERROR, "Unkown rule type");
            }

            // Unset input values if they are already set
            if($new_values['input'] == $check_rule['input']) unset($new_values['input']);
        }

        // Add controller and method if they are set
        if(isset($_POST['controller']) && $_POST['controller'] != $check_rule['controller']) $new_values['controller'] = $_POST['controller'];
        if(isset($_POST['method']) && $_POST['method'] != $check_rule['method']) $new_values['method'] = $_POST['method'];

        if(count($new_values) == 0) Status::message(Status::ERROR, "Nothing to change");

        // Create query string dynamically
        $query = "UPDATE request_authorization SET ";
        foreach ($new_values as $name => $value) $query .= "`" . $name . "` = :" . $name . ", ";
        $query = mb_strimwidth($query, 0, strlen($query) - 2); // Get rid of last comma
        $query .= " WHERE id = :id";
        $new_values['id'] = $_POST['id'];

        // Update in database
        $result = DatabaseManager::query("main", $query, $new_values);
        if (!$result) Status::message(Status::ERROR, "Couldn't edit request authorization in DB");

        CacheManager::getTable('request_authorization', true); // Refresh cache
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
        CacheManager::getTable('request_authorization', true); // Refresh cache
        Status::message(Status::SUCCESS, "Updated Successfully!");
    }
}
