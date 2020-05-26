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
 * Provides an interface to manage data entries and sources while hiding implementation details & automatic caching
 */
class DataManager {
    /**
     * Stores data entries which are used at runtime - thus saving speed when accessing data when working with entries on the same request
     * which most likely will happen, and this trick keeps things fast throughout requests
     */
    private static $runtime_entries = [];

    /**
     * Upload entry data to/from cache and current runtime
     */
    private static function uploadEntryToCacheAndRuntime($id) {
        if(!isset(self::$runtime_entries[$id])) {
            self::$runtime_entries[$id] = CacheManager::getFromTable('data_entries', $id);
            self::$runtime_entries[$id]['source'] = CacheManager::getFromTable("data_sources", self::$runtime_entries[$id]['source']);
            self::$runtime_entries[$id]['type'] = CacheManager::get('data_entry_types')[self::$runtime_entries[$id]['type']];
        }
    }

    public static function getEntryData($id) {
        self::uploadEntryToCacheAndRuntime($id);
        return self::$runtime_entries[$id];
    }

    public static function setEntryValue($id, $value) {
        self::uploadEntryToCacheAndRuntime($id);
        switch(self::$runtime_entries[$id]['type']['name']) {
            case 'array_key':
                if(isset($GLOBALS[self::$runtime_entries[$id]['source']['name']]))
                    $GLOBALS[self::$runtime_entries[$id]['source']['name']][self::$runtime_entries[$id]['name']] = $value;
                else if(!isset(${self::$runtime_entries[$id]['source']['name']}))
                    Status::message(Status::ERROR, "Couldn't find array " . self::$runtime_entries[$id]['source']['name']);
                else ${self::$runtime_entries[$id]['source']['name']}[self::$runtime_entries[$id]['name']] = $value;
                break;
            case 'variable':
                if(isset($GLOBALS[self::$runtime_entries[$id]['name']])) $GLOBALS[self::$runtime_entries[$id]['name']] = $value;
                else if(!isset(${self::$runtime_entries[$id]['name']}))
                    Status::message(Status::ERROR, "Couldn't find variable " . self::$runtime_entries[$id]['name']);
                else ${self::$runtime_entries[$id]['name']} = $value;
                break;
            case 'constant':
                self::$runtime_entries[$id]['name'] = $value;
                break;
            case 'table_cell':
                // TODO: retrieve data about cell from table
                break;
        }
    }

    public static function getEntryValue($id, $query_where_inputs = []) {
        self::uploadEntryToCacheAndRuntime($id);
        switch(self::$runtime_entries[$id]['type']['name']) {
            case 'array_key':
                if(isset($GLOBALS[self::$runtime_entries[$id]['source']['name']]))
                    return $GLOBALS[self::$runtime_entries[$id]['source']['name']][self::$runtime_entries[$id]['name']];
                else if(!isset(${self::$runtime_entries[$id]['source']['name']}))
                    Status::message(Status::ERROR, "Couldn't find array " . self::$runtime_entries[$id]['source']['name']);
                else return ${self::$runtime_entries[$id]['source']['name']}[self::$runtime_entries[$id]['name']];
                break;
            case 'variable':
                if(isset($GLOBALS[self::$runtime_entries[$id]['name']])) return $GLOBALS[self::$runtime_entries[$id]['name']];
                else if(!isset(${self::$runtime_entries[$id]['name']}))
                    Status::message(Status::ERROR, "Couldn't find variable " . self::$runtime_entries[$id]['name']);
                else return ${self::$runtime_entries[$id]['name']};
                break;
            case 'constant':
                return self::$runtime_entries[$id]['name'];
                break;
            case 'table_cell':
                // TODO: retrieve data about cell from table
                // Get table name
                $table_name = self::$runtime_entries[$id]['source']['name'];
                // Initial query parameters
                $query_params = [];

                // TODO: determine query by inputs (SELECT by default)
                $query = "SELECT " . self::$runtime_entries[$id]['name'] . " FROM " . $table_name;

                // Add inputs to where clause
                if (!empty($query_where_inputs)) {
                    $query .= " WHERE ";
                    foreach ($query_where_inputs as $name => $value) {
                        if (gettype($value) != 'string') continue;
                        $query .= $name . " = :" . $name . " ";
                    }
                }

                // Run query to retrive values
                $query_result = [];
                DatabaseManager::fetchInto('main', $query_result, $query, $query_where_inputs);
                return $query_result;
                break;
        }
    }
}