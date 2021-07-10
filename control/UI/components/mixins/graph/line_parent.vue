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
        mixins: [APIShift.API.getMixin('orm/graph_element')],
        data() {
            return {
                lines: [],
                line_connector_map: {}, 
                im_a_line_parent: true,
            }
        },
        created () {
        },
        methods: {
            create_line (to_index, data, is_from = false, is_deleted = false) {
                let line_index = window.graph_view.lines.length;

                window.graph_view.lines.push({
                    from_index: is_from ? this.$props.index : to_index,
                    to_index: is_from ? to_index : this.$props.index,
                    data: { ...data, is_parent_left: is_from },
                    is_deleted
                });

                this.lines.push(window.graph_view.lines.length - 1);

                return window.graph_view.lines.length - 1;
            },
            create_point: function(is_left = true, position = null ,is_deleted = false) {
                let my_rect = this.get_rect;

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
                        parent_index: this.$props.index,
                        drag_end_func: this.on_point_drag_end,
                        is_left
                    },
                    is_deleted
                }

                graph_view.elements.push(
                    point
                );

                return graph_view.elements.length - 1;
            },
            on_point_drag_end (event, point_index) {
                let line_index = graph_view.lines.findIndex(line => (line.to_index == this.$props.index && line.from_index == point_index) || (line.from_index == this.$props.index && line.to_index == point_index));

                // Delete line if not presistent 
                if (!graph_view.lines[line_index].data.is_persistent) {
                    graph_view.$set(graph_view.lines[line_index], 'is_deleted', true);
                    graph_view.$set(graph_view.elements[point_index], 'is_deleted', true);
                }

                if (this.on_point_drag_end_addition) 
                    this.on_point_drag_end_addition(event, point_index);

            },
            on_line_click (event, line_index) {
                let graph_center_rect = graph_view.$el.querySelector('#graph_center').getBoundingClientRect();

                // Calculate mouse position
                let mouse_image = {
                    x:  (event.clientX - graph_center_rect.x) / graph_view.scale - 5,
                    y:  (event.clientY - graph_center_rect.y) / graph_view.scale - 5
                }

                // Find / Create point
                let is_parent_left = graph_view.lines[line_index].data.is_parent_left;
                let element_index = this.get_line_element()[line_index];
                let point_index = window.graph_elements[element_index].im_a_point ? element_index : this.create_point(!is_parent_left, mouse_image);

                // Assign point position and line ref
                setTimeout(() => {
                    Object.assign(graph_view.elements[point_index].data.position, mouse_image);
                    
                    graph_view.lines[line_index][is_parent_left ? 'to_index' : 'from_index'] = point_index;
                    graph_elements[point_index].drag_start(event);
                });
            },
            remove_connection (element_index) {
                // Step 1: Create points / Delete line
                let points_line = {};
                let line_element_map = this.get_line_element();
                Object.keys(line_element_map).forEach(line_index => {
                    // Ignore deleted lines or not connected ones to element
                    if (graph_view.lines[line_index].is_deleted || line_element_map[line_index] !== element_index) 
                        return;
                    
                    // Create a point on line edge
                    if(graph_view.lines[line_index].data.is_persistent) {
                        // Determine line edge position
                        let is_parent_left = graph_view.lines[line_index].data.is_parent_left;
                        let point_pos = window.graph_elements[element_index][is_parent_left ? 'to_position' : 'from_position'];
                        point_pos.x += graph_view.lines[line_index].data[`offsetLeft${is_parent_left ? 'Dest' : 'Src'}`] | 0;
                        point_pos.y += graph_view.lines[line_index].data[`offsetTop${is_parent_left ? 'Dest' : 'Src'}`] | 0;

                        // Create point
                        let point_index = this.create_point(!graph_view.lines[line_index].data.is_parent_left, point_pos);
                        points_line[point_index] = line_index;
                        return;
                    } 

                    // Line is not persistent - assign as deleted
                    graph_view.$set(graph_view.lines[line_index], 'is_deleted', true);
                });

                // Step 2: Link lines to points post renderation
                setTimeout(() => {
                    Object.keys(points_line).forEach((point_index) => {
                        let line_index = points_line[point_index];
                        graph_view.lines[line_index][graph_view.elements[point_index].data.is_left ? 'from_index' : 'to_index'] = point_index;
                    });
                });

                // Step 3: Execute additional procedures
                if (this.remove_connected_addition)
                    this.remove_connected_addition(element_index);
            }, 
            on_delete () {
                // Step 1: Remove line_parent connections
                setTimeout(() => {
                    let line_parents_indices = new Set();
                    graph_view.elements.forEach((element, index) => {
                        if (element.is_deleted || !window.graph_elements[index].im_a_line_parent || index == this.$props.index) return;

                        let line_element_map = window.graph_elements[index].get_line_element();
                        let line_index = Object.keys(line_element_map).find(line_index => {
                            return line_element_map[line_index] === this.$props.index;
                        });
                        if (line_index) window.graph_elements[index].remove_connection(this.$props.index);
                    });      
                });          

                // Step 2: Delete points & lines
                let line_element_map = this.get_line_element();
                Object.keys(line_element_map).forEach(line_index => {
                    let element_index = line_element_map[line_index];
                    if (window.graph_elements[element_index].im_a_point)
                        graph_view.$set(graph_view.elements[element_index], 'is_deleted', true);
                    graph_view.$set(graph_view.lines[line_index], 'is_deleted', true);
                });  

                // Step 4: Excecute additional procedures if set
                if (this.on_delete_addition) this.on_delete_addition();
    
                // Step 3: Mark element as deleted
                graph_view.$set(graph_view.elements[this.$props.index], 'is_deleted', true);  
            },
            get_line_element () {
                let get_line_element = {};
                this.lines.forEach(line_index => {
                    if(graph_view.lines[line_index].is_deleted) 
                        return;
                    get_line_element[parseInt(line_index)] = graph_view.lines[line_index].data.is_parent_left ? graph_view.lines[line_index].to_index : graph_view.lines[line_index].from_index;
                });
                return get_line_element;
            }
        },
    };
</script>