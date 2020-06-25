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
 * Provides an interface to compile and manage procedural connections in the system
 * Procedural connection are explained in the ARCHITECTURE_AND_DESIGN.md file
 */
class Process {
    /**
     * Compiles a set of connections.
     * Set of connections need to be connected either from front or back, since
     * free nodes will not produce a correct compilation process
     * 
     * @param array $connections_set The set of connection to compile
     * @param array &$inputs Refernce to the inputs to pass to the compilation process
     * 
     * @return mixed The result of the compilation
     */
    public static function compileConnections(&$connections_set) {
        // Key-value store of results of each connection
        $connection_results = [];
        $result = null;

        // Load types from cache
        $connection_types = CacheManager::get("connection_types");
        $connection_node_types = CacheManager::get("connection_node_types");

        // Get the first connection which is not a result from the connections
        $connection_ids = array_keys($connections_set);
        $current_connection = $connection_ids[0];
        if(self::isResult($connections_set[$current_connection])) $current_connection = $connection_ids[1];
        
        // Compile connections until reaching the result
        while(count($connection_results) != count($connections_set)) {
            // Simplify representation of connection types
            $from_type = $connections_set[$current_connection]['from_type'] == null ?
                '' : $connection_node_types[$connections_set[$current_connection]['from_type']]['name'];
            $to_type = $connections_set[$current_connection]['to_type'] == null ?
                '' : $connection_node_types[$connections_set[$current_connection]['to_type']]['name'];
            $type = $connections_set[$current_connection]['connection_type'] == 0 ?
                'Flow' : $connection_types[$connections_set[$current_connection]['connection_type']]['name'];

            /**
             * This section handles moving in the process graph to retrieve all the necessary results
             * and get to the start of the graph
             */

            // Move to next connection if result exists
            if(isset($connection_results[$current_connection])) {
                // Load next connection if is of type connection and no result is present
                if($to_type == "Connection" && !isset($connection_results[$connections_set[$current_connection]['to']])) {
                    $current_connection = $connections_set[$current_connection]['to'];
                }
                // Load next connection who has 'from' as this
                else {
                    $break_from_loop = false;
                    foreach($connections_set as $id => $connection) {
                        if($connection_node_types[$connection['from_type']]['name'] == "Connection"
                            && $connection['from'] == $current_connection) {
                            $current_connection = $id;
                            $break_from_loop = true;
                            break;
                        }
                    }
                    if($break_from_loop) continue;
                    // If no next found then notify user about error
                    Status::message(Status::ERROR, "Process compilation error, couldn't find next connection");
                }
                continue;
            }

            // Move to previous connection if no result exists
            if($from_type == "Connection" && !isset($connection_results[$connections_set[$current_connection]['from']])) {
                $current_connection = $connections_set[$current_connection]['from'];
                continue;
            }

            // Manuver in connectors who point to the current connection to get results
            if($from_type == 'Connection' && !isset($connection_results[$connections_set[$current_connection]['from']])) {
                $current_connection = $connections_set[$current_connection]['from'];
                continue;
            }
            else {
                $break_from_loop = false;
                foreach($connections_set as $id => $connection) {
                    // Move to connection with no result
                    if($connection['to_type'] != null && $connection_node_types[$connection['to_type']]['name'] == "Conenction"
                        && $connection['to'] == $current_connection
                        && !isset($connection_results[$id])) {
                        $current_connection = $id;
                        $break_from_loop = true;
                        break;
                    }
                }
                if($break_from_loop) continue;
            }

            /**
             * This section is reached when all needed results are present for connection to run
             */

            // Loop through connections to get inputs
            $inputs = [];
            $input_names = []; // Names attached to inputs

            // Retrive results from 'from' and other connected
            // TODO: Fill in missing cases
            switch($from_type) {
                case "Process":
                    // TODO: retrieve and compile selected process
                    // NOTICE: Making a process call the caller process will create a recursion effect
                    // that might create a memory corruption - be aware!
                    break;
                case "Task":
                    // TODO: retrieve and compile selected task
                    // NOTICE: Making a task call the caller process will create a recursion effect
                    // that might create a memory corruption - be aware!
                    break;
                case "Connection":
                    $inputs[] = $connection_results[$connections_set[$current_connection]['from']];
                    $input_names[] = $connections_set[$connections_set[$current_connection]['from']]['name'];
                    break;
                case "DataEntry":
                    $entry_data = DataManager::getEntryData($connections_set[$current_connection]['from']);
                    $inputs[] = DataManager::getEntryValue($connections_set[$current_connection]['from']);
                    $input_names[] = $entry_data['type']['name'] == 'constant' ? $connections_set[$current_connection]['name'] : $entry_data['name'];
                case "DataSource":
                    break;
            }
            
            // Retrive results from connections  who point to this
            foreach($connections_set as $id => $connection) {
                if($connection['to_type'] != null && $connection_node_types[$connection['to_type']]['name'] == "Connection"
                    && $connection['to'] == $current_connection) {
                        $inputs[] = $connection_results[$id];
                        $input_names[] = $connection['name'];
                }
            }

            // Compute results of current connection
            // TODO: Fill in missing cases
            switch($type) {
                case 'Flow': // Connection of data flow reacts only by 'to_type'
                    switch($to_type) {
                        case 'DataEntry':
                            $kv_inputs = self::processValues($inputs, $input_names);
                            if(count($kv_inputs) == 1) {
                                $kv_inputs[$connections_set[$current_connection]['name']] = $kv_inputs[array_keys($kv_inputs)[0]];
                                unset($kv_inputs[array_keys($kv_inputs)[0]]);
                            }
                            $entry_data = DataManager::getEntryData($connections_set[$current_connection]['to']);
                            $entry = DataManager::getEntryValue($connections_set[$current_connection]['to'], $kv_inputs);

                            if($entry_data['type']['name'] != 'table_cell') {
                                if(count($kv_inputs) == 1) $kv_inputs = $kv_inputs[array_keys($kv_inputs)[0]];
                                DataManager::setEntryValue($connections_set[$current_connection]['to'], $kv_inputs);
                            } else {
                                $connection_results[$current_connection] = $entry[0][$entry_data['name']];
                            }
                            break;
                        case 'DataSource':
                            break;
                        default:
                            // If destination is not an entry nor a source then set the inputs as the result
                            $kv_inputs = self::processValues($inputs, $input_names);
                            if(count($kv_inputs) == 1) $kv_inputs = $kv_inputs[array_keys($kv_inputs)[0]];
                            $connection_results[$current_connection] = $kv_inputs;
                            break;
                    }
                    break;
                case 'Function':
                    $connection_results[$current_connection] = call_user_func_array($connections_set[$current_connection]['name'], $inputs);
                    break;
                case 'Task':
                    break;
                case 'Process':
                    break;
                case 'Rule':
                    // Get the variable to measure with from 'to'
                    $to_measure = "";
                    if($to_type == 'DataEntry') $to_measure = DataManager::getEntryValue($connections_set[$current_connection]['to']);
                    // Handle comparison rules
                    switch($connections_set[$current_connection]['name']) {
                        case '==': $connection_results[$current_connection] = ($inputs[0] == $to_measure); break;
                        case '<': $connection_results[$current_connection] = ($inputs[0] < $to_measure); break;
                        case '>': $connection_results[$current_connection] = ($inputs[0] > $to_measure); break;
                        case '<=': $connection_results[$current_connection] = ($inputs[0] <= $to_measure); break;
                        case '>=': $connection_results[$current_connection] = ($inputs[0] >= $to_measure); break;
                    }
                    break;
            }

            // If result is not set - then make as true
            if(!isset($connection_results[$current_connection])) $connection_results[$current_connection] = true;

            // If result is computed then break
            // Or in case all connection got concluded and no result found
            if(self::isResult($connections_set[$current_connection]) || count($connection_results) == count($connections_set))
            {
                $result = $connection_results[$current_connection];
                break;
            }
            // Load next connection if is of type connection and no result is present
            if($to_type == "Connection" && !isset($connection_results[$connections_set[$current_connection]['to']])) {
                $current_connection = $connections_set[$current_connection]['to'];
            }
            // Load next connection who has 'from' as this
            else {
                $break_from_loop = false;
                foreach($connections_set as $id => $connection) {
                    if($connection['from_type'] != null && $connection_node_types[$connection['from_type']]['name'] == "Connection"
                        && $connection['from'] == $current_connection) {
                        $current_connection = $id;
                        $break_from_loop = true;
                        break;
                    }
                }
                // If no next found then notify user about error
                if(!$break_from_loop) Status::message(Status::ERROR, "Process compilation error, couldn't find next connection");
            }
        }

        return $result;
    }

