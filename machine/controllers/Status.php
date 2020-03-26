<?php
/**
 * APIShift Engine v1.0.0
 * (c) 2020-present Sapir Shemer, DevShift (devshift.biz)
 * Released under the MIT License with the additions present in the LICENSE.md
 * file in the root folder of the APIShift Engine original release source-code
 * 
 * @author Sapir Shemer
 */

namespace APIShift\Controllers;
use APIShift\Core;

/**
 * Controller for retrieving information about the status component of the system
 */
class Status {
    /**
     * Returns to the user all the available statuses
     */
    public static function getAllStatuses() {
        Core\Status::message(Core\Status::SUCCESS, Core\CacheManager::get('StatusCollection'));
    }
}
?>