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
     * @author Ilan Dazanashvili
     * @contributor Sapir Shemer
     */


    /**
     * This is the ORM Line component, 
     * Line component represents a continuous extent of length between p1 and p2 using SVG's Path element drawing function 
     * we draw line according to settings inferred by the connection and relation of the nodes
     */
    module.exports = {
        props: {
            uid: String,
            // Functional data
            src_info: Object,
            dest_info: Object,
            settings: Object,
        },
        data () {
            return {
                uid: '',
                // Elements objects (for reactivity)
                src_element: {},
                dest_element: {},
                // Line edges
                src_point: {x: 0, y: 0},
                dest_point: {x: 0, y: 0}
            }
        },
        created () {
            this.uid = `${this.$props.src_info.type + this.$props.src_info.id}-${this.$props.dest_info.type + this.$props.dest_info.id}`;
            this.src_element = graph_view.get_element_by_info(this.$props.src_info);
            this.dest_element = graph_view.get_element_by_info(this.$props.dest_info);
        },
        mounted () {
            this.$el.ref = this.uid;
            graph_view.$refs[this.uid] = this;
        },
        methods: {
            update() {  
                    let src_rect = this.src_element.rect;
                    let dest_rect = this.dest_element.rect;

                    if (this.$props.settings.enum_to_item) {
                        this.src_point = {
                            x: src_rect.x + src_rect.width / 2,
                            y: src_rect.y + src_rect.height / 2,
                        }
                        this.dest_point = {
                            x: dest_rect.x + dest_rect.width / 2,
                            y: dest_rect.y + dest_rect.height / 2,
                        }
                    }
                    else if (this.$props.settings.enum_to_group) {

                        // TODO: Redo it according to specifications & make it smarter using geometry
                        let top_line = { 
                            x1: dest_rect.x, y1: dest_rect.y,
                            x2: dest_rect.x + dest_rect.width, y2: dest_rect.y 
                        };
                        let right_line = { 
                            x1: dest_rect.x + dest_rect.width, y1: dest_rect.y ,
                            x2: dest_rect.x + dest_rect.width, y2: dest_rect.y + dest_rect.height 
                        };
                        let bottom_line = { 
                            x1: dest_rect.x + dest_rect.width, y1: dest_rect.y + dest_rect.height,
                            x2: dest_rect.x, y2: dest_rect.y + dest_rect.height 
                        };
                        let left_line = { 
                            x1: dest_rect.x, y1: dest_rect.y + dest_rect.height,
                            x2: dest_rect.x, y2: dest_rect.y 
                        };

                        let connector_line = {
                            x1: src_rect.x + src_rect.width / 2, y1: src_rect.y + src_rect.height / 2,
                            x2: dest_rect.x + dest_rect.width / 2, y2: dest_rect.y + dest_rect.height / 2 
                        }

                        /**
                        * Calculate intersection point of 2 line segments. Line segments are represented
                        **/
                        function get_intersection (line1, line2) {
                            // if the lines intersect, the result contains the x and y of the intersection (treating the lines as infinite) and booleans for whether line segment 1 or line segment 2 contain the point
                            var denominator, a, b, numerator1, numerator2, result = {
                                x: null,
                                y: null
                            };
                            denominator = ((line2.y2 - line2.y1) * (line1.x2 - line1.x1)) - ((line2.x2- line2.x1) * (line1.y2 - line1.y1));

                            // The lines might be parrallel or collinear
                            if (denominator == 0) {
                                return false;
                            }

                            a = line1.y1 - line2.y1;
                            b = line1.x1 - line2.x1;
                            numerator1 = ((line2.x2 - line2.x1) * a) - ((line2.y2 - line2.y1) * b);
                            numerator2 = ((line1.x2 - line1.x1) * a) - ((line1.y2 - line1.y1) * b);
                            a = numerator1 / denominator;
                            b = numerator2 / denominator;

                            // if we cast these lines infinitely in both directions, they intersect here:
                            result.x = line1.x1 + (a * (line1.x2 - line1.x1));
                            result.y = line1.y1 + (a * (line1.y2 - line1.y1));

                            // If intersection point is on line segments 
                            if ((a > 0 && a < 1) && (b > 0 && b < 1)) {
                                return result
                             } else return false;
                        }

                        let intersections = [   get_intersection(connector_line, right_line), 
                                                get_intersection(connector_line, top_line), 
                                                get_intersection(connector_line, bottom_line), 
                                                get_intersection(connector_line, left_line) ];
                        var point = intersections.find((p) => p);

                        // Src point from enum's middle
                        this.src_point = {
                            x: connector_line.x1,
                            y: connector_line.y1,
                        }

                        // Handle Enum inside group
                        if (!point) {
                            if (connector_line.y1 <= connector_line.y2) {
                                this.dest_point = {
                                    x: (top_line.x1 + top_line.x2) / 2,
                                    y: top_line.y1
                                }                               
                            } else  {
                                this.dest_point = {
                                    x: (bottom_line.x1 + bottom_line.x2) / 2,
                                    y: bottom_line.y1
                                } 
                            }
                        } else {
                            this.dest_point = {
                                x: point.x,
                                y: point.y,
                            }
                        }
                    }
                    else if (this.$props.settings.item_to_relation || this.$props.settings.relation_to_item) {
                        this.src_point = {
                            x: src_rect.x + src_rect.width,
                            y: src_rect.y + src_rect.height / 2,
                        }
                        this.dest_point = {
                            x: dest_rect.x ,
                            y: dest_rect.y + dest_rect.height / 2,
                        }
                    }
            },
            enum_to_item() {
                this.src_rect
            },
            enum_to_group () {

            },
            item_to_relation () {

            },
            pointer_down() {
            }
        },
        watch: {
            'src_element.rect': {
                handler () {
                    this.update();
                },
                deep: true
            },
            'dest_element.rect': {
                handler () {
                    this.update();
                },
                deep: true
            }
        },
        computed: {
            // Just for testing 
            path_data () {
                const bezierWeight = 0.675; // Amount to offset control points
                
                const dx           =  Math.abs(this.src_point.x - this.dest_point.x) * bezierWeight * !(this.$props.settings.enum_to_item || this.$props.settings.enum_to_group) ;
                const c1           = { x: this.src_point.x + dx, y: this.src_point.y };
                const c2           = { x: this.dest_point.x - dx, y: this.dest_point.y };

                return `M ${this.src_point.x} ${this.src_point.y} C ${c1.x} ${c1.y} ${c2.x} ${c2.y} ${this.dest_point.x} ${this.dest_point.y}`;
            },
        }
    }
</script>

<template>
        <g>
        <path @pointerdown="pointer_down" 
            :style="{ 'stroke-width': `${settings.enum_to_item ? 4 : 4}`,
                        'stroke-dasharray': `${settings.enum_to_item || settings.enum_to_group ? '11,5' : 'none'}`}"
            :d="path_data"
            >
        </path>
        <!-- <polygon points="0 0, 10 3.5, 0 7" /> -->
        </g>
</template>

<style scoped>
/* Please style this crap, with style */    
    path {
        fill: none;
        stroke: dodgerblue;
        stroke-width: 6;
        cursor: pointer;
    }
    
</style>