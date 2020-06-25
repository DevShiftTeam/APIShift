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

use OCI_Lob;

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

        // Get the first connection which is not a result from the connections
        $connection_ids = array_keys($connections_set);
        $current_connection = $connection_ids[0];
        if(self::isResult($connections_set[$current_connection])) $current_connection = $connection_ids[1];
        
        // Compile connections until reaching the result
        while(count($connection_results) != count($connections_set)) {
            // Simplify representation of connection types
            $from_type = $connections_set[$current_connection]['from_type'];
            $to_type = $connections_set[$current_connection]['to_type'];
            $type = $connections_set[$current_connection]['connection_type'];

            /**
             * This section handles moving in the process graph to retrieve all the necessary results
             * and get to the start of the graph
             */

            // Move to next connection if result exists
            if(isset($connection_results[$current_connection])) {
                // Load next connection if is of type connection and no result is present
                if($to_type == 3 && !isset($connection_results[$connections_set[$current_connection]['to']])) {
                    $current_connection = $connections_set[$current_connection]['to'];
                }
                // Load next connection who has 'from' as this
                else {
                    $break_from_loop = false;
                    foreach($connections_set as $id => $connection) {
                        if($connection['from_type'] == 3 && $connection['from'] == $current_connection) {
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
            if($from_type == 3 && !isset($connection_results[$connections_set[$current_connection]['from']])) {
                $current_connection = $connections_set[$current_connection]['from'];
                continue;
            }

            // Manuver in connectors who point to the current connection to get results
            if($from_type == 3 && !isset($connection_results[$connections_set[$current_connection]['from']])) {
                $current_connection = $connections_set[$current_connection]['from'];
                continue;
            }
            else {
                $break_from_loop = false;
                foreach($connections_set as $id => $connection) {
                    // Move to connection with no result
                    if($connection['to_type'] == 3 && $connection['to'] == $current_connection
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
                case 1: // Process
                    // TODO: retrieve and compile selected process
                    // NOTICE: Making a process call the caller process will create a recursion effect
                    // that might create a memory corruption - be aware!
                    break;
                case 2: // Task
                    // TODO: retrieve and compile selected task
                    // NOTICE: Making a task call the caller process will create a recursion effect
                    // that might create a memory corruption - be aware!
                    break;
                case 3: // Connection
                    $inputs[] = $connection_results[$connections_set[$current_connection]['from']];
                    $input_names[] = $connections_set[$connections_set[$current_connection]['from']]['name'];
                    break;
                case 4: // Data Source
                    break;
                case 5: // Data Entry
                    $entry_data = DataManager::getEntryData($connections_set[$current_connection]['from']);
                    $inputs[] = DataManager::getEntryValue($connections_set[$current_connection]['from']);
                    // If constant then get name
                    $input_names[] = $entry_data['type'] == 3 ? $connections_set[$current_connection]['name'] : $entry_data['name'];
            }
            
            // Retrive results from connections who point to this
            foreach($connections_set as $id => $connection) {
                if($connection['to_type'] == 3 && $connection['to'] == $current_connection) {
                        $inputs[] = $connection_results[$id];
                        $input_names[] = $connection['name'];
                }
            }

            // Compute results of current connection
            // TODO: Fill in missing cases
            switch($type) {
                case 0: // Flow
                    switch($to_type) {
                        case 4: // Data Source
                            break;
                        case 5: // Data Entry
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
                        default:
                            // If destination is not an entry nor a source then set the inputs as the result
                            $kv_inputs = self::processValues($inputs, $input_names);
                            if(count($kv_inputs) == 1) $kv_inputs = $kv_inputs[array_keys($kv_inputs)[0]];
                            $connection_results[$current_connection] = $kv_inputs;
                            break;
                    }
                    break;
                case 1: // Process
                    break;
                case 2: // Task
                    break;
                case 3: // Rule
                    // Get the variable to measure with from 'to'
                    $to_measure = "";
                    if($to_type == 5) $to_measure = DataManager::getEntryValue($connections_set[$current_connection]['to']);
                    // Handle comparison rules
                    switch($connections_set[$current_connection]['name']) {
                        case '==': $connection_results[$current_connection] = ($inputs[0] == $to_measure); break;
                        case '<': $connection_results[$current_connection] = ($inputs[0] < $to_measure); break;
                        case '>': $connection_results[$current_connection] = ($inputs[0] > $to_measure); break;
                        case '<=': $connection_results[$current_connection] = ($inputs[0] <= $to_measure); break;
                        case '>=': $connection_results[$current_connection] = ($inputs[0] >= $to_measure); break;
                    }
                    break;
                case 4: // Function
                    $connection_results[$current_connection] = call_user_func_array($connections_set[$current_connection]['name'], $inputs);
                    break;
            }

            // If result is not set - then make as true
            if(!isset($connection_results[$current_connection])) $connection_results[$current_connection] = true;

            // If result is computed then break
            // Or in case all connection got concluded and no result connection found
            if(self::isResult($connections_set[$current_connection]) || count($connection_results) == count($connections_set))
            {
                $result = $connection_results[$current_connection];
                break;
            }
            // Load next connection if is of type connection and no result is present
            if($to_type == 3 && !isset($connection_results[$connections_set[$current_connection]['to']])) {
                $current_connection = $connections_set[$current_connection]['to'];
            }
            // Load next connection who has 'from' as this
            else {
                $break_from_loop = false;
                foreach($connections_set as $id => $connection) {
                    if($connection['from_type'] == 3 && $connection['from'] == $current_connection) {
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
     * Checks if a process exists
     * 
     * @param string $name The name of the process
     * 
     * @return int|bool The ID of the process or FALSE if doesn't exist
     */
    public static function processExists($name) {
        $processes = CacheManager::get('processes');
        foreach($processes as $id => $process) if($process['name'] == $name) return $id;
        return false;
    }

    /**
     * Create a new process or return it's ID if exists
     * 
     * @param string $name The name of the process
     * @param array $connection_ids ID's of the connections to associate with the process
     * 
     * @return int The ID of the process
     */
    public static function createProcess($name, $connection_ids) {
        // Return process if exists
        $process_id = self::processExists($name);
        if($process_id) return $process_id;
        
        // Check if connections are valid
        $connections = CacheManager::get('connections');
        foreach($connection_ids as $id)
            if(!isset($connections[$id]))
                Status::message(Status::ERROR, "Connection ID's provided do not exist");

        // Add process
        $result = DatabaseManager::query("main", "INSERT INTO processes (name) VALUES (:name)", [ "name" => $name ]);
        if(!$result) Status::message(Status::ERROR, "Couldn't create process in database");
        CacheManager::getTable('processes', true); // Refresh cache
        $process_id = self::processExists($name);

        // Create connection<->process list
        $connection_process_list = [];
        $insert_values = []; // Used for insert query
        foreach($connection_ids as $id) {
            $insert_values[] = "(:process_" . $id . ", :connection_" . $id . ")";
            $connection_process_list['process_' . $id] = $process_id;
            $connection_process_list['connection_' . $id] = $id;
        }
        $query = "INSERT INTO process_connections (process, connection) VALUES " . implode(",", $insert_values);

        // Connect the connections
        $result = DatabaseManager::query("main", $query, $connection_process_list);
        if(!$result) { // In case of error
            // Remove process
            DatabaseManager::query("main", "DELETE FROM processes WHERE name = :name", [ "name" => $name ]);
            CacheManager::getTable('processes', true); // Refresh cache
            Status::message(Status::ERROR, "Couldn't link process with given connections in DB");
        }

        CacheManager::getTable('process_connections', true, 0, 'process', false); // Refresh cache
        return $process_id;
    }

    /**
     * Check if a connection already exists
     * 
     * @param string $name The name of the connection
     * @param string $type Type of the connection
     * @param string $from ID of the element the conenction is coming from
     * @param string $from_type The type of the from element
     * @param string $to ID of the element connected is going to
     * @param string $to_type The type of the to element
     * 
     * @return int|bool The ID of the connection or FALSE if doesn't exist
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
     * 
     * @param string $name The name of the connection
     * @param string $type Type of the connection
     * @param string $from ID of the element the conenction is coming from
     * @param string $from_type The type of the from element
     * @param string $to ID of the element connected is going to
     * @param string $to_type The type of the to element
     * 
     * @return int|bool The ID of the connection
     */
    public static function createConnection($name, $type, $from, $from_type, $to, $to_type) {
        // Check if a connection already exists
        $to_ret = self::connectionExists($name, $type, $from, $from_type, $to, $to_type);
        if($to_ret) return $to_ret;

        // Create a new connection
        $result = DatabaseManager::query("main", 
            "INSERT INTO connections (`name`, `connection_type`, `from`, `from_type`, `to`, `to_type`)
            VALUES
            (:name, :type, :from, :from_type, :to, :to_type)", [
                "name" => $name,
                "type" => $type,
                "from" => $from,
                "from_type" => $from_type,
                "to" => $to,
                "to_type" => $to_type
            ]);
        if(!$result) Status::message(Status::ERROR, "Couldn't create a connection in the DB");

        CacheManager::getTable('connections', true); // Refresh cache
        // Return connection ID
        return self::connectionExists($name, $type, $from, $from_type, $to, $to_type);
    }
}

?>