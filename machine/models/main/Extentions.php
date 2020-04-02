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

namespace APIShift\Models\Main;
use APIShift\Core;

/**
 * Gateway to personal extensions
 */
class Extensions {
    /**
     * Template function to run commands from extensions
     * 
     * @param array $data Array of commands to send to the extension
     */
    public static function run($data) {
        // TODO: redesign
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