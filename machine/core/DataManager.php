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
    public static function getEntryType($id) {
        $entry_data = CacheManager::setFromTable('data_entries', $id);
        return CacheManager::get('data_entry_types')[$entry_data['type']];
    }

    public static function getEntryValue($id) {
        $entry_data = CacheManager::setFromTable('data_entries', $id);
        $type = CacheManager::get('data_entry_types')[$entry_data['type']];
        $source = CacheManager::setFromTable("data_sources", $entry_data['source']);

        switch($type['name']) {
            case 'array_key':
                if(isset($GLOBALS[$source['name']])) return $GLOBALS[$source['name']][$entry_data['name']];
                else if(!isset(${$source['name']})) Status::message(Status::ERROR, "Couldn't find array " . $source['name']);
                else return ${$source['name']}[$entry_data['name']];
                break;
            case 'variable':
                if(isset($GLOBALS[$entry_data['name']])) return $GLOBALS[$entry_data['name']];
                else if(!isset(${$entry['name']})) Status::message(Status::ERROR, "Couldn't find variable " . $entry_data['name']);
                else return ${$entry['name']};
                break;
            case 'constant':
                return $entry_data['name'];
                break;
            case 'table_cell':
                // TODO: retrieve data about cell from table
                break;
        }
    }

    public static function getSource($id) {

    }
}