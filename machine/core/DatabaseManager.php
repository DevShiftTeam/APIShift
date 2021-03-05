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
     * Metadata of every connection at run-time
     */
    public static $connections_metadata = [
        "main" => [
            "host" => Configurations::DB_HOST,
            "user" => Configurations::DB_USER,
            "pass" => Configurations::DB_PASS,
            "db" => Configurations::DB_NAME,
            "port" => Configurations::DB_PORT,
        ]
    ];

    /**
     * Load all main connection and all connections in cache (load them if not present)
     */
    public static function loadDefaults() {
        // Start main connection and retrieve all other databases data
        self::startConnection("main");
    }

    /**
     * Add the data of the connection to the metadatalist
     * 
     * @param string $connection_name Key of the connection object
     * @param string $db_host Hostname of DB
     * @param string $db_user Username of DB
     * @param string $db_pass Password of DB
     * @param int $db_port Port of DB
     * @param string $db_name DB name in server
     * @param bool $exit_on_error Set to false to not exit
     * 
     * @return void
     */
    public static function addConnection($connection_name, $db_host = null, $db_user = null, $db_pass = null, $db_port = null, $db_name = null) {
        $exists = isset(self::$connections_metadata[$connection_name]);
        self::$connections_metadata[$connection_name] = [
            "host" => $db_host == null && Configurations::INSTALLED ?
                ($exists && isset(self::$connections_metadata[$connection_name]['host']) ?
                    self::$connections_metadata[$connection_name]['host'] : Configurations::DB_HOST) : $db_host,
            "user" => $db_user == null && Configurations::INSTALLED ?
                ($exists && isset(self::$connections_metadata[$connection_name]['user']) ?
                    self::$connections_metadata[$connection_name]['user'] : Configurations::DB_USER) : $db_user,
            "pass" => $db_pass == null && Configurations::INSTALLED ?
               ($exists && isset(self::$connections_metadata[$connection_name]['pass']) ?
                    self::$connections_metadata[$connection_name]['pass'] : Configurations::DB_PASS) : $db_pass,
            "db" => $db_name == null && Configurations::INSTALLED ?
                ($exists && isset(self::$connections_metadata[$connection_name]['db']) ?
                    self::$connections_metadata[$connection_name]['db'] : Configurations::DB_NAME) : $db_name,
            "port" => $db_port == null && Configurations::INSTALLED ?
                ($exists && isset(self::$connections_metadata[$connection_name]['port']) ?
                    self::$connections_metadata[$connection_name]['port'] : Configurations::DB_PORT) : $db_port
        ];
    }

    /**
     * Start the connection and add ID name
     * 
     * @param string $connection_name Key of the connection object
     * @param string $db_host Hostname of DB
     * @param string $db_user Username of DB
     * @param string $db_pass Password of DB
     * @param int $db_port Port of DB
     * @param string $db_name DB name in server
     * @param bool $exit_on_error Set to false to not exit
     * 
     * @return void
     */
    public static function startConnection($connection_name, $db_host = null, $db_user = null, $db_pass = null, $db_port = null,
                                            $db_name = null, $exit_on_error = true) {
        // Check if connection already exists is queue
        if(isset(self::$connections[$connection_name]) && (self::$connections[$connection_name] instanceof PDO)) return;
        try {
            // Null values are updated using the configurations class or by the present metadata if exists
            self::addConnection($connection_name, $db_host, $db_user, $db_pass, $db_port, $db_name);
            if($db_host === null) $db_host = self::$connections_metadata[$connection_name]['host'];
            if($db_user === null) $db_user = self::$connections_metadata[$connection_name]['user'];
            if($db_pass === null) $db_pass = self::$connections_metadata[$connection_name]['pass'];
            if($db_name === null) $db_name = self::$connections_metadata[$connection_name]['db'];
            if($db_port === null) $db_port = self::$connections_metadata[$connection_name]['port'];

            // Connect to specific DB if specified
            if($db_name != null)
                self::$connections[$connection_name] = 
                    new PDO("mysql:host={$db_host};dbname={$db_name};port={$db_port}", $db_user, $db_pass);
            else
                self::$connections[$connection_name] = 
                    new PDO("mysql:host={$db_host};port={$db_port}", $db_user, $db_pass);

            // Avoid possible SQL injections
            self::$connections[$connection_name]->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException $e) {
            self::closeConnection($connection_name);
            Status::message(
                Status::DB_CONNECTION_FAILED,
                "Couldn't create `" . $connection_name . "` db connection",
                $exit_on_error
            );
        }
    }

    /**
     * Return PDO instance of connection
     * 
     * @param string $connectionName Name of the connection
     * 
     * @return \PDO Instance of connection
     */
    public static function getInstance(string $connectionName)
    {
        if(!isset(self::$connections[$connectionName])) return null;
        return self::$connections[$connectionName];
    }

    /**
     * Close a connection
     * 
     * @param string $connectionName Name of the connection
     * 
     * @return void
     */
    public static function closeConnection(string $connectionName)
    {
        if(!isset(self::$connections[$connectionName])) return;
        unset(self::$connections[$connectionName]);
    }

    /**
     * Closes a connection and removes its metadata
     * @param string $connection_name Name of the connection
     * 
     * @return void
     */
    public static function deleteConnection(string $connection_name) {
        self::closeConnection($connection_name); // Close connection first
        if(!isset(self::$connections_metadata[$connection_name])) return;
        unset(self::$connections_metadata[$connection_name]);
    }

    /**
     * Fetch data into said variable
     * Order keys by desired column
     * 
     * @param string $connectionName name of the connection
     * @param array &$collector query to run
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
     * @return bool|\PDOStatement Returns FALSE on failure and PDOStatement on success
     */
    public static function query($connectionName, $query, $data = array()) {

        if(self::$connections[$connectionName] == null) {
            self::startConnection($connectionName); // Try to establish a connection
            if(self::$connections[$connectionName] == null) return false;
        }
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