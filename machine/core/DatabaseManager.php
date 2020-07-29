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
use \PDO;
use \PDOException;

/**
 * Interface which holds and manages PDO instances
 */
class DatabaseManager {
    /**
     * Array of all connections created by the API
     */
    private static $connections = array();

    /**
     * Start the connection and add ID name
     * 
     * @param string $connectionName Key of the connection object
     * @param string $db_host Hostname of DB
     * @param string $db_user Username of DB
     * @param string $db_pass Password of DB
     * @param int $db_port Port of DB
     * @param string $db_name DB name in server
     * @param bool $exit_on_error Set to false to not exit
     * 
     * @return void
     */
    public static function addConnection($connectionName, $db_host = null, $db_user = null, $db_pass = null, $db_port = null, $db_name = null, $exit_on_error = true) {
        // Check if connection already exists is queue
        if(isset(self::$connections[$connectionName]) && (self::$connections[$connectionName] instanceof PDO)) return;
        try {
            // Null values are updated using the configurations class
            if($db_host === null) $db_host = Configurations::DB_HOST;
            if($db_user === null) $db_user = Configurations::DB_USER;
            if($db_pass === null) $db_pass = Configurations::DB_PASS;
            if($db_port === null) $db_port = Configurations::DB_PORT;
            if($db_name === null && Configurations::INSTALLED) $db_name = Configurations::DB_NAME;

            // Connect to specific DB if specified
            if($db_name != null) self::$connections[$connectionName] = new PDO("mysql:host={$db_host};dbname={$db_name};port={$db_port}", $db_user, $db_pass);
            else self::$connections[$connectionName] = new PDO("mysql:host={$db_host};port={$db_port}", $db_user, $db_pass);

            // Avoid possible SQL injections
            self::$connections[$connectionName]->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException $e) {
            self::closeConnection($connectionName);
            Status::message(
                Status::DB_CONNECTION_FAILED,
                "Couldn't create `" . $connectionName . "` db connection ",
                $exit_on_error
            );
        }
    }

    /**
     * Return PDO instance of connection
     * 
     * @param $connectionName Name of the connection
     * 
     * @return PDO Instance of connection
     */
    public static function getInstance(string $connectionName)
    {
        if(!isset(self::$connections[$connectionName])) return null;
        return self::$connections[$connectionName];
    }

    /**
     * Close a connection
     * @param $connectionName Name of the connection
     * 
     * @return void
     */
    public static function closeConnection(string $connectionName)
    {
        if(!isset(self::$connections[$connectionName])) return;
        unset(self::$connections[$connectionName]);
    }

    /**
     * Fetch data into said variable
     * Order keys by desired column
     * 
     * @param string $connectionName name of the connection
     * @param array& $collector query to run
     * @param string $query query to run
     * @param array $data Parameters to pass when parsing query
     * @param string|null $column Comlumn to order as key
     * @param bool $single_row Store as a single row or as a collection of rows
     * 
     * @return void|false Returns false on failure
     */
    public static function fetchInto($connectionName, &$collector, $query, $data = array(), $column = null, $single_row = true) {
        try {
            // Run and validate query
            $result = self::$connections[$connectionName]->prepare($query);
            if(!$result) return false;
            $exec_check = $result->execute($data);
            if(!$exec_check) return false;
            // Store query in collector (will override existing keys)
            $result = $result->fetchAll(PDO::FETCH_ASSOC);
            if($column == null) $collector = $result;
            else
            {
                $collector = [];
                // Load all elements
                foreach($result as $row) {
                    // Single rows
                    if($single_row) {
                        $collector[$row[$column]] = $row;
                        unset($collector[$row[$column]][$column]);
                    }
                    // Multiple rows
                    else {
                        if(!isset($collector[$row[$column]])) $collector[$row[$column]] = [];
                        $collector[$row[$column]][] = $row;
                        unset($collector[$row[$column]][count($collector[$row[$column]]) - 1][$column]);
                    }
                }
            }
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Run a query
     * 
     * @param string $connectionName Name of the connection
     * @param string $query Query to run
     * @param array $data Parameters to pass when parsing query
     * 
     * @return bool|PDOStatement Returns FALSE on failure and PDOStatement on success
     */
    public static function query($connectionName, $query, $data = array()) {
        try {
            $result = self::$connections[$connectionName]->prepare($query);
            if(!$result) return false;
            $exec_check = $result->execute($data);
            if(!$exec_check) return false;
            return $result;
        } catch (PDOException $e) {
            return false;
        }
    }
}

?>