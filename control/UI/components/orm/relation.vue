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
        mixins: [APIShift.API.getMixin('graph/line_parent', true)],
        data () {
            return {
                drawer: null,
                group_index: -1,
                is_selected: false,
                element_sizes: {},
                point_indices: [],
                from_line_index: -1,
                to_line_index: -1,
            }
        },
        created () {
            window.graph_elements[this.$props.index] = this;
        }, 
        mounted () {
            this.expanded_functions.drag = this.drag_addition;
            this.expanded_functions.drag_start = this.drag_start_addition;
            let rect = this.$el.getBoundingClientRect();
            this.element_sizes = {
                width: rect.width,
                height: rect.height
            };

            graph_view.elements_loaded++;
            if(graph_view.first_load) {
                this.all_loaded();
                graph_view.bring_to_front(this.$props.index);
            }

        },
        methods: {
            all_loaded: function() {
                // Step 1: Determine from and to & fill missing points
                let from_index = this.$props.data.from !== undefined ?
                    graph_view.elements.findIndex((elem) => elem.id == this.$props.data.from && (elem.component_id == 0 || elem.component_id == 1 || elem.component_id == 4))
                    :
                    this.create_point(); // Create & attach point near relation
                
                let to_index = this.$props.data.to !== undefined ?
                    graph_view.elements.findIndex((elem) => elem.id == this.$props.data.to && (elem.component_id == 0 || elem.component_id == 1 || elem.component_id == 4))
                    :
                    this.create_point(false); // Create & attach point near relation
                
                setTimeout(()=>{
                    // Step 2: Create lines
                    this.from_line_index = this.create_line(from_index, {
                            is_curvy: true,
                            is_stroked: false,
                            marker_start: 'url(#many-arrow-head)',
                            is_persistent: true
                        },
                        false
                    );
                    
                    this.to_line_index = this.create_line(to_index, {
                            is_curvy: true,
                            is_stroked: false,
                            marker_end: 'url(#one-arrow-head)',
                            is_persistent: true
                        },
                        true
                    );
                });
            },
            on_point_drag_end_addition (event, point_index) {
                let line_index = graph_view.lines.findIndex(line => (line.to_index == this.$props.index && line.from_index == point_index) || (line.from_index == this.$props.index && line.to_index == point_index));
                let target_element = -1, z_index = 0;

                // TODO - decompose this shit
                for(let index in [...graph_view.elements.keys()]) {
                    let cmp_id = graph_view.elements[index].component_id;
                    // Skip non-item nor relations & self or undefined
                    if(window.graph_elements[index] === undefined || (cmp_id != 0 && cmp_id != 1 && cmp_id != 4) || graph_view.elements[index].is_deleted)
                        continue;
                    
                    // Check collisions 
                    if (graph_view.elements[index].component_id === 4) {
                        let group_rect = {
                            x: graph_view.elements[index].data.position.x,
                            y: graph_view.elements[index].data.position.y + graph_elements[index].get_rect.height - graph_elements[index].init_height,
                            height: graph_elements[index].init_height,
                            width: graph_elements[index].get_rect.width
                        };
                        if(window.graph_elements[index].data.z_index > z_index && graph_view.collision_check(window.graph_elements[point_index].get_rect, group_rect)) {
                            target_element = parseInt(index);
                            z_index = graph_view.elements[index].data.z_index;
                        }
                    } else if (window.graph_elements[index].data.z_index > z_index && graph_view.collision_check(window.graph_elements[point_index].get_rect, window.graph_elements[index].get_rect)) {
                        target_element = parseInt(index);
                        z_index = graph_view.elements[index].data.z_index;
                    }
                }
                
                // Drop on a connectable item 
                if (target_element !== -1) {
                    setTimeout(() => {
                        graph_view.$set(graph_view.elements[point_index], 'is_deleted', true);
                        graph_view.lines[line_index][graph_view.elements[point_index].data.is_left ? 'from_index' : 'to_index'] = target_element;
                        this.$props.data[graph_view.elements[point_index].data.is_left ? 'from' : 'to'] = graph_view.elements[target_element].id;          
                    });
                }
            },
            drag_start_addition: function() {
                if(this.group_index != -1) {
                    window.graph_elements[this.group_index].bring_to_front();
                }

                // Align z-index of self & points
                graph_view.bring_to_front(this.$props.index);
                this.point_indices.forEach(p => {
                    graph_view.bring_to_front(p);
                });
            },
            drag_addition: function(event) {
                if(this.group_index != -1)
                    window.graph_elements[this.group_index].update_group_size();
            },
            get_enums () {
                if (!this.enums) this.enums = graph_view.enums.filter(e => e.data.connected.find(connected => connected.type + connected.id === this.uid));
                return this.enums;
            },
            delete_connected_expanded (element_index) {
                // Step 1: Update element data
                let keys = ['from', 'to'];
                keys.forEach(key => {
                    this.$props.data[key] = this.$props.data[key] == graph_view.elements[element_index].id ? -1 : this.$props.data[key];
                })
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
                        return relations_indices.push(rel_index);
                    }
                });

                return relations_indices;
            },
            on_delete() {
                let my_id = graph_view.elements[this.$props.index].id;

                // Mark element as deleted
                graph_view.$set(graph_view.elements[this.$props.index], 'is_deleted', true);    

                 // Remove connection from connected enums
                this.get_connected_enums().forEach(enum_index => {
                    window.graph_elements[enum_index].delete_connected(this.index);
                });

                // Remove relation connection from item
                this.get_connected_relations().forEach(rel_index => {
                    window.graph_elements[rel_index].delete_connected(this.index);
                });
            
                // Delete connected lines
                graph_view.$set(graph_view.lines[this.from_line_index], 'is_deleted', true);
                graph_view.$set(graph_view.lines[this.to_line_index], 'is_deleted', true);

                // Delete connected points
                this.point_indices.forEach(point_index => {
                    graph_view.$set(graph_view.elements[point_index], 'is_deleted', true);
                });

                // Remove from owning group
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
                            graph_view.in_edit = this.$props.index;
                            graph_view.dialog = 0;
                        },
                        name: 'Edit',
                        icon: 'mdi-pencil',
                    },
                    {
                        starter: () => {

                            graph_view.context_menu.is_active = false;
                        },
                        name: 'Duplicate',
                        icon: 'mdi-content-duplicate',
                    },
                    {
                        starter: () => {

                            graph_view.context_menu.is_active = false;
                        },
                        actions: [
                            {
                                starter: () => {

                                    graph_view.context_menu.is_active = false;
                                },
                                name: 'One-to-One',
                                icon: 'mdi-relation-one-to-one',
                            },
                            {
                                starter: () => {
                                    graph_view.context_menu.is_active = false;

                                },
                                name: 'One-to-Many',
                                icon: 'mdi-relation-one-to-many',
                            },
                            {
                                starter: () => {
                                    
                                    graph_view.context_menu.is_active = false;
                                },
                                name: 'Many-to-Many',
                                icon: 'mdi-relation-one-to-one'
                            }
                    ],
                        name: 'Relate',
                        icon: 'mdi-transit-connection-variant',
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
                // Update owning group size
                if (this.group_index !== -1) 
                {
                    window.graph_elements[this.group_index].update_group_size();
                }
            }
        }
    }
</script>

<template>
    <div class="item" :class="{ is_selected , ghost_mode }" color="#8789ff"
        :style="transformation"
        @pointerdown.prevent="drag_start"
        @contextmenu.prevent="on_context"
        @dblclick.prevent="is_edit_mode = true"
        @pointerup.prevent="drag_end"
        >
            <v-avatar left class="item_type darken-4 purple">R</v-avatar>
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

.item.is_selected  {
    border: dashed white 2px;
    padding: 4px;
}
.type.ghost_mode {
    opacity: 0.7;
}

</style>