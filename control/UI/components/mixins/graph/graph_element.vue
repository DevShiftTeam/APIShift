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
     * @contributor Ilan Dazanashvili
     */


    module.exports = window.graph_element = {
        data() {
            return {
                // This elements adds functionallity to drag events in needed
                expanded_functions: {
                    'drag_start': (event) => {},
                    'drag': (event) => {},
                    'drag_end': (event) => {},
                    'on_delete': () => {},
                    'on_context': () => {}
                },
                is_dragging: false,
                ghost_mode: false,
                init_position: { x: 0, y: 0},
                is_edit_mode: false,
                ui_refresher: 0,
                mouse_pos: {},
                container_index: -1
            }
        },
        props: {
            name: String,
            data: Object,
            index: Number,
            id: Number
        },
        methods: {
            drag_start (event) {
                graph_view.update_graph_position();

                // Get position when drag started
                this.init_position = Object.assign({} ,this.$props.data.position);
                window.init_pointer = {
                    x: event.clientX,
                    y: event.clientY
                };

                // Bring to front
                graph_view.bring_to_front(this.$props.index);

                // Update drag function
                graph_view.drag_handler = this.drag;

                // Activate scroll functionallity
                if(!graph_view.scroll_manager.is_running()) graph_view.scroll_manager.start(this.on_scroll, 20);

                // Call additional function if set
                this.expanded_functions.drag_start(event);
            },
            drag (event) {
                this.mouse_pos = { x: event.pageX - window.graph_position.x, y: event.pageY - window.graph_position.y};
                this.$props.data.position = {
                    x: this.init_position.x + ((event.clientX - window.init_pointer.x) / graph_view.scale),
                    y: this.init_position.y + ((event.clientY - window.init_pointer.y) / graph_view.scale)
                };
                this.is_dragging = true;

                // Call additional function if set
                this.expanded_functions.drag(event);
            },
            drag_end (event) {
                if (graph_view.drag_end_lock) return;

                graph_view.scroll_manager.stop();

                // Reset drag function
                graph_view.drag_handler = window.empty_function;
                this.is_dragging = false;

                // Call additional function if set
                this.expanded_functions.drag_end(event);
            },
            on_context (event) {
                // Step 1 - Set contextmenu position pre-renderation
                graph_view.contextmenu.position = {
                    x: event.clientX - graph_position.x,
                    y: event.clientY - graph_position.y,
                };

                // Step 2 - Render context menu
                graph_view.contextmenu.is_active = true;

                // Step 3 - excecute additional procedures
                this.expanded_functions.on_context ();
            },
            on_scroll () {
                if (!this.is_dragging) return;
                
                if (this.mouse_pos.x < 20) {
                    this.move_by(-5 / graph_view.scale , 0);
                    graph_view.move_camera_by(5 , 0);
                }
                if (this.mouse_pos.x > window.graph_position.width - 20 ) {
                    this.move_by(5 / graph_view.scale , 0);
                    graph_view.move_camera_by(-5 , 0);
                }
                if (this.mouse_pos.y < 20) {
                    this.move_by(0, -5 / graph_view.scale);
                    graph_view.move_camera_by(0, 5 );
                }
                if (this.mouse_pos.y > window.graph_position.height - 20 ) {
                    this.move_by(0, 5 / graph_view.scale);
                    graph_view.move_camera_by(0, -5 );
                }
            },
            move_by (dx, dy) {
                this.init_position.x += dx;
                this.init_position.y += dy;

                this.$props.data.position.x += dx;
                this.$props.data.position.y += dy;

                // Call additional function if set
                this.expanded_functions.drag(event);
            },
            on_input (event) {
                // Trim text
                event.target.textContent = event.target.textContent.replace(/^\s+|\s+$/g, '');

                // Refresh view dependencies
                this.ui_refresher++;
                
                // Blur on enter key press
                if (event.inputType === "insertParagraph") this.on_blur(event);

                // Call additional functionallity if set
                if (this.on_input_addition) this.on_input_addition();
            },
            on_blur (event) {
                // Disable edit
                this.is_edit_mode = false;

                // Render white space as default value
                if (event.target.innerText == '') event.target.innerText = ' ';

                // Change model value
                graph_view.elements[this.$props.index].name = event.target.textContent;
            },
            on_delete () {
                // Step 1: Find connected line_parents & remove connections.
                setTimeout(() => {
                    let line_parents_indices = new Set();
                    graph_view.elements.forEach((element, index) => {
                        if (element.is_deleted || !window.graph_elements[index].im_a_line_parent || index == this.$props.index) return;

                        let line_element_map = window.graph_elements[index].get_line_element();
                        let line_index = Object.keys(line_element_map).find(line_index => {
                            return line_element_map[line_index] === this.$props.index;
                        });

                        if (line_index != null) 
                            window.graph_elements[index].remove_connection(this.$props.index);
                    });      
                });    
                // Step 2: Mark element as deleted
                graph_view.$set(graph_view.elements[this.$props.index], 'is_deleted', true);    

                // Step 3: Excecute additional procedures if set
                this.expanded_functions.on_delete();
            },
            refresh_dependencies () {
                // Step 1: Update cached get_rect dependency
                this.ui_refresher++;

                // Step 2: Update owning group size
                setTimeout(() => {
                    if (this.container_index !== -1) 
                    {
                        window.graph_elements[this.container_index].update_indices();
                        window.graph_elements[this.container_index].update_size();
                    }
                });
            }
        },
        computed: {
            // Rendered transformation (coordinates and scale) 
            transformation () {
                return  {
                    transform: `translate(${this.$props.data.position.x}px,${this.$props.data.position.y}px)`,
                    'z-index': this.$props.data.z_index
                }
            },
            get_rect: function() {
                this.ui_refresher;
                return {
                    x: this.$props.data.position.x,
                    y: this.$props.data.position.y,
                    width: this.$el.offsetWidth,
                    height: this.$el.offsetHeight
                };
            }
        },
        watch: {
            is_edit_mode (newVal) {
                // Focus input on edit
                if (newVal) {
                    let input = this.$el.querySelector('[contenteditable]');
                    setTimeout(
                        () => {
                            input.focus();
                        }
                    );
                }
            },
        }
    };
</script>