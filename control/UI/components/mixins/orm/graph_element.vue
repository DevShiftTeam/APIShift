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
                // This elements adds functionallity to drag events in needed
                expanded_functions: {
                    'drag_start': (event) => {},
                    'drag': (event) => {},
                    'drag_end': (event) => {}
                },
                is_dragging: false,
                ghost_mode: false,
                ui_refresher: 0,
                init_position: { x: 0, y: 0},
                is_edit_mode: false
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

                // Delete element on delete state
                if (graph_view.cursor_state.type == "delete") return;

                // Bring to front
                graph_view.bring_to_front(this.$props.index);

                // Update drag function
                graph_view.drag_handler = this.drag;
                this.is_dragging = true;

                // Call additional function if set
                this.expanded_functions.drag_start(event);
            },
            drag (event) {
                this.$props.data.position = {
                    x: this.init_position.x + ((event.clientX - window.init_pointer.x) / graph_view.scale),
                    y: this.init_position.y + ((event.clientY - window.init_pointer.y) / graph_view.scale)
                };

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
                graph_view.context_menu.target = this;
                graph_view.context_menu.position = {
                    x: event.clientX - graph_position.x,
                    y: event.clientY - graph_position.y,
                };
                graph_view.context_menu.is_active = true;

                this.on_context_addition ();
            },
            on_scroll () {
                if (!this.is_dragging) return;

                let mouse = { x: this.last_event.pageX - window.graph_position.x, y: this.last_event.pageY - window.graph_position.y };
                if (mouse.x < 20) {
                    this.move_by(-5 / graph_view.scale, 0);
                    graph_view.move_camera_by(5 , 0);
                }
                if (mouse.x > window.graph_position.width - 20 ) {
                    this.move_by(5 / graph_view.scale , 0);
                    graph_view.move_camera_by(-5 , 0);
                }
                if (mouse.y < 20) {
                    this.move_by(0, -5 / graph_view.scale);
                    graph_view.move_camera_by(0, 5 );
                }
                if (mouse.y > window.graph_position.height - 20 ) {
                    this.move_by(0, 5 / graph_view.scale);
                    graph_view.move_camera_by(0, -5 );
                }

                this.expanded_functions.drag(this.last_event);
            },
            move_by (dx, dy) {
                this.$props.rect.x += dx;
                this.$props.rect.y += dy;
            },
            on_input (event) {
                // Trim text
                event.target.textContent = event.target.textContent.replace(/^\s+|\s+$/g, '');

                // Refresh view dependencies
                this.ui_refresher++;
                setTimeout(() => {
                    if (this.group_index && this.group_index != -1) window.graph_elements[this.group_index].update_group_size();
                    if (this.parent_group_index && this.parent_group_index != -1) window.graph_elements[this.parent_group_index].update_group_size();
                });

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
            }
        }
    };
</script>