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


    // connectable element is an abstraction for an element that receives connections as input 
    module.exports = {
        mixins: [APIShift.API.getMixin('graph/line_parent', true)],
        data() {
            return {
                im_a_data_source: true,
                entries: [],
                entries_set: [],
                flows_set: [],
                output_line: {},
                input_line: {},
                outgoing_flows: [], // indices
                incoming_flows: []
            }
        },
        created () {
            this.expanded_functions.on_point_drag_end = this.on_point_drag_end;
        },
        methods: {
            on_connector_click (event, con) {
                let graph_center = graph_view.$el.querySelector('#graph_center').getBoundingClientRect();
                let connectors = this.$el.querySelectorAll(`.connector.output`);
                let point_index = this.create_point(false, { 
                        x: ( event.pageX -  graph_center.x) / graph_view.scale,
                        y: ( event.pageY - graph_center.y) / graph_view.scale
                    });

                // Center point
                graph_view.elements[point_index].data.position.x -= 10;
                graph_view.elements[point_index].data.position.y -= 10;

                setTimeout(() => {
                    this.create_line(point_index, {
                        is_curvy: true,
                        is_stroked: false,
                        offsetTopSrc: connectors[con].offsetTop + connectors[con].offsetHeight / 2,
                        is_interactive: true
                    }, true);
                    
                    this.drag_end(event); 
                    graph_elements[point_index].drag_start(event);
                });
            },
            add_entry () {
                console.log(this.entries);
                this.entries.push({});
            },
            create_lines () {
                for (let index = 0; index < this.data.entries.length; index++) {
                    const to = this.data.entries[index].to;
                     if (!to) continue;

                    let to_index = graph_view.elements.findIndex(el => el.id == to.id && el.component_id == to.component_id);
                    let line_props = {
                        offsetTopSrc: this.$el.querySelectorAll('.connector.output')[index].offsetTop + this.$el.querySelectorAll('.connector.output')[index].offsetHeight / 2,
                        offsetTopDest: window.graph_elements[to_index].$el.querySelectorAll('.connector.input')[to.con].offsetTop + window.graph_elements[to_index].$el.querySelectorAll('.connector.input')[to.con].offsetHeight / 2,
                        is_curvy: true,
                        is_stroked: false,
                    };

                    // Create line
                    this.create_line(to_index, line_props, true);
                }
            },
            on_point_drop (point_index) {
                console.log('asdasd');
            },
            on_point_drag_end (event, point_index) {
                let line_index = graph_view.lines.findIndex(line => line.from_index == this.$props.index && line.to_index == point_index);
                
                // Delete line & point
                graph_view.$set(graph_view.elements[point_index], 'is_deleted', true);
                graph_view.$set(graph_view.lines[line_index], 'is_deleted', true);
            }
        },
        computed: {

        }
    };
</script>