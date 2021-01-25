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
     * @author Sapir Shemer & Ilan Dazanashvili
     */


    module.exports = {
        data() {
            return {
                // Functional data 
                left: 0,
                top: 20,
                scale: 1,
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
            relative: Object
        },
        methods: {
            // Move by a specified vector on the plane 
            move_by: function(vmove) {
                this.left += vmove[0];
                this.top += vmove[1];
                this.updateLines();
            },
            // Move to a specified coordinate on the plane 
            move_to: function(coordinate) {
                this.left = coordinate[0];
                this.top = coordinate[1];
                this.updateLines();
            },
            updateLines: function() {
                
            }
        },
        watch: {
            scale: function (newScale, oldScale) {
                let ds = newScale / oldScale;
                let x = this.left*ds + this.$props.relative['x']*(1-ds);
                let y = this.top*ds + this.$props.relative['y']*(1-ds);
                this.move_to([x,y]);
            }
        },
        computed: {
            // Rendered transformation (coordinates and scale) 
            transformation () {
                return  {
                    // transform3d leverages GPU acceleration on some clients 
                    transform: `translate3d(${this.left}px,${this.top}px, 0) scale(${this.$props.scale})`
                }
            }
        }
    };
</script>