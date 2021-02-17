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
     * @author Ilan Dazanashvili
     */

    //TODO: Design better z-index system.
    module.exports = {
        props: {
            data: Object
        },
        data () {
            return {

            }
        },
        created () {
        },
        mounted () {
            graph_view.$refs['side_menu'] = this;

        },
        methods: {
            actionCreator(name) {
                switch (name) {
                    case 'item':
                        graph_view.$refs['item_builder'].open();
                        break;
                    case 'relation':
                        graph_view.$refs['relation_builder'].open();
                        break;
                    case 'delete':
                        graph_view.$refs['item_builder'].open();
                        break;
                    case 'enum':
                        graph_view.enums.push();
                        break;
                    case 'type':
                        
                        break;
                    case 'group':
                        
                        break;
                    default:
                        break;
                }
            }
        }, 
    }
</script>

<template>
    <div id="side_menu">
        <div class="action" @click="actionCreator('item')"> 
            item
        </div>
        <div class="action" @click="actionCreator('relation')">
            relation
        </div>
        <div class="action" @click="actionCreator('delete')">
            delete
        </div>
        <div class="action" @click="actionCreator('enum')">
            enum
        </div>
        <div class="action" @click="actionCreator('type')">
            type
        </div> 
        <div class="action" @click="actionCreator('group')">
            group
        </div>
    </div>
    
</template>

<style scoped>
/* Please style this crap, with style */

#side_menu {
    position: absolute;
    left: 50px;
    top: 50px;
    width: 50px;
    height: 50px;
    transform-origin: 0 0;
    background: red;
}
</style>