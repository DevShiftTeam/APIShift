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
     */

    module.exports = {
        data() {
            return {
                // Original position
                origin_x: 0,
                origin_y: 0,
                // Mouse and start position data while dragging
                drag_data: null
            }
        },
        props: {
            scale: Number,
            // The point from which the scale is happening
            relative: Object
        },
        methods: {
            move (x, y) {
                this.origin_x = x > 0 ? x : 0;
                this.origin_y = y > 0 ? y : 0;
            },
            drag_start (event) {
                this.drag_data = {
                    mouse_x: event.clientX,
                    mouse_y: event.clientY,
                    start_x: this.origin_x,
                    start_y: this.origin_y
                }
            },
            drag (event) {
                event.preventDefault();
                this.move(
                    this.drag_data.start_x + (event.clientX - this.drag_data.mouse_x),
                    this.drag_data.start_y + (event.clientY - this.drag_data.mouse_y)
                    );
            }
        },
        computed: {
            x () {
                return this.origin_x * this.$props.scale + this.$props.relative.x * (1 - this.$props.scale);
            },
            y () {
                return this.origin_y * this.$props.scale + this.$props.relative.y * (1 - this.$props.scale);
            }
        }
    };
</script>