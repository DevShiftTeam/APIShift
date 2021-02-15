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
                },
                last_event: null,
                is_dragging: false,
                ghost_mode: false
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
                const self = this;

                graph_view.update_graph_position();

                // Get position when drag started
                window.init_position = Object.assign({} ,this.$props.position);

                // Update drag function
                graph_view.drag_handler = this.drag;
                graph_view.front_z_index++;
                this.$props.index = graph_view.front_z_index;
                this.last_event = event;
                this.is_dragging = false;

                // Start graph view scroll manager and pass handler 
                graph_view.scroll_manager.start(this.on_scroll, 20);

                // Call additional function if set
                this.expanded_functions.drag_start(event);
            },
            drag (event) {
                let dx = (event.clientX - this.last_event.clientX) / graph_view.scale;
                let dy = (event.clientY - this.last_event.clientY) / graph_view.scale;

                this.move_by(dx, dy);

                // Call additional function if set
                this.expanded_functions.drag(event);
                this.last_event = event;
                this.is_dragging = true;
            },
            drag_end (event) {
                graph_view.scroll_manager.stop();

                // Reset drag function
                graph_view.drag_handler = window.empty_function;
                this.is_dragging = false;

                // Call additional function if set
                this.expanded_functions.drag_end(event);
            },
            on_scroll () {
                if (!this.is_dragging) return;

                let mouse = { x: this.last_event.pageX - window.graph_position.x, y: this.last_event.pageY - window.graph_position.y };
                if (mouse.x < 20) {
                    this.move_by(-5 / graph_view.scale, 0);
                    graph_view.pan_by(5 , 0);
                }
                if (mouse.x > graph_view.init_rect.width - 20 ) {
                    this.move_by(5 / graph_view.scale , 0);
                    graph_view.pan_by(-5 , 0);
                }
                if (mouse.y < 20) {
                    this.move_by(0, -5 / graph_view.scale);
                    graph_view.pan_by(0, 5 );
                }
                if (mouse.y > graph_view.init_rect.height - 20 ) {
                    this.move_by(0, 5 / graph_view.scale);
                    graph_view.pan_by(0, -5 );
                }

                this.expanded_functions.drag(this.last_event);
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
                    'z-index': this.z_index + 5 // Base z-index for graph elements 
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