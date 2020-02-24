<?php
/**
 * APIShift Engine v1.0.0
 * (c) 2020-present Sapir Shemer, DevShift (devshift.biz)
 * Released under the MIT License with the additions present in the LICENSE.md
 * file in the root folder of the APIShift Engine original release source-code
 * @author Sapir Shemer
 */

namespace APIShift\Models;
use APIShift\Core;

/**
 * Your gateway to personal extensions
 */
class Extensions {
    public static function run($data) {
        $request = "../extensions/{$data['name']}/{$data['controller']}.php";

        if(file_exists($request))
        {
            require $request;
            $data['controller']::$data['method']();
        }

        Core\Status::message(Core\Status::ERROR, "Extensions couldn't be found");
    }
}
?>