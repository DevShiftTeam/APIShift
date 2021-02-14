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
            data: Object,
            name: String
        },
        data () {
            return {
                drawer: null,
                group_container: null,
                selected: false
            }
        },
        created () {
            const self = this;

            // We use the type to differentiate between objects
            this.type = this.$props.is_relation ? 'relation' : 'item';

            this.expanded_functions.drag_start = (event) => {

            };
            this.expanded_functions.drag = (event) => {
                if (self.group_container) {
                    self.group_container.set_rect();
                }
            };
            this.expanded_functions.drag_end = (event) => {

            };

        }, 
        mounted () {
            if(this.$props.is_relation) {
                // Draw relation lines
                graph_view.create_line(this.$props.data.from, this.component_id, { item_to_relation: true, relate_type: this.$props.data.type });
                graph_view.create_line(this.component_id, this.$props.data.to, { relation_to_item: true, relate_type: this.$props.data.type });
            }
        },
        methods: {
            render_needed () {
            }
        }
    }
</script>

<template>
    <div class="item" :class="{ selected }" color="#8789ff"
        :style="transformation"
        @pointerdown.prevent="drag_start"
        @pointerup.prevent="drag_end">
            <v-avatar left class="item_type darken-4" :class="is_relation ? 'purple' : 'blue'">{{ is_relation ? 'R' : 'I'}}</v-avatar>
            <div style="display: inline;">{{ name }}</div>
    </div>
</template>

<style scoped>
/* Please style this crap, with style */
.item_type {
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
    box-shadow: 50px 50px 50px rgba(255, 242, 94, 0); /* Removing weird trace on chrome */
}

.item.selected {
    border: dashed white 2px;
    padding: 4px;
}
</style>