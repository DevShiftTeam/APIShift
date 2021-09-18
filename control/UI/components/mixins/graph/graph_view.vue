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

    window.empty_function = (event) => {};

    // This shit is made for scripting
    module.exports = {
        data () {
            return {
                drawer: null,
                // Components
                mixins: {},
                dialog: 0,
                dialog_open: false,
                dialog_data: {},
                in_edit: 0,
                contextmenu: {
                    is_active: false,
                    position: {
                        
                    },
                    actions: [],
                },
                elements: [
                ],
                lines: [

                ],
                relative: {
                    x: 0,
                    y: 0
                },
                // Index of the most frontal element
                front_z_index: 0,
                scale: 1,
                /* Drag & Drop functional data */
                tap_counter: 0,
                // Holds the function which renders the dragging event
                drag_handler: window.empty_function,
                // Defines the camera origin relative to the initial 0,0 position
                camera: {
                    x: 0,
                    y: 0
                },
                init_camera: {
                    x: 0,
                    y: 0
                },
                drag_end_lock: false,
                selection_active: false,
                side_menu_actions: [],
                context_menu: {
                    actions: [],
                    position: { x: 0, y: 0 },
                    is_active: false
                },
                scroll_manager: {
                    id: null,
                    interval: 20,
                    params: [],
                    cb: window.empty_function,
                    position_function(time) {
                        return 
                    },
                    start: function(cb, interval) {
                        if (cb) this.cb = cb;
                        if (interval) this.iv = interval;
                        this.id = window.setInterval(this.cb, this.interval);
                    },
                    stop: function() {
                        clearInterval(this.id);
                        this.id = null;
                    },
                    is_running: function () {
                        return !!this.id; 
                    }
                },
                cursor_state: "auto",
                elements_loaded: 0,
                first_load: false,
                action_selected: false,
                current_action: window.empty_function,
            }
        },
        created () {
            // Store this object with a global reference
            window.graph_elements = {};
            window.graph_lines = {};
            window.graph_view = this;
            this.current_action = window.empty_function;

            // Set initial z-index
            for(var index in this.elements) this.$set(this.elements[index].data, 'z_index', parseInt(index) + 1);
        },
        mounted () {
            this.update_graph_position();
        },
        methods: {
            bring_to_front: function(element_index) {
                // Ignore if in front
                if(this.elements[element_index].data.z_index !== undefined && this.elements[element_index].data.z_index == this.elements.length) return;

                let current_z_index = this.elements[element_index].data.z_index;

                // Bring other elements to back
                for(let index in this.elements)
                    if(this.elements[index].data.z_index > current_z_index)
                        this.elements[index].data.z_index--;
                

                // Bring to front
                this.elements[element_index].data.z_index = this.elements.length;
                app.$refs.navigator.updateIndex(this.elements.length + 1);
                app.$refs.footer.updateIndex(this.elements.length + 1);
                window.holder.updateIndex(this.elements.length + 1);
            },
            /**
             * User interactions
             */
            pointer_down(event) {
                document.addEventListener('pointerup', this.pointer_up);

                // Add event to event cache, determine interactive target
                this.tap_counter++;
                
                // Update graph position
                this.update_graph_position();

                // Handle mobile zooming
                if(this.tap_counter === 2) {
                    window.scale_factor = 1;
                    window.scale_init = this.scale;
                    window.init_pointer_first = Object.assign({}, window.init_pointer);
                    window.init_pointer_second = {
                        x: event.clientX,
                        y: event.clientY
                    };
                    window.temp_pointer = {
                        x: (window.init_pointer_first.x + window.init_pointer_second.x) / 2 - window.graph_position.x,
                        y: (window.init_pointer_first.y + window.init_pointer_second.y) / 2 - window.graph_position.y
                    };

                    this.drag_handler = this.pointer_scale;
                    return;
                }

                // Get initiale pointer coordinates
                window.init_pointer = {
                    x: event.clientX,
                    y: event.clientY
                };
                this.init_camera = Object.assign({}, this.camera);

                if(this.current_action != window.empty_function) {
                    // Determine pointer position in respect to graph transformation
                    let camera_rect = document.querySelector('#graph_center').getBoundingClientRect();
                    
                    window.mouse_on_graph = { x: (window.init_pointer.x - camera_rect.x) / (this.scale), y: (window.init_pointer.y - camera_rect.y) / (this.scale)};
                    this.current_action(event);
                    return;
                }

                // Proceeds only if not dragging any other object
                if(this.drag_handler != window.empty_function) return;

                this.drag_handler = this.pointer_move;
            },
            pointer_move(event) {
                this.camera.x = this.init_camera.x + event.clientX - window.init_pointer.x;
                this.camera.y = this.init_camera.y + event.clientY - window.init_pointer.y;
            },
            pointer_scale (event) {
                // De-activate context menu if present
                this.context_menu.is_active = false;

                this.update_graph_position();
                let rect = document.getElementById('graph_center').getBoundingClientRect();
                // Calculate center
                window.init_pointer.x = (window.init_pointer_first.x + window.init_pointer_second.x) / 2 - window.graph_position.x;
                window.init_pointer.y = (window.init_pointer_first.y + window.init_pointer_second.y) / 2 - window.graph_position.y;
                // Middle of graph camera
                let mid = {
                    x: rect.x + rect.width / 2 - window.graph_position.x,
                    y: rect.y + rect.height / 2 - window.graph_position.y
                };
                // Calculate previous distance vector
                let prev_diff = {
                    x: window.init_pointer_first.x - window.init_pointer_second.x,
                    y: window.init_pointer_first.y - window.init_pointer_second.y
                };

                // Calculate new point that moved
                if(Math.abs(window.init_pointer_first.y - event.clientY) + Math.abs(window.init_pointer_first.x - event.clientX)
                    < Math.abs(window.init_pointer_second.y - event.clientY) + Math.abs(window.init_pointer_second.x - event.clientX)) {
                    window.init_pointer_first.x = event.clientX;
                    window.init_pointer_first.y = event.clientY;
                }
                else {
                    window.init_pointer_second.x = event.clientX;
                    window.init_pointer_second.y = event.clientY;
                }

                // Calculate new distance vector using new pointer
                let new_diff = {
                    x: window.init_pointer_first.x - window.init_pointer_second.x,
                    y: window.init_pointer_first.y - window.init_pointer_second.y
                };

                // Update scale
                let change = Math.sqrt(
                        (new_diff.x * new_diff.x + new_diff.y * new_diff.y) /
                        (prev_diff.x * prev_diff.x + prev_diff.y * prev_diff.y)
                    );
                window.scale_factor *= change;
                let new_scale = window.scale_init * window.scale_factor;
        
                // Keep the scale on bound
                if (new_scale < 0.2 || new_scale > 4 ) {
                    return;
                }
                this.scale = new_scale;

                // Move Camera
                this.camera.x += (window.init_pointer.x - mid.x) * (1 - change) + window.init_pointer.x - window.temp_pointer.x;
                this.camera.y += (window.init_pointer.y - mid.y) * (1 - change) + window.init_pointer.y - window.temp_pointer.y;
                window.temp_pointer = Object.assign({}, window.init_pointer);
            },
            pointer_up(event) {
                this.tap_counter = 0;
                
                // Release drag end lock
                this.drag_end_lock = false;

                // Empty current action
                this.current_action = window.empty_function;

                // Reset drag event to none
                this.drag_handler = window.empty_function;

                this.scroll_manager.stop();
                graph_view.cursor_state = "auto";
            },
            wheel (event) {
                // De-activate context menu if present
                this.context_menu.is_active = false;

                // Update graph position
                this.update_graph_position();
                let rect = document.getElementById('graph_center').getBoundingClientRect();
                window.init_pointer = {
                    x: event.clientX - window.graph_position.x,
                    y: event.clientY - window.graph_position.y
                }

                // Middle of graph camera
                let mid = {
                    x: rect.x + rect.width / 2 - window.graph_position.x,
                    y: rect.y + rect.height / 2 - window.graph_position.y
                };

                // Calculate change in scale
                var delta = event.deltaMode > 0 ? event.deltaY * 100 : event.deltaY;
                var sign = Math.sign(delta), speed = 1;
                var deltaAdjustedSpeed = Math.min(0.25, Math.abs(speed * delta / 128));
                let change = (1 - sign * deltaAdjustedSpeed);
                let new_scale = this.scale * change;
                
                // Keep the scale on bound
                if (new_scale < 0.2 || new_scale > 4 ) {
                    return;
                }
                this.scale = new_scale;
                
                // Move camera to fit mouse as scaling center
                this.camera.x += (window.init_pointer.x - mid.x) * (1 - change);
                this.camera.y += (window.init_pointer.y - mid.y) * (1 - change);
            },
            move_camera_by (dx, dy) {
                this.camera.x += dx;
                this.camera.y += dy;
            },
            // Update graph position
            update_graph_position() {
                let rect = document.getElementById('graph_view').getBoundingClientRect();
                // Stores the current position of the graph on the screen
                window.graph_position = {
                    x: rect.x,
                    y: rect.y,
                    width: rect.width,
                    height: rect.height
                };
            },
            /**
             * Test whether 2 graph elements hit each other on the graph.
             * @param {Object} size_object_1
             * @param {Object} size_object_2
             * @returns {Boolean} 
             */
            collision_check: function(size_object_1, size_object_2){
                return (
                    size_object_1.x < size_object_2.x + size_object_2.width &&
                    size_object_1.x > size_object_2.x - size_object_1.width &&
                    size_object_1.y < size_object_2.y + size_object_2.height &&
                    size_object_1.y > size_object_2.y - size_object_1.height
                );
            },
            on_save() {

            }
        },
        watch: {
            elements_loaded: function(val) {
                if(val == this.elements.length && !this.first_load)
                {
                    for(let index in window.graph_elements)
                        if(window.graph_elements[index].all_loaded !== undefined) window.graph_elements[index].all_loaded();
                    this.first_load = true;
                }
            }
        }
    }
</script>
