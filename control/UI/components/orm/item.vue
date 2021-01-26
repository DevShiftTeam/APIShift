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
        mixins: [APIShift.API.getMixin('orm/graph_element')],
        props: {
            is_relation: Boolean,
            name: String
        },
        data () {
            return {
                drawer: null,
                item_rect: {},
                drag_range: {
                    x: 0,
                    y: 0
                },
                init_relative_drag: {
                    x: 0,
                    y: 0
                }
            }
        },
        created () {
        }, 
        mounted () {
            this.$el.ref = this.name;
        },
        methods: {
            drag_start (event) {
                // Get mouse coordinates relative to element
                this.item_rect = this.$el.getBoundingClientRect();
                this.init_relative_drag.x = event.clientX - this.item_rect.left;
                this.init_relative_drag.y = event.clientY - this.item_rect.top;

                // Set global drag function
                graph_view.drag_handler = this.drag;
            },
            drag (event) {

                this.drag_range.x = event.clientX - this.init_relative_drag.x - graph_view.graph_rect.left - graph_view.camera.x;
                this.drag_range.y = event.clientY - this.init_relative_drag.y - graph_view.graph_rect.top - graph_view.camera.y;
                this.move_to(this.drag_range);
            },
            drag_end (event) {
                // Reset global drag function
                graph_view.drag_handler = graph_view.pointer_move;
            }
        }
    }
</script>

<template>
    <div class="item"
        :style="transformation"
        @pointerdown.prevent="drag_start"
        @pointerup.prevent="drag_end">
            <div class="item_type">{{ is_relation ? 'R' : 'I' }}</div>
            <div style="display: inline;">{{ name }}</div>
    </div>
</template>

<style scoped>
/* Please style this crap, with style */
.item_type {
    border: solid white 1px;
    border-radius: 100%;
    text-align: center;
    display: inline;
    padding-left: 7px;
    padding-right: 7px;
}

.item {
    border: solid white 1px;
    border-radius: 10px;
    padding: 5px;
    display: inline-block;
    position: absolute;
    cursor: copy !important;
    background: #8789ff;
}
</style>