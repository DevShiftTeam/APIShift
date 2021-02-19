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
                uid: 'i',
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
            // Draw relation lines
            if(this.$props.is_relation) {
                let self_data = { type: 'i', id: this.component_id }
                graph_view.create_line(this.$props.data.from, self_data, { item_to_relation: true, relate_type: this.$props.data.type });
                graph_view.create_line(self_data, this.$props.data.to, { relation_to_item: true, relate_type: this.$props.data.type });
            }
        },
        methods: {
            on_delete() {
                // Delete lines from the graph & connected relations recursivly
                this.get_lines().forEach(line => {
                    let to_instance = graph_view.$refs[line.to_uid];
                    let from_instance = graph_view.$refs[line.from_uid];
                    
                    graph_view.delete_line(line.line_uid);

                    if (line.settings.item_to_relation && !this.is_relation) {
                        let relation = to_instance;
                        relation.on_delete();
                    } else if (line.settings.relation_to_item && !this.is_relation) {
                        let relation = from_instance;
                        relation.on_delete(); 
                    }
                });

                // Delete element from the graph
                let id = this.component_id;
                graph_view.items = graph_view.items.filter((item) => item.id !== id);

                // Delete element connections from enum's 
                graph_view.item_enums = graph_view.item_enums.filter((item_enum) => item_enum.item_id !== id);

                // Remove element from group and recalculate group boundries if exists
                if (!this.in_group) return;
                graph_view.group_items = graph_view.group_items.filter((group_item) => group_item.item_id !== id);
                this.group_container.update_items();
                this.group_container.set_rect();
            },
            render_needed () {
            }
        },
        computed: {
        }
    }
</script>

<template>
    <div class="item" :class="{ selected , ghost_mode }" color="#8789ff"
        :style="transformation"
        @pointerdown.prevent="drag_start"
        @pointerup.prevent="drag_end">
            <v-avatar left class="item_type darken-4" :class="is_relation ? 'purple' : 'blue'">{{ is_relation ? 'R' : 'I'}}</v-avatar>
            <div style="display: inline;">{{ component_id }}</div>
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
    cursor: copy ;
    background: #8789ff;
    box-shadow: 50px 50px 50px rgba(255, 242, 94, 0); /* Removing weird trace on chrome */
}

.item.selected {
    border: dashed white 2px;
    padding: 4px;
}
.type.ghost_mode {
    opacity: 0.7;
}
</style>