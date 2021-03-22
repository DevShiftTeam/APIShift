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
        $orm_graph_elements = [];
        DatabaseManager::fetchInto("main", $orm_graph_elements, 
            "SELECT `orm_graph_elements`.*, `orm_graph_element_types`.component_index as component_id FROM `orm_graph_elements` WHERE graph_id = :graph_id INNER JOIN `orm_graph_element_types` ON `orm_graph_element_types`.id = `orm_graph_elements`.type",
            [ 'graph_id' => $_POST['graph_id'] ]);
        
        // Structure the elements in a manner that is easier for the UI
        foreach($orm_graph_elements as $element) {
            $element['data'] = [];
            $element['data']['position'] = [
                'x' => $element['position_x'],
                'y' => $element['position_y']
            ];
            $element['data']['z_index'] = $element['z_index'];

            unset($element['position_x']);
            unset($element['position_y']);
            unset($element['z_index']);

            switch($element['type']) {
                case 2: // Relation
                    // Get connected items
                    $relations = CacheManager::get('relations');
                    if(isset($relations[$element['element_id']])) {
                        $element['data']['to'] = $relations[$element['element_id']]['to'];
                        $element['data']['from'] = $relations[$element['element_id']]['from'];
                    }
                    break;
                case 4: // Enum
                    // Get connected items & types
                    $element['data']['types'] = CacheManager::get('enum_types')[$element['element_id']];
                    $element['data']['connected'] = CacheManager::get('item_enums')[$element['element_id']];
                    break;
                case 5: // Group
                    // Get connected items
                    $items = CacheManager::get('items');
                    $element['data']['elements'] = [];

                    foreach(array_keys($items) as $item_id)
                        if($items[$item_id]['parent'] == $element['element_id']) 
                            $element['data']['elements'][] = $item_id;
                    break;
            }

            unset($element['element_id']);
        }

        Status::message(Status::SUCCESS, $orm_graph_elements);
    }

    public static function set() {
        // Store elements in database
        
        // Modify table data to fit new model
    }
}
