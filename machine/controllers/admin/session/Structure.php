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

namespace APIShift\Controllers\Admin\Session;

use APIShift\Core\CacheManager;
use APIShift\Core\Status;

/**
 * This controller provides an interface to manipulate each session state structure
 */
class Structure {
    public static function getSessionStructure() {
        if(!isset($_POST['id'])) Status::message(Status::ERROR, "No state ID specified");
        if($_POST['id'] != 0 && !isset(CacheManager::get('session_states')[$_POST['id']])) Status::message(Status::ERROR, "Invalid state ID");

        $structures = CacheManager::get('session_state_structures');
        if(!isset($structures[$_POST['id']])) Status::message(Status::SUCCESS, []); // Return empty set of no structure found
        $structures = $structures[$_POST['id']]; // Get specific state only

        // Attach inputs and tasks to structure
        $inputs = CacheManager::get('inputs');
        $tasks = CacheManager::get('tasks');
        foreach($structures as &$keys) {
            $keys['task_name'] = $tasks[$keys['task']]['name'];
            $keys['input_name'] = $inputs[$keys['input']]['name'];
            unset($keys['task']); unset($keys['input']);
        }

        Status::message(Status::SUCCESS, $structures); // Return the result
    }

    public static function addSessionStructure() {}

    public static function removeSessionStructure() {}
}