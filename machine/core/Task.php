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

namespace APIShift\Core;

/**
 * Interface to manage collections of processes and call them when needed, whether for authorization, as a request, etc.
 */
class Task {
    /**
     * Places a trigger and calls all the tasks that are part of the trigger
     * 
     * @param string $ref_name Name of the trigger
     * 
     * @return void
     */
    public static function placeTrigger($ref_name) {
        // TODO: Call the tasks associated withthe trigger
        // If non are found - then do nothing
    }

    /**
     * Checks if a request exists as a task
     * 
     * @param string $controller Controller name
     * @param string $method Method name
     * 
     * @return bool TRUE if request exists as a task, FALSE otherwise
     */
    public static function requestAsTaskExists($controller, $method) {
        // TODO: Check if the controller and method have an associated class
        return false;
    }

    /**
     * Run the request task
     * 
     * @param string $controller Controller name
     * @param string $method Method name
     * 
     * @return void
     */
    public static function runRequestTask($controller, $method) {
        if(!self::requestAsTaskExists($controller, $method)) Status::message(Status::ERROR, "Controller or method are not defined");
        // TODO: Run the task associated with the request
    }

    /**
     * Matches search directive to a string, using the '*' directive
     * 
     * @param string $directive Directive to check
     * @param string $str String to match
     * 
     * @return boolean Whether matching (TRUE) or not (FALSE)
     */
    private static function isDirectiveMatching($directive, $str) {
        // Normalize data
        $directive = strtolower($directive);
        $str = strtolower($str);
        
        // Check if matches directly
        if($directive == $str || $directive == '*') return true;
        
        $dir_rules = explode('*', $directive);
        if(count($dir_rules) == 0) return false;
        $need_break = false;
        $last_pos = -1;
        $search_str = $str;
        
        // Validate matching directive
        if($dir_rules[0] == "") $last_pos = 0;
        foreach($dir_rules as $req) {
            if($req != "") {
                $match = strpos($search_str, $req);

                if(($last_pos === -1 && $match === 0) || ($match > -1 && $last_pos !== -1)) {
                    $search_str = substr($search_str, $match + strlen($req) - 1);
                    continue;
                }
                else {
                    $need_break = true;
                    break;
                }
            }
        }

        // Add task if match found
        if(!$need_break) return true;
        return false;
    }

    /**
     * Find all authorization tasks related to the current request
     * 
     * @param string $controller Name of the controller to authorize
     * @param string $method Name of the method to authorize
     * 
     * @return array Collection of results from running the tasks
     */
    public static function runAuthorizationTasks($controller, $method) {
        // Cannot continue using the DB if system not connected
        if (!Configurations::INSTALLED) return [];

        $task_list = [];
        $input_ids = [];
        $req_auth = CacheManager::get("request_authorization");
        // Search string to help itereate request validation
        $controller_search_str = $controller;

        // Find all tasks associated to request
        foreach($req_auth as $key => $val) {
            if(self::isDirectiveMatching($val['controller'], $controller) && self::isDirectiveMatching($val['method'], $method)) {
                $task_list[] = $val['task'];
                if($val['input'] != NULL || $val['input'] != 0) $input_ids[$val['task']] = $val['input'];
            }
        }
        
        // Retrieve inputs from DB
        $results = [];
        if(count($input_ids) != 0) {
            $input_values = CacheManager::get('input_values');
            foreach($input_ids as $input_id) if(isset($input_values[$input_id])) $results[$input_id] = $input_values[$input_id];
        }
        
        // Run valid tasks
        return Task::run($task_list, $results, $input_ids);
    }

