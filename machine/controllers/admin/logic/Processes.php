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
use APIShift\Core\Status;
use ReflectionFunction;

/**
 * Provides a set of request handlers that allows users to manipulate Tasks in the system
 */
class Processes {
    /**
     * Get all the Tasks available
     */
    public static function getAllProcesses() {
        // Extract Processes data from system
        $processes = CacheManager::get("processes");

        foreach ($processes as $id => $process) {
            $processes[$id]['id'] = $id;
        }

        Status::message(Status::SUCCESS, array_values($processes));
    }

    /**
     * Get Connection list & data of a specified process, let the client to construct the data properly
     */
    public static function getProcessData() {
        if (!isset($_POST['id'])) Status::message(Status::ERROR, "Please specify id");

         // Extract Process data from system
        $process_connections = CacheManager::get("process_connections")[$_POST['id']];
        $connections = CacheManager::get("connections");
        $data_entries = CacheManager::get("data_entries");
        $data_sources = CacheManager::get("data_sources");
        $connection_node_types = CacheManager::get("connection_node_types");
        $connection_types = CacheManager::get("connection_types");

        // Add params to Functions
        foreach ($connections as &$connection) {
            if(!isset($connection_types[$connection["connection_type"]]))
                continue;
            if($connection_types[$connection["connection_type"]]["name"] == "Function") {
                $connection["params"] = (new ReflectionFunction($connection["name"]))->getParameters();
            }
        }

        $response = [
            "process_connections" => $process_connections, 
            "connections" => $connections,
            "data_entries" => $data_entries, 
            "data_sources" => $data_sources,
            "connection_node_types" => $connection_node_types,
            "connection_types" => $connection_types
        ];

        Status::message(Status::SUCCESS, $response);
    }
}
?>