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