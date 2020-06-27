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
use APIShift\Core\Status;

/**
 * This controller returns run-time data about the system
 */
class Data {
    public static function getEntriesMetadata() {
        Status::message(Status::SUCCESS, CacheManager::get('data_entries'));
    }
    
    public static function getSourcesMetadata() {
        Status::message(Status::SUCCESS, CacheManager::get('data_sources'));
    }

    public static function getMetadata() {
        // Get sources and entries separately
        $data_sources = CacheManager::get('data_sources');
        $data_sources[0] = [ 'name' => 'No Source' ]; // Entries with no source belong to this
        $data_entries = CacheManager::get('data_entries');

        // Add entries to each source as array ordered by ID's
        foreach($data_entries as $eid => &$entry) {
            if(!isset($data_sources[$entry['source']]['entries'])) $data_sources[$entry['source']]['entries'] = [];
            $data_sources[$entry['source']]['entries'][$eid] = &$entry;
            unset($entry['source']); // Source not needed since already nested
        }

        // TODO: sources should have parents and children - needs to be first implemented in the database

        Status::message(Status::SUCCESS, $data_sources);
    }
}