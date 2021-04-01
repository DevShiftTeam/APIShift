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
        mixins: [APIShift.API.getMixin('graph/graph_element')],
        data () {
            return {
                drawer: null,
                group_index: -1,
                is_selected: false,
                element_sizes: {}
            }
        },
        created () {
            window.graph_elements[this.$props.index] = this;
        }, 
        mounted () {
            let rect = this.$el.getBoundingClientRect();
            this.element_sizes = {
                width: rect.width,
                height: rect.height
            };
            this.expanded_functions.drag_start = this.drag_start_addition;
            this.expanded_functions.drag = this.drag_addition;
            graph_view.elements_loaded++;

            if(graph_view.first_load) {
                graph_view.bring_to_front(this.$props.index);
            }
        },
        methods: {
            drag_start_addition: function() {
                // console.log(APIShift.API.getMixin('graph/graph_element'));
                if(this.group_index != -1) {
                    window.graph_elements[this.group_index].bring_to_front();
                    graph_view.bring_to_front(this.$props.index);
                }
            },
            drag_addition: function() {
                if(this.group_index != -1)
                    window.graph_elements[this.group_index].update_group_size();
            },
            get_connected_enums () {
                let my_id = graph_view.elements[this.$props.index].id;

                // Iterate through enums
                let enums = graph_view.elements.filter((el) => {
                    return el.component_id == 3 && !el.is_deleted;;
                });

                // Infer connected enums indices
                let enums_indices = [];
                enums.forEach((e) => {
                    if (e.data.connected.find(i => i == my_id)) {
                        let enum_index = graph_view.elements.findIndex(el => el.id == e.id && el.component_id == 3); 
                        return enums_indices.push(enum_index);
                    }
                });

                return enums_indices;
            },
            get_connected_relations () {
                let my_id = graph_view.elements[this.$props.index].id;

                // Iterate through enums
                let relations = graph_view.elements.filter((el) => {
                    return el.component_id == 1 && !el.is_deleted;;
                });


                // Infer connected enums indices
                let relations_indices = [];
                relations.forEach((rel) => {
                    if (rel.data.to == my_id || rel.data.from == my_id) {
                        let rel_index = graph_view.elements.findIndex(el => el.id == rel.id && el.component_id == 1); 
                        relations_indices.push(rel_index);
                    }
                });

                return relations_indices;
            },
            on_delete() {
                let my_id = graph_view.elements[this.$props.index].id;

                // Remove connection from connected enums
                this.get_connected_enums().forEach(enum_index => {
                    window.graph_elements[enum_index].remove_connection(my_id);
                });

                // Remove relation connection from item
                this.get_connected_relations().forEach(rel_index => {
                    window.graph_elements[rel_index].replace_connected(this.$props.index, -1);
                });

                // Remove from owning group
                if (this.group_index !== -1) 
                {
                    window.graph_elements[this.group_index].data.elements = window.graph_elements[this.group_index].data.elements.filter(id => id != my_id);
                    window.graph_elements[this.group_index].update_indices();
                    window.graph_elements[this.group_index].update_group_size();
                }

                // Removing element from screen
                graph_view.$set(graph_view.elements[this.$props.index], 'is_deleted', true);

            },
            on_context_addition () {
                graph_view.context_menu.actions = [
                    {
                        starter: () => {
                            this.is_edit_mode = true;
                            graph_view.context_menu.is_active = false;
                        },
                        name: 'Edit',
                        icon: 'mdi-pencil',
                    },
                    {
                        starter: () => {

                        },
                        name: 'Duplicate',
                        icon: 'mdi-content-duplicate',
                    },
                    {
                        starter: () => {
                            this.on_delete();
                            graph_view.context_menu.is_active = false;
                        },
                        name: 'Delete',
                        icon: 'mdi-delete-outline',
                    },
                ]
            }
        },
        computed: {
            from_position: function() {
                return {
                    x: this.$props.data.position.x + this.get_rect.width,
                    y: this.$props.data.position.y + this.get_rect.height / 2
                };
            },
            to_position: function() {
                return {
                    x: this.$props.data.position.x,
                    y: this.$props.data.position.y + this.get_rect.height / 2
                };
            }
        }
    }
</script>

<template>
    <div class="item" :class="{ is_selected , ghost_mode }" color="#8789ff"
        :style="transformation"
        @pointerdown="drag_start"
        @contextmenu.prevent="on_context"
        @dblclick.prevent="is_edit_mode = true"
        @pointerup.prevent="drag_end">
            <v-avatar left class="item_type darken-4 blue">I</v-avatar>
            <div 
            @input="on_input"
            @blur="on_blur" 
            :contenteditable="is_edit_mode"
            style="display: inline-block;">
                {{name}}
            <div>
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

.item.is_selected {
    border: dashed white 2px;
    padding: 4px;
}
.type.ghost_mode {
    opacity: 0.7;
}

.user-input {
    overflow-y: auto;
    max-width: auto;
}
</style>