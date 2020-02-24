<?php
/**
 * APIShift Engine v1.0.0
 * (c) 2020-present Sapir Shemer, DevShift (devshift.biz)
 * Released under the MIT License with the additions present in the LICENSE.md
 * file in the root folder of the APIShift Engine original release source-code
 * @author Sapir Shemer
 */

namespace APIShift\Controllers;

use APIShift\Core;
use APIShift\Core\Status;

/**
 * Manages components of the control panel - pages and other view data
 */
class SessionState {
    public static function getCurrentSessionState() {
       Status::message(Status::SUCCESS, $_SESSION['state']);
    }

    public static function changeState($params) {
        Core\SessionState::changeState($params['state'], $params);
        Status::message(Status::SUCCESS, "State Changed!");
    }
}
?>