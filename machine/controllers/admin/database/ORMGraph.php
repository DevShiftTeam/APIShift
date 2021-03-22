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
 * @author Sagi Weizmann
 */

namespace APIShift\Controllers\Admin\Database;

use APIShift\Controllers\Admin\Access\Database;
use APIShift\Core\CacheManager;
use APIShift\Core\DataManager;
use APIShift\Core\DatabaseManager;
use APIShift\Core\Process;
use APIShift\Core\Status;
use APIShift\Core\Task;

/**
 * Interface containing the available request to manipulate access rules to controllers
 */
class ORMGraph
{
    public static function get() {
        // Validate request
        if(!isset($_POST['graph_id'])) Status::message(Status::ERROR, "No graph was specified");

        $orm_graphs = [];
        DatabaseManager::fetchInto("main", $orm_graphs, "SELECT * FROM `orm_graphs`", [], 'id');

        if(!isset($orm_graphs[$_POST['graph_id']])) Status::message(Status::ERROR, "Graph not found, was it deleted?");

        // Constrcut elements object
        $elements = [];

        Status::message(Status::SUCCESS, $elements);
    }

    public static function set() {
        // Store elements in database
        
        // Modify table data to fit new model
    }
}
