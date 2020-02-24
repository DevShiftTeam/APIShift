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
  * Manages cache data on your command, sir!
  */
 class CacheManager {
     /**
      * Load default cache data
      */
    public static function loadDefaults() {
        if(!extension_loaded("apcu")) Status::message(Status::ERROR, "Please install/enable APCu");
        // Get session states into cache if not cached
        if(!apcu_exists('StateCollection')) {
            $collection_to_load = [];
            if(DatabaseManager::fetchInto("main", $collection_to_load, "SELECT * FROM session_states", [], 'id') === false)
                Status::message(Status::ERROR, "Couldn't retrieve collection of States");
            // Load to cache
            apcu_store('StateCollection', $collection_to_load);
        }

        // Load available return statuses
        if(!apcu_exists('StatusCollection')) {
            $temp_statuses = [];
            if(DatabaseManager::fetchInto("main", $temp_statuses, "SELECT * FROM statuses", [], 'id') === false)
                Status::message(Status::ERROR, "Couldn't retrieve collection of Statuses");
            apcu_store('StatusCollection', $temp_statuses);
        }

        // Load Data sources to cache
        if(!apcu_exists('DataSources')) {
            $temp_sources = [];
            if(DatabaseManager::fetchInto("main", $temp_sources, "SELECT * FROM data_sources", [], 'id') === false)
                Status::message(Status::ERROR, "Couldn't retrieve collection of data sources");
            apcu_store('DataSources', $temp_sources);
        }

        // Load Data entries to cache
        if(!apcu_exists('DataEntries')) {
            $temp_entries = [];
            if(DatabaseManager::fetchInto("main", $temp_entries, "SELECT * FROM data_entries", [], 'id') === false)
                Status::message(Status::ERROR, "Couldn't retrieve collection of data entries");
            apcu_store('DataEntries', $temp_entries);
        }

        // Load Data source types to cache
        if(!apcu_exists('DataSourceTypes')) {
            $temp_source_types = [];
            if(DatabaseManager::fetchInto("main", $temp_source_types, "SELECT * FROM data_source_types", [], 'id') === false)
                Status::message(Status::ERROR, "Couldn't retrieve collection of data source types");
            apcu_store('DataSourceTypes', $temp_source_types);
        }

        // Load Data entry types to cache
        if(!apcu_exists('DataEntryTypes')) {
            $temp_entry_types = [];
            if(DatabaseManager::fetchInto("main", $temp_entry_types, "SELECT * FROM data_entry_types", [], 'id') === false)
                Status::message(Status::ERROR, "Couldn't retrieve collection of data entry types");
            apcu_store('DataEntryTypes', $temp_entry_types);
        }

        // Load Connection types to cache
        if(!apcu_exists('ConnectionTypes')) {
            $temp_connection_types = [];
            if(DatabaseManager::fetchInto("main", $temp_connection_types, "SELECT * FROM connection_types", [], 'id') === false)
                Status::message(Status::ERROR, "Couldn't retrieve collection of connection types");
            apcu_store('ConnectionTypes', $temp_connection_types);
        }

        // Load Connection node types to cache
        if(!apcu_exists('ConnectionNodeTypes')) {
            $temp_connection_node_types = [];
            if(DatabaseManager::fetchInto("main", $temp_connection_node_types, "SELECT * FROM connection_node_types", [], 'id') === false)
                Status::message(Status::ERROR, "Couldn't retrieve collection of connection node types");
            apcu_store('ConnectionNodeTypes', $temp_connection_node_types);
        }

        // Load Items to cache
        if(!apcu_exists('Items')) {
            $temp_items = [];
            if(DatabaseManager::fetchInto("main", $temp_items, "SELECT * FROM items", [], 'id') === false)
                Status::message(Status::ERROR, "Couldn't retrieve collection of Items");
            apcu_store('Items', $temp_items);
        }
        
        // Load Relations to cache
        if(!apcu_exists('Relations')) {
            $temp_relations = [];
            if(DatabaseManager::fetchInto("main", $temp_relations, "SELECT * FROM relations", [], 'id') === false)
                Status::message(Status::ERROR, "Couldn't retrieve collection of Relations");
            apcu_store('Relations', $temp_relations);
        }
    }
 }
?>