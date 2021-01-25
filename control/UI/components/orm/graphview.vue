<script>
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

    // This shit is made for scripting
    module.exports = {
        data () {
            return {
                drawer: null,
                item_comp: APIShift.API.getComponent('orm/item', true),
                items: [
                    { is_relation: false, name: "wait" },
                    { is_relation: false, name: "haha" }
                ],
                relative: {
                    x: 0,
                    y: 0
                },
                scale: 1
            }
        },
        created () {
            for(var x in [...Array(100).keys()]) {
                this.items.push({
                    is_relation: false, name: "w" + x
                })
            }
        }
    }
</script>

<template>
    <div>
        <component
            v-for="item in items"
            :is="item_comp"
            :key="item.name"
            :relative="relative"
            :is_relation="item.is_relation"
            :scale="scale"
            :name="item.name"></component>
    </div>
</template>

<style scoped>
/* Please style this crap, with style */
</style>