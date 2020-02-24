<?php
/**
 * APIShift Engine v1.0.0
 * (c) 2020-present Sapir Shemer, DevShift (devshift.biz)
 * Released under the MIT License with the additions present in the LICENSE.md
 * file in the root folder of the APIShift Engine original release source-code
 * @author Sapir Shemer
 */

namespace APIShift\Core;

class Item {
    // Item types

    // Relation Types
    public const SINGLE_TO_SINGLE_RELATION = 0;
    public const SINGLE_TO_MULTI_RELATION = 1;
    public const MULTI_TO_MULTI_RELATION = 2;
    public const EXTENDS_RELATION = 3;
    
}
?>