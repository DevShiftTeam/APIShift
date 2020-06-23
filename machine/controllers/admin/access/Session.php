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
 * Interface containing the available main request of the system's control panel
 */
class Session {
    /**
     * Returns all the sessions and their access tasks
     */
    public static function getSessionTasks()
    {
        $res = [];
        if (DatabaseManager::fetchInto("main", $res,
            "SELECT inputs.name as input_name, tasks.name as task_name, tasks.*, session_states.* FROM session_states
                LEFT JOIN tasks ON tasks.id = session_states.auth_task
                LEFT JOIN inputs ON session_states.auth_input = inputs.id") === false)
            Status::message(Status::ERROR, "Couldn't retrieve session tasks");

        // Get rid of null's
        foreach($res as &$row) foreach($row as &$val) if($val == null) $val = "";

        Status::message(Status::SUCCESS, $res);
    }

    /**
     * Edit an acess rule
     */
    public static function editAccessRule()
    {
        if(!isset($_POST['id'])) Status::message(Status::ERROR, "Please provide a session ID to edit");
        if(count($_POST) == 1) Status::message(Status::ERROR, "No data to edit provided");

        // Stores the new values assigned to the rule
        $new_values = [];

        // Check if rule exists
        $states = CacheManager::get("session_states");
        if (!isset($states[$_POST['id']])) Status::message(Status::ERROR, "State doesn't exist");

        // Check if data is new
        if(isset($_POST['type'])) {
            switch($_POST['type']) {
                case "State":
                    // Check if state exists
                    if (!isset($states[$_POST['rule']['val']]) && $_POST['rule']['val'] != 0) Status::message(Status::ERROR, "State doesn't exist");
                    $new_values['auth_task'] = 3; // The 'state_auth' task ID

                    // Check if state task exists
                    $name = 'state_' . $_POST['rule']['text'];
                    $task_res = [];
                    DatabaseManager::fetchInto("main", $task_res, "SELECT id FROM inputs WHERE name = :name", ['name' => $name]);
                    if (!is_array($task_res) || count($task_res) == 0) {
                        // Add input collection with the session state name
                        DatabaseManager::query("main", "INSERT INTO inputs (name) VALUES (:name)", ['name' => $name]);
                        DatabaseManager::fetchInto("main", $task_res, "SELECT id FROM inputs WHERE name = :name", ['name' => $name]);
                        $new_values['auth_input'] = $task_res[0]['id'];

                        // Add session state ID value
                        DatabaseManager::query("main", "INSERT INTO input_values (id, `value`, is_source, name) VALUES (:input_id, :state_id, 0, :name)", [
                            'input_id' => $new_values['input'],
                            'state_id' => $_POST['rule']['val'],
                            'name' => 'state_id'
                        ]);
                    } else $new_values['auth_input'] = $task_res[0]['id'];
                    break;
                case "Function":
                    // TODO: Check if function exists
                    // if(!is_callable($_POST['rule'])) Status::message(Status::ERROR, "Function not found");
                    // TODO: Add function as task if not exists
                    // TODO: Create function inputs as instructed
                    $new_values['auth_input'] = 0;
                    Status::message(Status::ERROR, "Comming Soon!");
                    break;
                case "Task":
                    $new_values['auth_input'] = 0;
                    if(isset($_POST['rule']['val'])) $new_values['auth_task'] = $_POST['rule']['val'];
                    break;
                default: Status::message(Status::ERROR, "Unkown rule type");
            }

            // Unset input values if they are already set
            if($new_values['auth_input'] == $states[$_POST['id']]['auth_input']) unset($new_values['auth_input']);
        }

        if(count($new_values) == 0) Status::message(Status::ERROR, "Nothing to change");

        // Create query string dynamically
        $query = "UPDATE session_states SET ";
        foreach ($new_values as $name => $value) $query .= "`" . $name . "` = :" . $name . ", ";
        $query = mb_strimwidth($query, 0, strlen($query) - 2); // Get rid of last comma
        $query .= " WHERE id = :id";
        $new_values['id'] = $_POST['id'];

        // Update in database
        $result = DatabaseManager::query("main", $query, $new_values);
        if (!$result) Status::message(Status::ERROR, "Couldn't edit session state in DB");

        CacheManager::getTable("session_states", true); // Refresh cache
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
        $res = DatabaseManager::query("main", "UPDATE session_states SET auth_task = NULL WHERE id = :id", [ 'id' => $_POST['id']]);
        if (!$res) Status::message(Status::ERROR, "Couldn't remove rule from DB");
        CacheManager::getTable("session_states", true); // Refresh cache
        Status::message(Status::SUCCESS, "Updated Successfully!");
    }
}