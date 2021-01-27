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
     * @author Ilan Dazanashvili
     */

const graphviewVue=require("./graphview.vue");
// const graphElement=require("../mixins/orm/graph_element");

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
                width: 0,
                height: 0
            }
        },
        created () {
            // We use the type to differentiate between objects
            this.type = 'item';
        }, 
        mounted () {
            this.$el.ref = this.name;
            let rect = this.$el.getBoundingClientRect();
            this.width = rect.width;
            this.height = rect.height;
        },
        methods: {
            drag_start (event) {
                element_mixin.methods.drag_start.call(this, event);
            },
            drag (event) {
                element_mixin.methods.drag.call(this, event);
            },
            drag_end (event) {
                element_mixin.methods.drag_end.call(this, event);
            },
            render_needed () {
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