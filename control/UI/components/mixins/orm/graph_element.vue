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
                height: 0,
                width: 0,
                // This elements adds functionallity to drag events in needed
                expanded_functions: {
                    'drag_start': (event) => {},
                    'drag': (event) => {},
                    'drag_end': (event) => {}
                },
                group: null,
                last_event: null,
                is_dragging: false,
                ghost_mode: false
            }
        },
        created () {
            this.z_index = this.$props.index;
            graph_view.$refs[this.uid] = this;
        },
        mounted () {
            this.$el.ref = this.uid;
            this.rect.width  = this.$el.offsetWidth;
            this.rect.height = this.$el.offsetHeight;

            this.z_index = 5;
        },
        props: {
            name: String,
            rect: Object,
            data: Object,
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
                window.init_position = Object.assign({} ,this.$props.rect);

                // Delete element on delete state
                if (graph_view.cursor_state.type === 'delete') {
                    this.on_delete();
                    return;
                }

                // Update drag function
                graph_view.drag_handler = this.drag;
                graph_view.front_z_index++;
                this.$props.index = graph_view.front_z_index;
                this.last_event = event;
                this.is_dragging = false;

                // Call additional function if set
                this.expanded_functions.drag_start(event);


                // Start graph view scroll manager and pass handler 
                graph_view.scroll_manager.start(this.on_scroll, 20);
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
                    graph_view.move_camera_by(5 , 0);
                }
                if (mouse.x > graph_view.init_rect.width - 20 ) {
                    this.move_by(5 / graph_view.scale , 0);
                    graph_view.move_camera_by(-5 , 0);
                }
                if (mouse.y < 20) {
                    this.move_by(0, -5 / graph_view.scale);
                    graph_view.move_camera_by(0, 5 );
                }
                if (mouse.y > graph_view.init_rect.height - 20 ) {
                    this.move_by(0, 5 / graph_view.scale);
                    graph_view.move_camera_by(0, -5 );
                }

                this.expanded_functions.drag(this.last_event);
            },
            on_context (event) {
                event.preventDefault();
                console.log(event);
            },
            move_by (dx, dy) {
                this.$props.rect.x += dx;
                this.$props.rect.y += dy;
            },
            get_lines () {
                return graph_view.lines.filter((line) => (line.src_info.id === this.component_id & line.src_info.type === this.component_type ) 
                                                || (line.dest_info.id === this.component_id & line.dest_info.type === this.component_type ) );
            },
            get_group () {
                if (!this.group) {
                    let {type, id} = this.component_info;
                    graph_view.groups.forEach((group) =>  {
                        if (group.data.contained_elements.find((element) => element.id === id && element.type === type)) {
                            this.group = { id: group.id, type: group.type };
                        }
                    });
                }
                return this.group;
            },
            // Update lines explicitilly 
            update_lines () {
                this.get_lines().forEach(line => {
                    graph_view.$refs[line.line_uid].update();
                });
            }, 
            setIndex (index) {
                this.$props.index = index;
            },
            on_delete () {
                // Do Nothing
            }
        },
        computed: {
            // Rendered transformation (coordinates and scale) 
            transformation () {
                return  {
                    transform: `translate(${this.$props.rect.x}px,${this.$props.rect.y}px)`,
                    minWidth: `${this.$props.rect.width}px`,
                    minHeight: `${this.$props.rect.height}px`,
                    'z-index': this.z_index + 5 // Base z-index for graph elements 
                }
            },
            // Exspose position info conveniently for external usage 
            x_pos () {
                return this.$props.rect.x;
            },
            y_pos () {
                return this.$props.rect.y;
            },
            // Expose computed immutable local components-scope id 
            component_id () {
                return parseInt(this.uid.substring(1));
            },
            component_type () {
                return this.uid[0];
            },
            component_info () {
                return { id: this.component_id, type: this.component_type };
            }
        }
    };
</script>