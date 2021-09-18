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
                flows_set: [],
                output_line: {},
                input_line: {},
                outgoing_flows: [], // indices
                incoming_flows: []
            }
        },
        created () {
        },
        methods: {
            output_press(event, index) {
                let connectors = this.$el.querySelectorAll(`.output`);
                let offsetTop = connectors[index].offsetTop - connectors[0].offsetTop;
                let graph_pos = document.body.querySelector("#graph_center").getBoundingClientRect();
                let to_index = this.create_point(false, { 
                        x: ( connectors[index].getBoundingClientRect().x -  graph_pos.x) / graph_view.scale,
                        y: ( connectors[index].getBoundingClientRect().y - graph_pos.y) / graph_view.scale
                    }
                );

                setTimeout(() => {
                    this.create_line(to_index, {
                        is_curvy: true,
                        is_stroked: false,
                        offsetTopSrc: offsetTop,
                        is_interactive: true
                    }, true);
                    
                    // TODO: Improve mechanism - fundemmentally wrong
                    this.drag_end(event); 
                    graph_elements[to_index].drag_start(event);
                });
            },
            add_entry () {
                console.log(this.entries);
                this.entries.push({});
            }
        },
        computed: {

        }
    };
</script>