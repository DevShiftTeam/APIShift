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

/**
 * A component holding helper functions that are repeated throughout the program and connot be categorized to a specific component
 */
class Helper {
    /**
     * This is a smart array key mapper that maps the whole keys as a tree
     * It avoids recursion by storing the positions on the tree when working on each child
     * @param   $arr        The array to map
     */
    public static function smartArrayKeys(array $arr) {
        // Array of current parent-child sizes and algorithmic position
        $sizes = [ count($arr) - 1 ];
        $counters = [ 0 ];
        $result = array_keys($arr);

        while(isset($counters[0]) && $counters[0] <= $sizes[0])
        {
            // Determine current counter
            $current_counter = count($counters) - 1;
            $name = [$counters[0]];
            // Remove counter if range done and come back to previous position
            if($counters[$current_counter] >= $sizes[$current_counter])
            {
                unset($sizes[$current_counter]);
                unset($counters[$current_counter]);
                continue;
            }

            // Create key string

            // Add to result

            // If array then extend into child

            $counters[$current_counter]++;
        }
    }
}
?>