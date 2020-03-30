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

namespace APIShift\Controllers\Main;

use APIShift\Core;
use APIShift\Core\Status;

/**
 * Provides a set of request handlers that allows users to recieve general information about the session state
 */
class SessionState {
    /**
     * Get the current session state used
     */
    public static function getCurrentSessionState() {
       Status::message(Status::SUCCESS, $_SESSION['state']);
    }

    /**
     * Change the current state to a new one.
     * Automatically run authorization process defined for the state
     */
    public static function changeState() {
        Core\SessionState::changeState($_POST['state']);
        Status::message(Status::SUCCESS, "State Changed!");
    }
}
?>