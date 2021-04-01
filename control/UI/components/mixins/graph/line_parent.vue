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
        mixins: [APIShift.API.getMixin('graph/graph_element')],
        data() {
            return {
                line_indices: [],
                point_indices: [],
            }
        },
        props: {
            
        },
        methods: {
            create_line (to_index, data, is_from = false) {
                window.graph_view.lines.push({
                    from_index: is_from ? this.$props.index : to_index,
                    to_index: is_from ? to_index : this.$props.index,
                    data: { ...data, is_parent_source: is_from }
                });
                this.line_indices.push(window.graph_view.lines.length - 1);

                return window.graph_view.lines.length - 1;
            },
            create_point: function(is_left = true, position = null, is_deleted = false) {
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
                        line_index: graph_view.lines.length - 1,
                        parent_index: this.$props.index,
                        is_left
                    },
                    is_deleted
                }

                graph_view.elements.push(
                    point
                );
                this.point_indices.push(graph_view.elements.length - 1);

                return graph_view.elements.length - 1;
            },
            replace_connected (current_index, replace_index) {
                // Replace current with points
                if (replace_index == -1) {
                    let point_indices = [];
                    for (const line_index in graph_view.lines) 
                        if ( !graph_view.lines[line_index].data.is_parent_source && graph_view.lines[line_index].from_index == current_index && graph_view.lines[line_index].to_index == this.$props.index || graph_view.lines[line_index].data.is_parent_source && graph_view.lines[line_index].from_index == this.$props.index && graph_view.lines[line_index].to_index == current_index){
                            point_indices.push(this.create_point(!graph_view.lines[line_index].data.is_parent_source, window.graph_elements[current_index][!graph_view.lines[line_index].data.is_parent_source ? 'from_position' : 'to_position']));
                    }
                    setTimeout(() => point_indices.forEach(point_index => this.replace_connected(current_index, point_index)));
                    return;
                } 

                // Replace from point to non-point & delete point
                if (graph_view.elements[current_index].component_id == 5 && graph_view.elements[replace_index].component_id != 5) {
                    let line_index = graph_view.elements[current_index].data.is_left ? graph_view.lines.findIndex(line => line.from_index == current_index && line.to_index == this.$props.index && !line.is_deleted)
                                    : graph_view.lines.findIndex(line => line.to_index == current_index && line.from_index == this.$props.index && !line.is_deleted);

                    graph_view.elements[current_index].data.is_left ? graph_view.lines[line_index].from_index = replace_index : graph_view.lines[line_index].to_index = replace_index;
                    graph_view.elements[current_index].is_deleted = true; 
                }
                // Replace from non-point to point
                if (graph_view.elements[current_index].component_id != 5 && graph_view.elements[replace_index].component_id == 5) {
                    let line_index = graph_view.elements[replace_index].data.is_left ? graph_view.lines.findIndex(line => line.from_index == current_index && line.to_index == this.$props.index && !line.is_deleted)
                                    : graph_view.lines.findIndex(line => line.to_index == current_index && line.from_index == this.$props.index && !line.is_deleted);

                    graph_view.elements[replace_index].data.is_left ? graph_view.lines[line_index].from_index = replace_index : graph_view.lines[line_index].to_index = replace_index;
                }
                // Replace from point to point - do nothing 



                // Call additional function if set
                if (this.replace_connected_expanded && replace_index !== -1) this.replace_connected_expanded(current_index, replace_index);
            },

            on_delete () {
                // Delete lines
                this.line_indices.forEach(line_index => {
                    graph_view.$set(graph_view.lines[line_index], 'is_deleted', true);
                });

                // Delete points
                this.point_indices.forEach(point_index => {
                    graph_view.$set(graph_view.elements[point_index], 'is_deleted', true);
                });

                // Call additional function if set
                if (this.on_delete_addition) this.on_delete_addition();
            }
        },
    };
</script>