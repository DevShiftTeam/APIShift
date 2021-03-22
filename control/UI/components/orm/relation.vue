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
        data () {
            return {
                drawer: null,
                group_index: -1,
                is_selected: false,
                element_sizes: {},
                point_indices: [],
                from_line_index: -1,
                to_line_index: -1
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
                    window.graph_view.lines.push({
                        from_index: from_index,
                        to_index: this.$props.index,
                        data: {
                            is_curvy: true,
                            is_stroked: false,
                            is_rel_source: false
                        }
                    });
                    this.from_line_index = window.graph_view.lines.length - 1;
                    
                    window.graph_view.lines.push({
                        from_index: this.$props.index,
                        to_index: to_index,
                        data: {
                            is_curvy: true,
                            is_stroked: false,
                            is_rel_source: true
                        }
                    });
                    this.to_line_index = window.graph_view.lines.length - 1;
                })
            },
            create_point: function(is_left = true, position = null, is_deleted = false) {
                let my_rect = this.get_rect();

                let point = 
                { 
                    id: 0, component_id: 5, name: '',
                    data:
                    {
                        position: position ? position : {
                            x: is_left ? my_rect.x - 20 : my_rect.x + my_rect.width + 20,
                            y: my_rect.y + my_rect.height / 2
                        },
                        z_index: graph_view.elements.length+1,
                        rel_index: this.$props.index,
                        is_left
                    },
                    is_deleted
                }

                graph_view.elements.push(
                    point
                );
                this.point_indices.push(graph_view.elements.length - 1);

                // this.point_indices.push(ret_index);
                return graph_view.elements.length - 1;
            },
            connect_to_line: function(is_from_or_to, element_index, to_delete = true) {
                if(element_index == this.$props.index) return;
                
                // Change from
                if (is_from_or_to === true) {
                    this.$props.data.from = graph_view.elements[element_index].id;
                    setTimeout(() => {
                        if (to_delete) graph_view.$set(graph_view.elements[graph_view.lines[this.from_line_index].from_index], 'is_deleted', true);
                        graph_view.lines[this.from_line_index].from_index = element_index;
                    });
                } // Change to 
                else if (is_from_or_to === false) {
                    this.$props.data.to = graph_view.elements[element_index].id;
                    setTimeout(() => {
                        if (to_delete) graph_view.$set(graph_view.elements[graph_view.lines[this.to_line_index].to_index], 'is_deleted', true);
                        graph_view.lines[this.to_line_index].to_index = element_index;
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
            remove_connection (id) {
                let self = this;
                let element_index = graph_view.elements.findIndex(element => element.id == id && (element.component_id == 0 || element.component_id == 1 || element.component_id == 4));
                if (id == this.$props.data.from) {
                    let point_index = this.create_point(true, window.graph_elements[element_index].from_position);
                    this.connect_to_line(true, point_index, false);
                }
                if (id == this.$props.data.to) { 
                    let point_index = this.create_point(false, window.graph_elements[element_index].to_position);
                    this.connect_to_line(false, point_index, false);
                }
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
                    window.graph_elements[enum_index].remove_connection(my_id);
                });

                // Remove relation connection from item
                this.get_connected_relations().forEach(rel_index => {
                    window.graph_elements[rel_index].remove_connection(my_id);
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
            }
        },
        computed: {
            from_position: function() {
                return {
                    x: this.$props.data.position.x + this.get_rect().width,
                    y: this.$props.data.position.y + this.get_rect().height / 2
                };
            },
            to_position: function() {
                return {
                    x: this.$props.data.position.x,
                    y: this.$props.data.position.y + this.get_rect().height / 2
                };
            }
        }
    }
</script>

<template>
    <div class="item" :class="{ is_selected , ghost_mode }" color="#8789ff"
        :style="transformation"
        @pointerdown.prevent="drag_start"
        @contextmenu.prevent="on_context"
        @pointerup.prevent="drag_end">
            <v-avatar left class="item_type darken-4 purple">R</v-avatar>
            <div style="display: inline;">{{ id }}</div>
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