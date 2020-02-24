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

class Status {
    public static function getAllStatuses() {
        Core\Status::message(Core\Status::SUCCESS, apcu_fetch('StatusCollection'));
    }
}
?>