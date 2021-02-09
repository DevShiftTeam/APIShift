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
                uid: '',
                type: 'graph_element',
                z_index: 0,
                lines: [],
                // This elements adds functionallity to drag events in needed
                expanded_functions: {
                    'drag_start': (event) => {},
                    'drag': (event) => {},
                    'drag_end': (event) => {}
                }
            }
        },
        created () {
            this.z_index = this.$props.index;
        },
        mounted () {
            graph_view.$refs[this.uid] = this;
            this.$el.ref = this.uid;
            this.$el.type = 'graph_element';
        },
        props: {
            name: String,
            position: Object,
            // Index - used for smart rendering
            index: Number,
            // uid - used for global component reference
            uid: String,
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
            drag (event) {
                this.$props.position.x = window.init_position.x + (event.clientX - window.init_pointer.x) / graph_view.scale;
                this.$props.position.y = window.init_position.y + (event.clientY - window.init_pointer.y) / graph_view.scale;

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
            move_to (xpos, ypos) {
                this.$props.position.x = xpos;
                this.$props.position.y = ypos;
                this.update_lines();
            },
            move_by (dx, dy) {
                this.$props.position.x += dx;
                this.$props.position.y += dy;
                this.update_lines();
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
            setIndex (index) {
                this.$props.index = index;
            }
        },
        computed: {
            // Rendered transformation (coordinates and scale) 
            transformation () {
                return  {
                    transform: `translate(${this.$props.position.x}px,${this.$props.position.y}px)`,
                    'z-index': this.z_index + 5
                }
            },
            // Exspose position info conveniently for external usage 
            x_pos () {
                return this.$props.position.x;
            },
            y_pos () {
                return this.$props.position.y;
            },
            // Expose computed immutable local components-scope id 
            component_id () {
                return parseInt(this.uid.substring(1));
            }
        }
    };
</script>