<?php
/**
 * APIShift Engine v1.0.0
 * (c) 2020-present Sapir Shemer, DevShift (devshift.biz)
 * Released under the MIT License with the additions present in the LICENSE.md
 * file in the root folder of the APIShift Engine original release source-code
 * @author Sapir Shemer
 */

namespace APIShift\Core;

/**
 * Interface that helps manage the structures and types of items and relations available in the system
 */
class DataModelManager {
    /**
     * Creates an item/relation using query rules
     * @param array $query Query data to create the Item/Relation
     * @param string $query['name'] Item/Relation name to create
     * @param array $query['keys'] Collection of key names to types to add
     * @param string|int $query['relation_type'] ID or name of the relation type to assign
     * @param string|int|array $query['from'] Relation comming from item ID/name/collection
     * @param string|int|array $query['to'] Relation comming from item ID/name/collection
     */
    public static function create(array $query) {
        // Validate query
        if(!isset($query['name'])) Status::message(Status::ERROR, "No item/relation name specified");
        if(!isset($query['keys'])) Status::message(Status::ERROR, "Cannot create an empty item/relation structure");

        // Check if table/item exists
        $check = DatabaseManager::getInstance("main")->query("SELECT id FROM " . $query['name']);
        if(gettype($check) == 'array') Status::message(Status::ERROR, "Item/relation exist");
        
        // Query construction string
        $query_str = "";
        // Create query string containing the key pairs and their types
        foreach($query['keys'] as $key_name => $value) $query_str .= $key_name . " " . $value . ",";

        // Check if item is relation and create relation
        if(!isset($query['relation_type'])) {
            // Create AI primary key based on system
            switch(Configurations::DB_TYPE) {
                case "MySQL":
                    $query_str .= "id int NOT NULL AUTO_INCREMENT, PRIMARY KEY (id)";
                    break;
                case "MSSQL":
                    $query_str .= "id int NOT NULL PRIMARY KEY IDENTITY(1,1)";
                    break;
            }
            // Create the table
            $result = DatabaseManager::query("main", "CREATE TABLE " . $query['name']  . " (" . $query_str . ");");
            // Check if creation completed successfully
            if($result == false) Status::message(Status::ERROR, "Couldn't create item in DB");
            // Insert item data
            $result = DatabaseManager::query("main", "INSERT INTO items (table_name) VALUES (:iname);", [ 'iname' => $query['name'] ]);
            // Check if insertion completed successfully
            if($result == false) {
                Status::message(Status::ERROR, "Couldn't insert item to items");
                // TOOD: Dispose of data if any was created
            }
            Status::message(Status::SUCCESS, "Created Item successfully");
        }
        
        switch($query['relation_type']) {
            case 0: // 1 to 1
            case 1: // 1 to n
                // Table name to modify
                $table_name = "";
                // Get `to` table id
                if(gettype($query['to']) != 'int') {
                    if($query['relation_type'] == 1) $table_name = $query['to'];
                    // Get 'to' table ID and store it in $query['to']
                    $result = DatabaseManager::query("main", "SELECT id FROM items WHERE name = :to_name", ['to_name' => $query['to']]);
                    if(gettype($result) != 'array' || count($result) == 0) Status::message(Status::ERROR, "Relation `to` item doesn't exist");
                    $query['to'] = $result[0]['id'];
                }
                else if($query['relation_type'] == 1){
                    // Get 'to' table name
                    $result = DatabaseManager::query("main", "SELECT name FROM items WHERE id = :to_id", ['to_id' => $query['to']]);
                    if(gettype($result) != 'array' || count($result) == 0) Status::message(Status::ERROR, "Relation `to` item doesn't exist");
                    $table_name = $result[0]['name'];
                }
                // Get `from` table id
                if(gettype($query['from']) != 'int') {
                    if($query['relation_type'] == 0) $table_name = $query['from'];
                    // Get 'from' table ID and store it in $query['from']
                    $result = DatabaseManager::query("main", "SELECT id FROM items WHERE name = :from_name", ['from_name' => $query['from']]);
                    if(gettype($result) != 'array' || count($result) == 0) Status::message(Status::ERROR, "Relation `from` item doesn't exist");
                    $query['from'] = $result[0]['id'];
                }
                else if($query['relation_type'] == 0){
                    // Get 'from' table name
                    $result = DatabaseManager::query("main", "SELECT name FROM items WHERE id = :from_id", ['from_id' => $query['from']]);
                    if(gettype($result) != 'array' || count($result) == 0) Status::message(Status::ERROR, "Relation `from` item doesn't exist");
                    $table_name = $result[0]['name'];
                }

                // Create column in the `from` table to serve as the relation
                switch(Configurations::DB_TYPE) {
                    case "MySQL":
                        $query_str .= $query['name'] . " int AUTO_INCREMENT";
                        break;
                    case "MSSQL":
                        $query_str .= $query['name'] . " int IDENTITY(1,1)";
                        break;
                }
                $result = DatabaseManager::query("main",
                    "ALTER TABLE " . $table_name . " ADD " . $query_str . ";" .     // Create relation column
                    "INSERT INTO items (name) VALUES (:iname);" .                   // Add item
                    // Create the relation
                    "INSERT INTO relations (parent, `from`, `to`, `type`) VALUES ((SELECT id FROM items ORDER BY id DESC LIMIT 1), :fitem, :titem, :rtype);",
                    [
                        'iname' => $query['name'],
                        'fitem' => $query['from'],
                        'titem' => $query['to'],
                        'rtype' => $query['relation_type'],
                    ]
                );
                if($result == false) {
                    Status::message(Status::ERROR, "Couldn't create Relation in DB");
                    // TOOD: Dispose of data if any was created
                }

                // Add Item & Relation
                break;
            case 3: // n to n
                // TODO: Create relation as a new table
            break;
            default: Status::message(Status::ERROR, "Unrecognized relation type");
        }
    }
}
?>