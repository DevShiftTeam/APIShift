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

namespace APIShift\Controllers\Admin\Access;

use APIShift\Core\CacheManager;
use APIShift\Core\DatabaseManager;
use APIShift\Core\Status;

/**
 * Interface containing the available main request of the system's control panel
 */
class Session {
    /**
     * Returns all the sessions and their access tasks
     */
    public static function getSessionTasks()
    {
        $res = [];
        if (DatabaseManager::fetchInto("main", $res,
            "SELECT inputs.name as input_name, tasks.name as task_name, tasks.*, session_states.* FROM tasks
                JOIN session_states ON tasks.id = session_states.auth_task
                LEFT JOIN inputs ON session_states.auth_input = inputs.id") === false)
            Status::message(Status::ERROR, "Couldn't retrieve session tasks");
        Status::message(Status::SUCCESS, $res);
    }

    /**
     * Remove an access rule.
     * The structure of the request should like like this:
     * [
     *  'elem' => To which element to apply the auth rule (session, controller, database)
     *  'id' => Element ID to remove from
     * ]
     */
    public static function removeAccessRule()
    {
        $res = DatabaseManager::query("main", "UPDATE session_states SET auth_task = NULL WHERE id = :id", $_POST['id']);
        if (!$res) Status::message(Status::ERROR, "Couldn't remove rule from DB");
        Status::message(Status::SUCCESS, "Updated Successfully!");
    }
}