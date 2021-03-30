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

namespace APIShift\Controllers\Admin;

use APIShift\Core\CacheManager;
use APIShift\Core\DatabaseManager;
use APIShift\Core\Status;
use Exception;

/**
 * A class that helps configure the system and set pre-defined values that guide the core components of the system.
 */
class Settings {
    /**
     * Refreshes the values APIShift uses in the cache
     */
    public static function refreshCache() {
        CacheManager::loadDefaults(true);
        Status::message(Status::SUCCESS, "Cache refreshed successfuly");
    }


    /**
     * Imports from inital.sql or from user defined sql code
     */
    public static function importDatabase () {
        if(isset($_POST['load_initial']) && $_POST['load_initial']) {
            // Load from initial SQL file
            $data_to_import = file_get_contents("data/initial.sql");
            if(!$data_to_import) {
                Status::message(
                    Status::ERROR,
                    "Couldn't open initial data SQL file, please check permissions, re-download the system or add missing files"
                );
            }
            DatabaseManager::getInstance("main")->exec($data_to_import);
            Status::message(Status::SUCCESS, "Loaded initial database successfully!");
        }

        if(!isset($_POST['sql_code'])) Status::message(Status::ERROR, "Didn't set code to import");
        if(!isset($_POST['database_name'])) $_POST['database_name'] = "main";

        DatabaseManager::getInstance($_POST['database_name'])->exec($_POST['sql_code']);
        Status::message(Status::SUCCESS, "Loaded into database successfully!");
    }

    /**
     * Export current database to client as an SQL file string
     */
    public static function exportDatabase () {
        if(!isset($_POST['database_name'])) $_POST['database_name'] = "main";
        DatabaseManager::startConnection($_POST['database_name']);

        $res = "";
        
        // Fetch tables array from DB
        $show_tables = [];
        if(DatabaseManager::fetchInto($_POST['database_name'], $show_tables, "SHOW TABLES") === false)
            Status::message(Status::ERROR, "Couldn't retrieve tables");
        
        // Extract table names 
        $tables = [];
        foreach($show_tables as $table) {
            $tables[] = array_values($table)[0];
        }

        // Build SQL file string
        foreach($tables as $table_name) {
            $table_name = "`".$table_name."`";

            // Generate table creation metadata
            $table_metadata = [];
            if (DatabaseManager::fetchInto($_POST['database_name'], $table_metadata, "SHOW CREATE TABLE " . $table_name) === false) 
                Status::message(Status::ERROR, "Couldn't retrieve table data");
            $res .= "DROP TABLE IF EXISTS " . $table_name . ";\n";
            $res .= $table_metadata[0]["Create Table"] . ";" .  "\n";

            // Populate table with data
            $res .= 'LOCK TABLES ' . $table_name . " WRITE;\n";            
            $rows = [];
            if (DatabaseManager::fetchInto($_POST['database_name'], $rows, "SELECT * FROM " . $table_name) === false) 
                Status::message(Status::ERROR, "Couldn't retrieve data from table");

            // Generate insert query
            if (!empty($rows)) {
                $insert_query = 'INSERT INTO ' . $table_name . ' VALUES ';

                foreach ($rows as $row) {
                    $row_data = array_values($row);
    
                    $insert_query .= '(';
                    foreach($row_data as $cell) {
                        if($cell === null) $insert_query .= 'NULL, ';
                        else if(gettype($cell) != 'string') $insert_query .= '' . $cell . ', ';
                        else $insert_query .= "'" . $cell . "', ";
                    }
                    $insert_query = substr($insert_query, 0, strlen($insert_query) - 2);
                    $insert_query .= "),";
                }
                $insert_query = substr($insert_query, 0, strlen($insert_query) - 1) . ";";
                
                // Concatinate insert query 
                $res .= $insert_query;
            }

            $res .= "\n" . "UNLOCK TABLES;\n";

        }
        
        Status::message(Status::SUCCESS, $res);
    }

    /**
     * Export initial database to client
     */
    public static function exportAsInitialDatabase () {
        $res = "";
        
        // Fetch tables array from DB
        $show_tables = [];
        if(DatabaseManager::fetchInto("main", $show_tables, "SHOW TABLES") === false)
            Status::message(Status::ERROR, "Couldn't retrieve tables");
        
        // Extract table names 
        $tables = [];
        foreach($show_tables as $table) {
            $tables[] = array_values($table)[0];
        }

        // Build SQL file string
        foreach($tables as $table_name) {
            $table_name = "`".$table_name."`";

            // Generate table creation metadata
            $table_metadata = [];
            if (DatabaseManager::fetchInto("main", $table_metadata, "SHOW CREATE TABLE " . $table_name) === false) 
                Status::message(Status::ERROR, "Couldn't retrieve table data");
            $res .= "DROP TABLE IF EXISTS " . $table_name . ";\n";
            $res .= $table_metadata[0]["Create Table"] . ";" .  "\n";

            // Populate table with data
            $res .= 'LOCK TABLES ' . $table_name . " WRITE;\n";            
            $rows = [];
            if (DatabaseManager::fetchInto("main", $rows, "SELECT * FROM " . $table_name) === false) 
                Status::message(Status::ERROR, "Couldn't retrieve data from table");

            // Generate insert query
            if (!empty($rows)) {
                $insert_query = 'INSERT INTO ' . $table_name . ' VALUES ';

                foreach ($rows as $row) {
                    $row_data = array_values($row);
    
                    $insert_query .= '(';
                    foreach($row_data as $cell) {
                        if($cell === null) $insert_query .= 'NULL, ';
                        else if(gettype($cell) != 'string') $insert_query .= '' . $cell . ', ';
                        else $insert_query .= "'" . $cell . "', ";
                    }
                    $insert_query = substr($insert_query, 0, strlen($insert_query) - 2);
                    $insert_query .= "),";
                }
                $insert_query = substr($insert_query, 0, strlen($insert_query) - 1) . ";";
                
                // Concatinate insert query 
                $res .= $insert_query;
            }
            $res .= "\n" . "UNLOCK TABLES;\n";
        }

        // Write to initial.sql and handle error cases
        if(!is_writable('data/initial.sql'))
            Status::message(Status::ERROR, "Couldnt write to initial.sql");
        
        file_put_contents("data/initial.sql", $res);

        // Finish excecution
        Status::message(Status::SUCCESS, "Exported succesfully");
    }
}