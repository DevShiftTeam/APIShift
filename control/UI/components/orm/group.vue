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
        mixins: [APIShift.API.getMixin('orm/graph_element')],
        data () {
            return {
                init_height: 0,
                init_width: 0,
                occupied_width: 0,
                occupied_height: 0,
                element_indices: [],
                group_indices: [],
                parent_group_index: -1,
                is_selected: false
            }
        },
        created () {
            window.graph_elements[this.$props.index] = this;

            if(this.$props.data.position === undefined) {
                this.$set(this.$props.data, 'position', { x: 0, y: 0 });
            }

            this.expanded_functions.drag_start = this.drag_start_addition;
            this.expanded_functions.drag = this.drag_addition;
        },
        mounted () {
            // Determine initial rect pre bounding setup
            let rect = this.$el.getBoundingClientRect();
            this.init_height = rect.height;
            this.init_width = rect.width;
            graph_view.elements_loaded++;

            // Remove common elements of parent groups
            this.parent_group_index = graph_view.elements.findIndex(el => el.id == this.$props.data.parent && el.component_id == 4);
            if (this.parent_group_index != -1)
                graph_view.elements[this.parent_group_index].data.elements = graph_view.elements[this.parent_group_index].data.elements
                                                                            .filter (elem => this.$props.data.elements.indexOf(elem) < 0);
            
            if(graph_view.first_load) {
                this.all_loaded();
                this.bring_to_front();
            }


        },
        methods: {
            all_loaded: function() {
                // Determine new calculated rect & bounds
                this.update_indices();
                this.update_group_size();
                if(this.parent_group_index != -1) { 
                    window.graph_elements[this.parent_group_index].update_indices();
                    window.graph_elements[this.parent_group_index].update_group_size();
                }

                this.bring_to_front();
            },
            update_indices: function() {
                this.element_indices = [];
                this.group_indices = [];

                // Get all element indices
                for(let elem in this.$props.data.elements) {
                    let item_id = this.$props.data.elements[elem];
                    let index = graph_view.elements.findIndex(el => el.id === item_id && (el.component_id == 1 || el.component_id == 0) && !el.is_deleted);
                    if(index == -1) continue;
                    window.graph_elements[index].group_index = this.$props.index;
                    this.element_indices.push(index);
                }

                // Get all group indices
                for(let grp_index in graph_view.elements) {
                    if(graph_view.elements[grp_index].component_id != 4 || graph_view.elements[grp_index].data.parent != this.$props.id || graph_view.elements[grp_index].is_deleted)
                        continue;
                    window.graph_elements[grp_index].parent_group_index = this.$props.index;
                    this.group_indices.push(grp_index);
                };

                // Delete group if empty 
                if (this.element_indices.length + this.group_indices.length === 0) this.on_delete();    
            },
            bring_to_front: function(ignore_parent = false) {
                // If father present then call only the father's function
                if(this.parent_group_index != -1 && !ignore_parent)
                {
                    window.graph_elements[this.parent_group_index].bring_to_front();
                    return;
                }

                graph_view.bring_to_front(this.$props.index);

                for(let elem in this.element_indices)
                    graph_view.bring_to_front(this.element_indices[elem]);
                    
                for(let elem in this.group_indices)
                    window.graph_elements[this.group_indices[elem]].bring_to_front(true);
            },
            update_group_size () {
                let rect = {};
                
                // Calculate size via elements
                for(let elem in this.element_indices) {
                    let index = this.element_indices[elem];

                    // Get first rect
                    let temp_rect = window.graph_elements[index].get_rect;

                    if(rect.x === undefined) {
                        rect.x = temp_rect.x;
                        rect.y = temp_rect.y;
                        rect.x_end = temp_rect.x + temp_rect.width;
                        rect.y_end = temp_rect.y + temp_rect.height;
                    }
                    else {
                        // Get start of group
                        if(rect.x > temp_rect.x) rect.x = temp_rect.x;
                        if(rect.y > temp_rect.y) rect.y = temp_rect.y;

                        // Get end of group
                        if(rect.x_end < temp_rect.x + temp_rect.width)
                            rect.x_end = temp_rect.x + temp_rect.width;
                        if(rect.y_end < temp_rect.y + temp_rect.height)
                            rect.y_end = temp_rect.y + temp_rect.height;
                    }
                }

                // Calculate size via sub_groups
                for(let grp in this.group_indices) {
                    let index = this.group_indices[grp];

                    // Get first rect
                    let temp_rect = window.graph_elements[index].get_rect;

                    if(rect.x === undefined) {
                        rect.x = temp_rect.x;
                        rect.y = temp_rect.y;
                        rect.x_end = temp_rect.x + temp_rect.width;
                        rect.y_end = temp_rect.y + temp_rect.height;
                    }
                    else {
                        // Get start of group
                        if(rect.x > temp_rect.x) rect.x = temp_rect.x;
                        if(rect.y > temp_rect.y) rect.y = temp_rect.y;

                        // Get end of group
                        if(rect.x_end < temp_rect.x + temp_rect.width)
                            rect.x_end = temp_rect.x + temp_rect.width;
                        if(rect.y_end < temp_rect.y + temp_rect.height)
                            rect.y_end = temp_rect.y + temp_rect.height;
                    }
                }

                // Update rect data 
                this.$props.data.position.x = rect.x;
                this.$props.data.position.y = rect.y;
                this.occupied_width = Math.max(rect.x_end - rect.x, this.init_width);
                this.occupied_height = rect.y_end - rect.y;

                // Update parent
                if(this.parent_group_index != -1) window.graph_elements[this.parent_group_index].update_group_size();
            },
            drag_start_addition: function(event) {
                if(this.parent_group_index != -1) window.graph_elements[this.parent_group_index].bring_to_front();

                // Initialize all elements
                for(let elem in this.element_indices) {
                    let index = this.element_indices[elem];
                    graph_view.bring_to_front(index);
                    window.graph_elements[index].init_position = Object.assign({}, window.graph_elements[index].data.position);
                }

                // Initialize all sub-groups
                for(let sub_group in this.group_indices)
                    window.graph_elements[this.group_indices[sub_group]].drag_start(event);
                
                graph_view.drag_handler = this.drag;
            },
            drag_addition: function(event) {
                if(this.parent_group_index != -1 && !(window.graph_elements[this.parent_group_index].is_dragging))
                    window.graph_elements[this.parent_group_index].update_group_size();

                // Drag all elements
                for(let elem in this.element_indices)
                    window.graph_elements[this.element_indices[elem]].drag(event);

                // Drag all sub groups
                for(let sub_group in this.group_indices)
                    window.graph_elements[this.group_indices[sub_group]].drag(event);
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
                    return el.component_id == 1 && !el.is_deleted;
                });


                // Infer connected enums indices
                let relations_indices = [];
                relations.forEach((rel) => {
                    if (rel.data.to == my_id || rel.data.from == my_id) {
                        let rel_index = graph_view.elements.findIndex(el => el.id == rel.id && el.component_id == 1); 
                        return relations_indices.push(rel_index);
                    }
                });

                return relations_indices;
            },
            on_delete () {
                let my_id = graph_view.elements[this.$props.index].id;
                
                // Mark as deleted
                graph_view.$set(graph_view.elements[this.$props.index], 'is_deleted', true);

                // Remove connection from connected enums
                this.get_connected_enums().forEach(enum_index => {
                    window.graph_elements[enum_index].remove_connection(my_id);
                });

                // Remove relation connection form item
                this.get_connected_relations().forEach(rel_index => {
                    window.graph_elements[rel_index].remove_connection(my_id);
                });

                // Detach inner elements
                for(let elem in this.element_indices) {
                    window.graph_elements[this.element_indices[elem]].group_index = -1;
                }

                // Detach inner groups
                for(let elem in this.group_indices) {
                    graph_view.elements[this.group_indices[elem]].data.parent = this.$props.data.parent;
                    window.graph_elements[this.group_indices[elem]].parent_group_index = -1;
                }

                // Add group elements to owning group's elements
                if (this.parent_group_index !== -1) 
                {
                    graph_view.elements[this.parent_group_index].data.elements = [...window.graph_elements[this.parent_group_index].data.elements, ...this.$props.data.elements];
                    window.graph_elements[this.parent_group_index].update_indices();
                    window.graph_elements[this.parent_group_index].update_group_size();
                }
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
            },
            on_input_addition () {
                // Re-compute base width & height length
                let info_el = this.$el.querySelector("#group_info");
                this.init_height = info_el.offsetHeight;
                this.init_width = info_el.querySelector('[contenteditable').offsetWidth + info_el.querySelector('.v-avatar').offsetWidth + 8;

                this.update_group_size();
            },
            move_by (dx,dy) {

                // Move by all elements
                for(let elem in this.element_indices)
                    window.graph_elements[this.element_indices[elem]].move_by(dx, dy);

                // Move all sub groups
                 for(let sub_group in this.group_indices)
                     window.graph_elements[this.group_indices[sub_group]].move_by(dx, dy);
            }
        },
        computed: {
            get_rect: function() {
                return {
                    x: this.$props.data.position.x,
                    y: this.$props.data.position.y,
                    width: this.occupied_width,
                    height: this.occupied_height + 24,
                };
            },
            from_position: function() {
                return {
                    x: this.$props.data.position.x + this.occupied_width ,
                    y: this.$props.data.position.y + (this.occupied_height + this.init_height) / 2
                };
            },
            to_position: function() {
                return {
                    x: this.$props.data.position.x,
                    y: this.$props.data.position.y + (this.occupied_height + this.init_height) / 2
                };
            },
            sizes () {
                return {
                    minWidth: this.occupied_width + 'px',
                    height: this.occupied_height + 'px',
                }
            } 
        },
    }
