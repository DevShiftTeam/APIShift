<?php
/**
 * APIShift Engine v1.0.0
 * (c) 2020-present Sapir Shemer, DevShift (devshift.biz)
 * Released under the MIT License with the additions present in the LICENSE.md
 * file in the root folder of the APIShift Engine original release source-code
 * @author Sapir Shemer
 */

namespace APIShift\Core;

class DataModelManager {
    /**
     * Creates an item/relation using query rules
     * Query structure:
     * [
     *      name => "<item/relation name>",
     *      keys => [
     *                  key_name_1: 'type',
     *                  .
     *                  .
     *                  .
     *              ],
     *      relation_type: type_id \\ In case of a relation
     *      from: <item_id/item_name/array>,
     *      to: <item_id/item_name/array>
     * ]
     */
    public static function create(string $query) {
        // Validate query
        if(!isset($query['name'])) Status::message(Status::ERROR, "No item/relation name specified");
        if(!isset($query['keys'])) Status::message(Status::ERROR, "Cannot create an empty item/relation structure");

        // Check if table exists
        $check = DatabaseManager::getInstance("main")->query("SELECT id FROM " . $query['name']);
        if(gettype($check) == 'array') Status::message(Status::ERROR, "Item/relation exist");
        
        // Create query string containing the key pairs and their data
        $query_str = "";
        foreach($query['keys'] as $key_name => $value) $query_str .= $key_name . " " . $value . ",\n";
        // Create the table
        $result = DatabaseManager::getInstance("main")->query("CREATE TABLE " . $query['name']  . '{' . "id primary INT, " . $query_str . '}');

        // TODO: Check if item is relation and create relation

        // Check if creation completed successfully
        if(gettype($result) == 'string') Status::message(Status::ERROR, $result);
    }
}
?>