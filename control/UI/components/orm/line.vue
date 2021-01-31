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
     */


    /**
     * This is the ORM Line component, 
     * Line component represents a continuous extent of length between p1 and p2 using SVG's Path element drawing function 
     * we draw line according to settings inferred by the connection and relation of the nodes
     */
    module.exports = {
        props: {
            // Functional data
            from_index: Number,
            to_index: Number,
            settings: Object, 
            // View data 
            camera: Object,
            scale: Number,
        },
        data () {
            return {
                uid: '',
                p1x: 0,
                p1y: 0,
                p2x: 0,
                p2y: 0,
            }
        },
        created () {
            this.uid = `${this.$props.from_index}c${this.$props.to_index}`;
        },
        mounted () {
            this.$el.ref = this.uid;
            graph_view.$refs[this.uid] = this;
            // this.$el.querySelector('path') ? this.querySelector('path').ref = this.uid : null;
            console.log(`New ${this.uid} line from ${this.$props.from_index} to ${this.$props.to_index}`);
        
            

            this.update();
        },
        methods: {
            update() {  
                    const self = this;
                        const src_item  = graph_view.$refs[self.$props.from_index];
                        const dest_item = graph_view.$refs[self.$props.to_index];

                        // Positon line edges on the leftmost-uppermost corner of the elements
                        self.p1x = src_item.left  +  self.$props.camera.x   + (1-self.$props.scale)*src_item.$el.offsetWidth   / 2;
                        self.p1y = src_item.top   +  self.$props.camera.y   + (1-self.$props.scale)*src_item.$el.offsetHeight  / 2;
                        self.p2x = dest_item.left +  self.$props.camera.x   + (1-self.$props.scale)*src_item.$el.offsetWidth   / 2;
                        self.p2y = dest_item.top  +  self.$props.camera.y   + (1-self.$props.scale)*dest_item.$el.offsetHeight / 2;

                        // Position line edges properly according to line settings 
                        if (self.$props.settings.item_to_enum) {
                            
                        }
                        if (self.$props.settings.item_to_relation || self.$props.settings.relation_to_item) {
                            self.p1x += (src_item.$el.offsetWidth - 5) * self.$props.scale    ;
                            self.p2x += 1                              * self.$props.scale    ;
                            self.p1y += src_item.$el.offsetHeight      * self.$props.scale / 2;
                            self.p2y += src_item.$el.offsetHeight      * self.$props.scale / 2;                                       
                        }
            },
            pointer_down() {
                console.log('ipoasd');
            }
        },
        watch: {
            scale: function() {
                this.update();
            },
            camera: {
                handler: function () {
                    this.update();
                },
                deep: true
            }
        },
        computed: {
            // Just for testing 
            path_data () {
                const bezierWeight = 0.675; // Amount to offset control points
                const dx           =  Math.abs(this.p1x - this.p2x) * bezierWeight * !this.$props.settings.item_to_enum;
                const c1           = { x: this.p1x + dx, y: this.p1y };
                const c2           = { x: this.p2x - dx, y: this.p2y };

                return `M ${this.p1x} ${this.p1y} C ${c1.x} ${c1.y} ${c2.x} ${c2.y} ${this.p2x} ${this.p2y}`;
            },
        }
    }
</script>

<template>
    <!-- <svg style="position:absolute; width:100%; height:100%" :style="svg_transform" @pointerdown="pointer_down"> -->
        <g>
        <path @pointerdown="pointer_down" :style="{ 'stroke-width': `${5*scale}`}"
            :d="path_data"
            >
        </path>
        <polygon id="relation" points="0 0, 10 3.5, 0 7" /> 
        </g>
        
        <!-- <polygon points="0 0, 10 3.5, 0 7" /> -->
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