</script>

<template>
    <div class="group" color="#8789ff"
        :style="transformation" :class="{ is_selected }"
        @pointerdown.prevent="drag_start"
        @dblclick.prevent="is_edit_mode = true"
        @pointerup.prevent="drag_end"
        >
        <div class="group_container" :style="sizes">
        </div>

        <div id="group_info" ref="data"
            @contextmenu="on_context">
            <v-avatar class="group_type darken-4 green" style="height: initial; min-width: initial; width: initial;">G</v-avatar>
            <div style="margin-left: 5px; line-height: 1;"
                @input="on_input"
                @blur="on_blur" 
                :contenteditable="is_edit_mode">
                    {{name}}
            <div>
        </div>
    </div>
</template>

<style scoped>
/* Please style this crap, with style */

.group_type {
    text-align: center;
    display: inline;
    padding-left: 7px;
    padding-right: 7px;
}

.group {
    min-width: 10ch;
    border: solid white 1px;
    border-radius: 10px;
    display: flex;
    flex-direction: column;
    position: absolute;
    cursor: copy;
    box-shadow: 50px 50px 50px rgba(255, 242, 94, 0); /* Removing weird trace on chrome */
}

.item_type {
    text-align: center;
    display: inline;
    padding-left: 7px;
    padding-right: 7px;
}



#group_info {
    display: inline-flex;
    border: solid white 1px;
    border-radius: 10px;
    padding: 1px;
    bottom: 0;
    width: 100%;
    cursor: copy;
    background: #8789ff;
    box-shadow: 50px 50px 50px rgba(255, 242, 94, 0); /* Removing weird trace on chrome */
}
.group_container {
    height: 0;
    width: 0;
    pointer-events: none;
    opacity: 0.5;
    background:#aad6ff;
    z-index: -1;
}

.group.is_selected {
    outline: dashed white 2px;
}
</style>