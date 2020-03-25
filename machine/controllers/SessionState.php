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
use APIShift\Core\CacheManager;
use APIShift\Core\Status;

/**
 * Provides a set of request handlers that allows users to manipulate the session state element
 */
class SessionState {
    public static function getAllSessionStates() {
        Status::message(Status::SUCCESS, CacheManager::get("StateCollection"));
    }

    public static function getCurrentSessionState() {
       Status::message(Status::SUCCESS, $_SESSION['state']);
    }

    public static function changeState() {
        Core\SessionState::changeState($_POST['state']);
        Status::message(Status::SUCCESS, "State Changed!");
    }
    
    public static function addSessionState() {
        Status::message(Status::SUCCESS, $_SESSION['state']);
    }
    
    public static function removeSessionState() {
        Status::message(Status::SUCCESS, $_SESSION['state']);
    }
    
    public static function updateSessionState() {
        Status::message(Status::SUCCESS, $_SESSION['state']);
    }
}
?>