    /**
     * Checks if a given conenction is a result
     * 
     * @param array &$connection Reference to the connection to check
     * @return boolean If connection is indeed a result
     */
    private static function isResult(&$connection) {
        return $connection['name'] == 'result'
            && $connection['from'] == null
            && $connection['to'] == null;
    }

    /** 
     * Store inputs and their names as an array or a single value
     * 
     * @param array &$inputs Input collection to assign
     * @param array &$input_names Names of the inputs by index
     * @return array The result of the processed inputs
     */
    private static function processValues(&$inputs, &$input_names) {
        $result = [];
        foreach($inputs as $index => $value) $result[$input_names[$index]] = $value;
        return $result;
    }

    /**
     * Check if a connection already exists
     */
    public static function connectionExists($name, $type, $from, $from_type, $to, $to_type) {
        $connections = CacheManager::get('connections');
        foreach($connections as $id => $connection) {
            if($connection['name'] == $name
                && $connection['type'] == $type
                && $connection['from'] == $from
                && $connection['from_type'] == $from_type
                && $connection['to'] == $to
                && $connection['to_type'] == $to_type) return $id;
        }
        return false;
    }

    /**
     * Creates a new connection and returns it's ID, if a connection exists it returns the ID.
     */
    public static function createConnection($name, $type, $from, $from_type, $to, $to_type) {
        // Check if a connection already exists
        $to_ret = self::connectionExists($name, $type, $from, $from_type, $to, $to_type);
        if($to_ret) return $to_ret;

        // Create a new connection
        $result = DatabaseManager::query("main", 
            "INSERT INTO connections (`name`, `type`, `from`, `from_type`, `to`, `to_type`)
            VALUES
            (:name, :type, :from, :from_type, :to, :to_type)", [
                "name" => $name,
                "type" => $type,
                "from" => $from,
                "from_type" => $from_type,
                "to" => $to,
                "to_type" => $to_type
            ]);
        if(!$result) Status::message(Status::ERROR, "Couldn't create a connection if the DB");

        CacheManager::getTable('connections', true); // Refresh cache
        // Return connection ID
        return self::connectionExists($name, $type, $from, $from_type, $to, $to_type);
    }
}

?>