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

namespace APIShift\Controllers\Admin\Logic;

use APIShift\Core\CacheManager;
use APIShift\Core\DatabaseManager;
use APIShift\Core\Status;
use APIShift\Core\Task;

/**
 * Provides a set of request handlers that allows users to manipulate Tasks in the system
 */
class Tasks {
    /**
     * Get all the Tasks available in system
     */
    public static function getAllTasks() {
        // Extract Task data from system
        $tasks_rows = CacheManager::get("tasks");
        $task_processes = CacheManager::get("task_processes");

        // Normalize Task row data to a notionally common data
        foreach ($tasks_rows as $task_id => $task) {
            $tasks_rows[$task_id]['id'] = $task_id;
            $tasks_rows[$task_id]['processes'] = isset($task_processes[$task_id]) ? array_map(function($item) {
                return $item['process'];
            }, array_values($task_processes[$task_id])) : [];
        }

        Status::message(Status::SUCCESS, array_values($tasks_rows));
    }


    /**
     * Update Process list connections of a Task 
     */
    public static function updateTask() {
        if (!isset($_POST['id'])) Status::message(Status::ERROR, "Please specify id");
        if (!isset($_POST['name']) || empty($_POST['name'])) Status::message(Status::ERROR, "Please specify name");
        if (!isset($_POST['processes'])) $_POST['processes'] = [];
        
        // Update Task name
        $res = DatabaseManager::query("main", "UPDATE `tasks` SET name = :name WHERE id = :id" ,['name' => $_POST['name'], 'id' => $_POST['id']]);
        if(!$res) Status::message(Status::ERROR, "Couldnt update the DB");

        // Update the data
        $res = DatabaseManager::query("main", "DELETE from `task_processes` WHERE task = :id", ['id' => $_POST['id']]);
        if(!$res) Status::message(Status::ERROR, "Couldn't update the DB");

        if (!empty($_POST['processes'])) {
            // Create task<->process list
            $task_process_list = [];
            $insert_values = []; // Used for insert query
            foreach($_POST['processes'] as $id) {
                $insert_values[] = "(:task_" . $id . ", :process_" . $id . ")";
                $task_process_list['task_' . $id] = $_POST['id'];
                $task_process_list['process_' . $id] = $id;
            }

            $query = "INSERT INTO task_processes (task, process) VALUES " . implode(",", $insert_values);

            // Connect the connections
            $res = DatabaseManager::query("main", $query, $task_process_list);
            if(!$res) Status::message(Status::ERROR, $query);
        }
        
        // Refresh Cache to apply changes
        CacheManager::loadDefaults(true);
        Status::message(Status::SUCCESS, "Updated Successfully! :)");
    }

    /**
     * Create a new Task and attach processes to it
     */
    public static function createTask() {
        if (!isset($_POST['name']) || empty($_POST['name'])) Status::message(Status::ERROR, "Please specify name");
        if (!isset($_POST['processes'])) $_POST['processes'] = [];

        // Create Task (cache refreshed internally)
        Task::createTask($_POST['name'], $_POST['processes']);

        // Respond with 200 OK
        Status::message(Status::SUCCESS, "Updated Successfully! :)");
    }

    /**
     * Create a new Task and attach processes to it
     */
    public static function deleteTask() {
        if (!isset($_POST['id'])) Status::message(Status::ERROR, "Please specify Task");

        // Delete Task
        $res = DatabaseManager::query("main", "DELETE FROM tasks WHERE id = :id", $_POST);
        if (!$res) Status::message(Status::SUCCESS, "Error in Task deletion");

        // Refresh Cache to apply changes
        CacheManager::loadDefaults(true);
        Status::message(Status::SUCCESS, "Updated Successfully! :)");
    }
}
?>