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
     * @author Ilan Dazanashvili
     */


    module.exports = {
        data() {
            return {
                type: 'graph_element',
                index: 0,
                lines: [],
                // This elements adds functionallity to drag events in needed
                expanded_functions: {
                    'drag_start': (event) => {},
                    'drag': (event) => {},
                    'drag_end': (event) => {}
                }
            }
        },
        mounted () {
            this.$el.type = 'graph_element';
            this.$el.ref = this.$props.name;
            graph_view.$refs[this.$props.index] = this;
        },
        props: {
            name: String,
            scale: Number,
            position: Object,
            // The point from which the scale is happening
            relative: Object,
            // Index - used for smart rendering
            index: Number,
            // comp_id - Unique ID for on-screen components
        },
        methods: {
            drag_start (event) {
                // Get position when drag started
                window.init_position = Object.assign({} ,this.$props.position);

                // Update drag function
                graph_view.drag_handler = this.drag;
                graph_view.front_z_index++;
                this.$props.index = graph_view.front_z_index;

                // Call additional function if set
                this.expanded_functions.drag_start(event);
            },
            drag (event, offset = { x: 0, y: 0}) {
                this.$props.position.x = window.init_position.x + event.clientX - graph_view.init_pointer.x + offset.x;
                this.$props.position.y = window.init_position.y + event.clientY - graph_view.init_pointer.y + offset.y;

                // Call additional function if set
                this.expanded_functions.drag(event);

                this.update_lines();
            },
            drag_end (event) {
                // Reset drag function
                graph_view.drag_handler = window.empty_function;

                // Call additional function if set
                this.expanded_functions.drag_end(event);
            },
            update_lines () {
                this.lines.forEach(line_uid => {
                    let line_instance = graph_view.$refs[line_uid];
                    line_instance.update();
                });
            },
            add_line (line_uid) {
                this.lines.push(line_uid);
            },
            remove_line (line_uid) {
                const index = this.lines.indexOf(line_uid);
                if (index > -1) {
                    this.lines.splice(index, 1);
                }
            },
            // Helper functions to move elements explicitly 
            move_by (vec = { x: 0, y: 0 }) {
                this.$props.position.x += vec.x;
                this.$props.position.y += vec.y;
            },
            move_to (vert = { x: 0, y: 0 }) {
                this.$props.position.x = vert.x;
                this.$props.position.y = vert.y;
            }
        },
        watch: {
            scale: function (newScale, oldScale) {
                let ds = newScale / oldScale;
                this.$props.position.x = this.$props.position.x*ds + this.$props.relative.x*(1-ds);
                this.$props.position.y = this.$props.position.y*ds + this.$props.relative.y*(1-ds);
            }
        },
        computed: {
            // Rendered transformation (coordinates and scale) 
            transformation () {
                return  {
                    transform: `translate(${this.$props.position.x}px,${this.$props.position.y}px) scale(${this.$props.scale})`,
                    
                    'z-index': this.$props.index + 5
                }
            },
            // Exspose position info conveniently for external usage 
            x_pos () {
                return this.$props.position.x;
            },
            y_pos () {
                return this.$props.position.x;
            }
        }
    };
</script>