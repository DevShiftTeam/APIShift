<?php
/**
 * APIShift Engine v1.0.0
 * (c) 2020-present Sapir Shemer, DevShift (devshift.biz)
 * Released under the MIT License with the additions present in the LICENSE.md
 * file in the root folder of the APIShift Engine original release source-code
 * @author Sapir Shemer
 */

namespace APIShift\Core;

class Process {
    /**
     * Compiles a set of connections.
     * Set of connections need to be connected either from front or back, since
     * free nodes will not produce a correct compilation process
     * @param array $connections_set The set of connection to compile
     * @param array& $param Refernce to the params to pass to the compilation process
     * @return any The result of the compilation
     */
    public static function compileConnections(&$connections_set, &$params = []) {
        // Key-value store of results of each connection
        $connection_results = [];
        $result = null;

        // Load types from cache
        $connection_types = apcu_fetch("ConnectionTypes");
        $connection_node_types = apcu_fetch("ConnectionNodeTypes");

        // Get the first connection which is not a result from the connections
        $connection_ids = array_keys($connections_set);
        $current_connection = $connection_ids[0];
        if(self::isResult($connections_set[$current_connection])) $current_connection = $connection_ids[1];
        
        // Compile connections until reaching the result
        while(count($connection_results) != count($connections_set)) {
            // Get type names
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
                    // TODO: Add mechanism to avoid this type of memory corruption
                    break;
                case "Task":
                    // TODO: retrieve and compile selected task
                    // NOTICE: Making a task call the caller process will create a recursion effect
                    // that might create a memory corruption - be aware!
                    // TODO: Add mechanism to avoid this type of memory corruption
                    break;
                case "Connection":
                    $inputs[] = $connection_results[$connections_set[$current_connection]['from']];
                    $input_names[] = $connections_set[$connections_set[$current_connection]['from']]['name'];
                    break;
                case "DataEntry":
                    // Get data entry
                    $entry = apcu_fetch("DataEntries")[$connections_set[$current_connection]['from']];
                    $entry_type = apcu_fetch("DataEntryTypes")[$entry['type']];

                    // Handle entry by type
                    switch($entry_type['name']) {
                        case 'array_key':
                            // Get source
                            $source = apcu_fetch("DataSources")[$entry['source']]['name'];
                            if(isset($GLOBALS[$source])) $inputs[] = $GLOBALS[$source][$entry['name']];
                            else if(!isset(${$source})) Status::message(Status::ERROR, "Couldn't find array " . $source);
                            else $inputs[] = ${$source}[$entry['name']];
                            $input_names[] = $entry['name'];
                            break;
                        case 'variable':
                            if(isset($GLOBALS[$entry['name']])) $inputs[] = $GLOBALS[$entry['name']];
                            else if(!isset(${$entry['name']})) Status::message(Status::ERROR, "Couldn't find array " . $source);
                            else $inputs[] = ${$entry['name']};
                            $input_names[] = $entry['name'];
                            break;
                        case 'constant':
                            $inputs[] = $entry['name'];
                            $input_names[] = $connections_set[$current_connection]['name'];
                            break;
                        case 'table_cell':
                            // TODO: retrieve data about cell from table
                            break;
                    }
                    break;
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
                            // Get data entry
                            $entry = apcu_fetch("DataEntries")[$connections_set[$current_connection]['to']];
                            $entry_type = apcu_fetch("DataEntryTypes")[$entry['type']];
                            
                            switch($entry_type['name']) {
                                case 'array_key':
                                    // Get source
                                    $source = apcu_fetch("DataSources")[$entry['source']]['name'];
                                    // Store inputs
                                    if(isset($GLOBALS[$source])) $GLOBALS[$source][$entry['name']] = self::processValues($inputs, $input_names);
                                    else if(!isset(${$source})) Status::message(Status::ERROR, "Couldn't find array " . $source);
                                    else ${$source}[$entry['name']] = self::processValues($inputs, $input_names);
                                    break;
                                case 'variable':
                                    if(strpos($entry['name'], "::") !== false) {
                                        $separated = explode("::", $entry['name']);
                                        $separated[0]::${$separated[1]} = self::processValues($inputs, $input_names);
                                    }
                                    else ${$entry['name']} = self::processValues($inputs, $input_names);
                                    break;
                                case 'constant':
                                    $to_measure = $entry['name'];
                                    break;
                                case 'table_cell':
                                    // Get table name
                                    $table_name = apcu_fetch("DataSources")[$entry['source']]['name'];
                                    // Initial query parameters
                                    $query_params = [ ];

                                    // TODO: determine query by inputs (SELECT by default)
                                    $query = "SELECT " . $entry['name'] . " FROM " . $table_name;

                                    // Add inputs to where clause
                                    if(!empty($inputs)) {
                                        $query .= " WHERE ";
                                        foreach($inputs as $index => $value) {
                                            if(gettype($value) != 'string') continue;
                                            $query .= ($index == 0 ? $connections_set[$current_connection]['name'] : $input_names[$index]) . " = :" . $input_names[$index] . " ";
                                            $query_params[$input_names[$index]] = $value;
                                        }
                                    }

                                    // Run query to retrive values
                                    $query_result = [];
                                    DatabaseManager::fetchInto('main', $query_result, $query, $query_params);
                                    if(!empty($query_result)) $connection_results[$current_connection] = $query_result[0][$entry['name']];
                                    else $connection_results[$current_connection] = 0;
                                    break;
                            }
                            break;
                        case 'DataSource':
                            break;
                        default:
                            // If destination is not an entry or a source then set the inputs as the result
                            $connection_results[$current_connection] = self::processValues($inputs, $input_names);
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
                    if($to_type == 'DataEntry') {
                        // Get data entry
                        $entry = apcu_fetch("DataEntries")[$connections_set[$current_connection]['to']];
                        $entry_type = apcu_fetch("DataEntryTypes")[$entry['type']];
                        
                        switch($entry_type['name']) {
                            case 'array_key':
                                // Get source
                                $source = apcu_fetch("DataSources")[$entry['source']]['name'];
                                // Store inputs
                                if(isset($GLOBALS[$source])) $to_measure = $GLOBALS[$source][$entry['name']];
                                else if(!isset(${$source})) Status::message(Status::ERROR, "Couldn't find array " . $source);
                                else $to_measure = ${$source}[$entry['name']];
                                break;
                            case 'variable':
                                if(strpos($entry['name'], "::") !== false) {
                                    $separated = explode("::", $entry['name']);
                                    $to_measure = $separated[0]::${$separated[1]};
                                }
                                else $to_measure = ${$entry['name']};
                                break;
                            case 'constant':
                                $to_measure = $entry['name'];
                                break;
                            default: break;
                        }
                    }
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
     * @param reference &$connection Reference to the connection to check
     * @return boolean If connection is indeed a result
     */
    private static function isResult(&$connection) {
        return $connection['name'] == 'result'
            && $connection['from'] == null
            && $connection['to'] == null;
    }

    /** 
     * Assigns inputs as value if only one or array if more than one
     * 
     * @param reference &$inputs Input collection to assign
     * @param reference &$input_names Names of the inputs by index
     * @return any|array The result of the processed inputs
     */
    private static function processValues(&$inputs, &$input_names) {
        $result = "";
        foreach($inputs as $index => $value) {
            // For one entry, store value only
            if($index == 0) $result = $value;
            // For multiple entries store as array
            else if($index == 1)
                $result = [
                    $input_names[0] => $result,
                    $input_names[1] => $value
                ];
            else {
                $result[$input_names[$index]] = $value;
            }
        }
        return $result;
    }
}

?>