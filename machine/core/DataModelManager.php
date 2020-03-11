<?php
/**
 * APIShift Engine v1.0.0
 * (c) 2020-present Sapir Shemer, DevShift (devshift.biz)
 * Released under the MIT License with the additions present in the LICENSE.md
 * file in the root folder of the APIShift Engine original release source-code
 * @author Sapir Shemer
 */

namespace APIShift\Core;

use DatabaseManager;

class DataModelManager {
    public static function createItem(string $item_name, array $keys) {
        // Check if table exists
        $check = DatabaseManager::getInstance("main")->query("SELECT id FROM " . $item_name);
        if(gettype($check) == 'array') {
            // TODO: update existing structure
            return;
        }
        
        // TODO: create new table
        // Create query string containing the key pairs and their data
        $data = "";
        foreach($keys as $key_name => $value) $data .= $key_name . " " . $value['type'] . ",";
        $result = DatabaseManager::getInstance("main")->query("CREATE TABLE " . $item_name  .'{' . "id primary INT, " . $data . '}', $keys);
    }
}
?>