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

    // This shit is made for scripting
    module.exports = {
        mixins: [APIShift.API.getMixin('orm/graph_element')],
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
                if(this.group_index != -1) {
                    window.graph_elements[this.group_index].bring_to_front();
                    graph_view.bring_to_front(this.$props.index);
                }
            },
            drag_addition: function() {
                if(this.group_index != -1)
                    window.graph_elements[this.group_index].update_group_size();
            },
            on_delete_addition() {
                let my_id = graph_view.elements[this.$props.index].id;

                // Step 1: Remove from owning group
                if (this.group_index !== -1) 
                {
                    window.graph_elements[this.group_index].data.elements = window.graph_elements[this.group_index].data.elements.filter(id => id != my_id);
                    window.graph_elements[this.group_index].update_indices();
                    window.graph_elements[this.group_index].update_group_size();
                }
            },
            on_input_addition () {
                if (this.group_index !== -1) 
                    window.graph_elements[this.group_index].update_group_size();
            },
            on_context_addition () {
                graph_view.context_menu.actions = [
                    {
                        starter: () => {

                            graph_view.context_menu.is_active = false;
                            graph_view.dialog_open = true;
                            this.is_edit_mode = true;
                            graph_view.in_edit = this.$props.index;
                            graph_view.dialog = 0;
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
        },
        watch: {
            '$props.name' () {
                this.ui_refresher++;

                // Update owning group size - running notice how i used plug 
                let plug = setTimeout(
                    () => {
                        if (!plug) return;
                        plug = null;

                        // Critical part - runs only once due to the plugging mechaism provided and prevent reduant excecutions 
                        if (this.parent_group_index !== -1) 
                        {
                            window.graph_elements[this.group_index].update_group_size();
                        }
                    }
                );
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