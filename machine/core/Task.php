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
     * Checks if a request exiasts as a task
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
        $req_auth = CacheManager::get("request_authorization");
        // Search string to help itereate request validation
        $controller_search_str = $controller;

        // Find all tasks associated to request
        foreach($req_auth as $key => $val)
            if(self::isDirectiveMatching($val['controller'], $controller) && self::isDirectiveMatching($val['method'], $method))
                $task_list[] = $val['task'];
        
        // Run valid tasks
        return Task::run($task_list);
    }

    /**
     * Run a task list and store results
     * 
     * @param array|int $task_list The tasks IDs to run
     * @param array& $params Refernce to the parameters to use for authentication
     * 
     * @return array Collection of the results of the tasks
     */
    public static function run($task_list = [], &$params = []) {
        // Add to array if not array so that no modification to the code will be added
        if(!is_array($task_list)) $task_list = [$task_list];
        
        $results = []; // results collection
        $processes_to_compile = []; // Processes to compile

        // Load procedure connections from DB
        DatabaseManager::fetchInto("main", $processes_to_compile, 
            "SELECT processes.id AS proc_id, connections.* FROM tasks
                JOIN task_processes ON tasks.id = task_processes.task
                JOIN processes ON processes.id = task_processes.process
                JOIN process_connections ON process_connections.process = processes.id
                JOIN connections ON connections.id = process_connections.connection WHERE tasks.id IN (:task_ids)",
            array("task_ids" => implode(',', $task_list)), 'proc_id', false
        );

        // Run all task processes
        foreach($task_list as $task)
        {
            $results[$task] = [];
            // Loop through connection & compile to reach result
            foreach($processes_to_compile as $process) {
                // Order connections by IDs
                $ordered_connections = [];
                foreach($process as $connection) {
                    $ordered_connections[$connection['id']] = $connection;
                    unset($ordered_connections[$connection['id']]['id']);
                }

                // Compile & store result
                $results[$task][] = Process::compileConnections($ordered_connections, $params);
            }
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
}
?>