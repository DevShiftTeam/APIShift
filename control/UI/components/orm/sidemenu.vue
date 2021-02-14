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

    module.exports = {
        props: {
        },
        data () {
            return {
            }
        },
        created () {
        },
        mounted () {
            graph_view.$refs['sidemenu'] = this;
        },
        methods: {
            action_creator(action_name) {
                switch (action_name) {
                    case 'add-item':
                        graph_view.$refs['item_builder'].build('item');
                        break;
                    case 'add-relation':
                        graph_view.$refs['item_builder'].build('relation');
                        break;
                    case 'delete-element':
                        graph_view.cursor_state = {type: "delete"};
                        break;
                    case 'add-enum':
                        graph_view.cursor_state = {type: "create", data: 'add-enum'};
                        break;
                    case 'add-enum-type':
                        graph_view.cursor_state = {type: "create", data: 'add-enum-type'};
                        break;
                    case 'select':
                        graph_view.cursor_state = {type: "select"};
                        break;
                    default:
                        break;
                }
            }
        },
        computed: {
            // Just for testing 
            path_data () {
                this.update();

                const bezierWeight = 0.675; // Amount to offset control points
                const dx           =  Math.abs(this.from_position.x - this.to_position.x) * bezierWeight * !this.$props.settings.enum_to_item;
                const c1           = { x: this.from_position.x + dx, y: this.from_position.y };
                const c2           = { x: this.to_position.x - dx, y: this.to_position.y };

                return `M ${this.from_position.x} ${this.from_position.y} C ${c1.x} ${c1.y} ${c2.x} ${c2.y} ${this.to_position.x} ${this.to_position.y}`;
            },
        }
    }
</script>

<template>
        <g>
        <path @pointerdown="pointer_down" 
            :style="{ 'stroke-width': `${settings.enum_to_item ? 4 : 4}`,
                        'stroke-dasharray': `${settings.enum_to_item ? '11,5' : 'none'}`}"
            :d="path_data"
            >
        </path>
        <!-- <polygon points="0 0, 10 3.5, 0 7" /> -->
        </g>
</template>

<style scoped>
/* Please style this crap, with style */    
    path {
        fill: none;
        stroke: dodgerblue;
        stroke-width: 6;
        cursor: pointer;
    }
    
</style>