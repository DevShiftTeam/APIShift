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
                position: {
                    x: 0,
                    y: 0
                },
                mouse_relative_position: {
                    x: 0,
                    y: 0
                },
                scale: 1,
                type: 'graph_element',
                lines: []
            }
        },
        mounted () {
            this.$el.type = 'graph_element';
            this.$el.ref = this.$props.name;
        },
        props: {
            name: String,
            scale: Number,
            // The point from which the scale is happening
            relative: Object,
            // Index - used for smart rendering
            index: Number
        },
        methods: {
            drag_start (event) {
                // Get mouse coordinates relative to element
                let item_rect = this.$el.getBoundingClientRect();
                this.mouse_relative_position.x = (event.clientX - item_rect.x) / this.$props.scale;
                this.mouse_relative_position.y = (event.clientY - item_rect.y) / this.$props.scale;

                // Update drag function
                graph_view.drag_handler = this.drag;
            },
            drag (event) {
                this.position.x = event.clientX - this.mouse_relative_position.x - graph_view.graph_position.x - graph_view.camera.x;
                this.position.y = event.clientY - this.mouse_relative_position.y - graph_view.graph_position.y - graph_view.camera.y;
            },
            drag_end (event) {
                // Reset drag function
                graph_view.drag_handler = window.empty_function;
            }
        },
        watch: {
            scale: function (newScale, oldScale) {
                let ds = newScale / oldScale;
                this.position.x = this.position.x*ds + this.$props.relative.x*(1-ds);
                this.position.y = this.position.y*ds + this.$props.relative.y*(1-ds);
            }
        },
        computed: {
            // Rendered transformation (coordinates and scale) 
            transformation () {
                return  {
                    transform: `translate(${this.position.x}px,${this.position.y}px) scale(${this.$props.scale})`
                }
            }
        }
    };
</script>