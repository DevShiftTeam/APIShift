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
    public static $runtime_entries = [];
    public static $runtime_sources = [];

    /**
     * Upload entry data to/from cache and current runtime
     */
    private static function uploadEntryToCacheAndRuntime($id) {
        if(!isset(self::$runtime_entries[$id])) {
            self::$runtime_entries[$id] = CacheManager::getFromTable('data_entries', $id);

            // Add source if there is one and not present in run-time
            if(self::$runtime_entries[$id]['source'] != 0 && !isset(self::$runtime_sources[self::$runtime_entries[$id]['source']])) {
                self::$runtime_sources[self::$runtime_entries[$id]['source']]
                    = CacheManager::getFromTable('data_sources', self::$runtime_entries[$id]['source']);
            }
        }
    }

    public static function getEntryData($id) {
        self::uploadEntryToCacheAndRuntime($id);
        $to_ret = self::$runtime_entries[$id];
        $to_ret['source'] = self::$runtime_sources[$to_ret['source']];
        return $to_ret;
    }

    public static function setEntryValue($id, $value) {
        self::uploadEntryToCacheAndRuntime($id);

        // Keeping a reference for ease
        $entry = &self::$runtime_entries[$id];
        $source = &self::$runtime_sources[$entry['source']];

        // Handle base on type
        switch($entry['type']) {
            case 1: // Array key
                if(isset($GLOBALS[$source['name']]))
                    $GLOBALS[$source['name']][$source['name']] = $value;
                else if(!isset(${$source['name']}))
                    Status::message(Status::ERROR, "Couldn't find array " . $source['name']);
                else ${$source['name']}[$entry['name']] = $value;
                break;

            case 2: // Variable
                if(isset($GLOBALS[$entry['name']])) $GLOBALS[$entry['name']] = $value;
                else if(!isset(${$entry['name']}))
                    Status::message(Status::ERROR, "Couldn't find variable " . $entry['name']);
                else ${$entry['name']} = $value;
                break;

            case 3: // Constant
                $entry['name'] = $value;
                break;
            case 4: // Cell
                // TODO: retrieve data about cell from table
                break;
        }
    }

    public static function getEntryValue($id, $query_where_inputs = []) {
        self::uploadEntryToCacheAndRuntime($id);
        // Keeping a reference for ease
        $entry = &self::$runtime_entries[$id];
        $source = &self::$runtime_sources[$entry['source']];
        // Handle based on type
        switch($entry['type']) {
            case 1: // Array Key
                if(isset($GLOBALS[$source['name']]))
                    return $GLOBALS[$source['name']][$entry['name']];
                else if(!isset(${$source['name']}))
                    Status::message(Status::ERROR, "Couldn't find array " . $source['name']);
                else return ${$source['name']}[$entry['name']];
                break;
            case 2: // Variable
                if(isset($GLOBALS[$entry['name']])) return $GLOBALS[$entry['name']];
                else if(!isset(${$entry['name']}))
                    Status::message(Status::ERROR, "Couldn't find variable " . $entry['name']);
                else return ${$entry['name']};
                break;
            case 3: // Constant
                return $entry['name'];
                break;
            case 4: // Cell
                // TODO: retrieve data about cell from table
                // Get table name
                $table_name = $source['name'];

                // TODO: determine query by inputs (SELECT by default)
                $query = "SELECT " . $entry['name'] . " FROM " . $table_name;

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

    /**
     * Creates a new entry and returns its ID, or returns an ID of an existing entry
     */
    public static function createEntry($name, $type, $source) {
        // Check if entry exists
        $data_entries = CacheManager::get('data_entries');
        foreach($data_entries as $id => $entry)
            if($entry['name'] == $name && $entry['type'] == $type && $entry['source'] == $source)
                return $id; // Return ID if does exist
        
        // Check if source/type exist
        if($entry['source'] != 0 && !isset(CacheManager::get('data_sources')[$entry['source']]))
            Status::message(Status::ERROR, "Couldn't find source of the entry provided");
        if(!isset(CacheManager::get('data_entry_types')[$entry['type']]))
            Status::message(Status::ERROR, "Couldn't find type of the entry provided");

        // Create data entry in database
        $result = DatabaseManager::query("main", "INSERT INTO data_entries (name, `type`, source) VALUE (:name, :type, :source)", [
            "name" => $name,
            "type" => $type,
            "source" => $source
        ]);
        if(!$result) Status::message(Status::ERROR, "Couldn't create data entry in database");

        CacheManager::getTable('data_entries', true); // Refresh cache
        $data_entries = CacheManager::get('data_entries'); // Re-get data

        // Return new data entry ID
        foreach($data_entries as $id => $entry)
            if($entry['name'] == $name && $entry['type'] == $type && $entry['source'] == $source)
                return $id; // Return ID if does exist
    }
}