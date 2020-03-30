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

use APIShift\Core\DatabaseManager;
use APIShift\Core\Status;

/**
 * Interface containing the available main request of the system's control panel
 */
class Control {
    /**
     * Returns all the pages of the control panel
     */
    public static function getPages() {
        $res = [];
        if(DatabaseManager::fetchInto("main", $res, "SELECT * FROM admin_pages", [], 'id') === false) Status::message(Status::ERROR, "Couldn't retrieve pages");;
        Status::message(Status::SUCCESS, $res);
    }
}
?>