    /**
     * Run a task list and store results
     * 
     * @param array|int $task_list The tasks IDs to run
     * @param array &$inputs Inputs provided for the task
     * 
     * @return array Collection of the results of the tasks
     */
    public static function run($task_list = [], $inputs = [], $task_to_inputs = []) {
        // Add to array if not array so that no modification to the code will be added
        if(!is_array($task_list)) $task_list = [$task_list];
        
        $results = []; // results collection

        // Get proccesses to compile and their connections
        $processes_to_compile = []; // Processes to compile
        $task_processes = CacheManager::get('task_processes');
        $process_connections = CacheManager::get('process_connections');
        $connections = CacheManager::get('connections');

        foreach($task_list as $task_id) {
            if(!isset($task_processes[$task_id])) continue;
            foreach($task_processes[$task_id] as $process) {
                $processes_to_compile[$process['process']] = [];
                foreach($process_connections[$process['process']] as $connection) {
                    $processes_to_compile[$process['process']][$connection['connection']] = $connections[$connection['connection']];
                }
            }
        }

        // Run all task processes
        foreach($task_list as $task)
        {
            $results[$task] = [];

            // Create task inputs list by input name
            $task_input_list = [];
            if(isset($task_to_inputs[$task])) {
                $temp = $inputs[$task_to_inputs[$task]];
                // Separate inputs by names
                foreach($temp as $key => $value) $task_input_list[$value['name']] = DataManager::getEntryValue($value['value']);
                unset($temp);
            }
            // Compile & store result
            if(!empty($task_input_list)) $GLOBALS['task_inputs'] = $task_input_list;
            // Loop through connection & compile to reach result
            foreach($processes_to_compile as $process) $results[$task][] = Process::compileConnections($process);
        }

        return $results;
    }

    /**
     * Check if out of all processes at least one succeeded
     * 
     * @param array $task_results The task results to validates
     */
    public static function validateResult($task_results = [])
    {
        // If empty return true
        if(count($task_results) == 0) return true;

        // If atleast one process result is true then the results are true
        foreach($task_results as $process_results)
            foreach($process_results as $result)
                if($result != null && $result) return true;

        // If no result is true then return false
        return false;
    }

    /**
     * Checks if a task exists
     * 
     * @param string $name The name of the task
     * 
     * @return int|bool The ID of the task or FALSE if doesn't exist
     */
    public static function taskExists($name) {
        $tasks = CacheManager::get('tasks');
        foreach($tasks as $id => $task) if($task['name'] == $name) return $id;
        return false;
    }

    /**
     * Create a new task or return it's ID if exists
     * 
     * @param string $name The name of the task
     * @param array $process_ids ID's of the processes to associate with the task
     * 
     * @return int The ID of the task
     */
    public static function createTask($name, $process_ids) {
        // If task exist then return its ID
        $task_id = self::taskExists($name);
        if($task_id) return $task_id;

        // Check if processes are valid
        $processes = CacheManager::get('processes');
        foreach($process_ids as $id)
            if(!isset($processes[$id]))
                Status::message(Status::ERROR, "Process ID's provided do not exist");

        // Add task
        $result = DatabaseManager::query("main", "INSERT INTO tasks (name) VALUES (:name)", [ "name" => $name ]);
        if(!$result) Status::message(Status::ERROR, "Couldn't create task in database");
        CacheManager::getTable('tasks', true); // Refresh cache
        $task_id = self::taskExists($name);

        // Create task<->process list
        $task_process_list = [];
        $insert_values = []; // Used for insert query
        foreach($process_ids as $id) {
            $insert_values[] = "(:task_" . $id . ", :process_" . $id . ")";
            $task_process_list['task_' . $id] = $task_id;
            $task_process_list['process_' . $id] = $id;
        }
        $query = "INSERT INTO task_processes (task, process) VALUES " . implode(",", $insert_values);

        // Connect the connections
        $result = DatabaseManager::query("main", $query, $task_process_list);
        if(!$result) { // In case of error
            // Remove process
            DatabaseManager::query("main", "DELETE FROM tasks WHERE name = :name", [ "name" => $name ]);
            CacheManager::getTable('tasks', true); // Refresh cache
            Status::message(Status::ERROR, "Couldn't link task with given processes in DB");
        }

        CacheManager::getTable('task_processes', true, 0, 'task', false); // Refresh cache
        return $task_id;
    }
}
?>
