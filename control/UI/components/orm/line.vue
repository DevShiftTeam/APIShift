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
                src_rect: {
                    x: 0,
                    y: 0,
                    width: 0,
                    height: 0
                },
                dest_rect: {
                    x: 0,
                    y: 0,
                    width: 0,
                    height: 0
                }
            }
        },
        created () {
            this.uid = `${this.$props.from_uid}-${this.$props.to_uid}`;
        },
        mounted () {
            this.$el.ref = this.uid;
            graph_view.$refs[this.uid] = this;

            // Get coresponding source rect from graph_view by source info  
            if (this.$props.src_info.type === 'i') {
                this.src_rect = graph_view.items.find((item) => item.id === this.$props.src_info.id).rect
            }
            if (this.$props.src_info.type === 'e') {
                this.src_rect = graph_view.enums.find((item) => item.id === this.$props.src_info.id).rect
            }
            if (this.$props.src_info.type === 't') {
                this.src_rect = graph_view.enum_types.find((item) => item.id === this.$props.src_info.id).rect
            } 
            if (this.$props.src_info.type === 'g') {
                this.src_rect = graph_view.groups.find((item) => item.id === this.$props.src_info.id).rect
            } 

            // Get coresponding dest rect from graph_view by dest info  
            if (this.$props.dest_info.type === 'i') {
                this.dest_rect = graph_view.items.find((item) => item.id === this.$props.dest_info.id).rect
            }
            if (this.$props.dest_info.type === 'e') {
                this.dest_rect = graph_view.enums.find((item) => item.id === this.$props.dest_info.id).rect
            }
            if (this.$props.dest_info.type === 't') {
                this.dest_rect = graph_view.enum_types.find((item) => item.id === this.$props.dest_info.id).rect
            } 
            if (this.$props.dest_info.type === 'g') {
                this.dest_rect = graph_view.groups.find((item) => item.id === this.$props.dest_info.id).rect
            } 
        },
        methods: {
            update() {  
                    // const src_item  = graph_view.$refs[this.$props.from_uid];
                    // const dest_item = graph_view.$refs[this.$props.to_uid];

                    // // Positon line edges on the leftmost-uppermost corner of the elements
                    // this.from_position = Object.assign({}, src_item.rect);
                    // this.to_position = Object.assign({}, dest_item.rect);

                    // // Position line edges properly according to line settings 
                    // if (this.$props.settings.enum_to_item) {
                    //     this.from_position.x += src_item.$el.offsetWidth / 2;
                    //     this.from_position.y += src_item.$el.offsetHeight / 2;
                    //     this.to_position.x += dest_item.$el.offsetWidth / 2;                    
                    //     this.to_position.y += dest_item.$el.offsetHeight / 2; 
                    // }
                    // if (this.$props.settings.item_to_relation || this.$props.settings.relation_to_item) {
                    //     this.from_position.x += (src_item.$el.offsetWidth);
                    //     this.from_position.y += src_item.$el.offsetHeight / 2;
                    //     this.to_position.y += src_item.$el.offsetHeight / 2;
                    // }
            },
            pointer_down() {
            }
        },
        computed: {
            // Just for testing 
            path_data () {
                const bezierWeight = 0.675; // Amount to offset control points
                let src_offset = { x: 0, y: 0 }, dest_offset = { x: 0, y: 0 };
                
                // Position line edges properly according to line settings 
                if (this.$props.settings.enum_to_item) {
                    src_offset = { x: this.src_rect.width / 2, y: this.src_rect.height / 2 };
                    dest_offset = { x: this.dest_rect.width / 2, y: this.dest_rect.height / 2 };
                }
                if (this.$props.settings.item_to_relation || this.$props.settings.relation_to_item) {
                    src_offset = { x: this.src_rect.width, y: this.src_rect.height / 2 };
                    dest_offset = { x: 0, y: this.dest_rect.height / 2 };
                }
                
                let calc_from  = { x: this.src_rect.x + src_offset.x, y: this.src_rect.y + src_offset.y };
                let calc_to = { x: this.dest_rect.x + dest_offset.x, y: this.dest_rect.y + dest_offset.y };
                
                const dx           =  Math.abs(calc_from.x - calc_to.x) * bezierWeight * !this.$props.settings.enum_to_item;
                const c1           = { x: calc_from.x + dx, y: calc_from.y };
                const c2           = { x: calc_to.x - dx, y: calc_to.y };

                return `M ${calc_from.x} ${calc_from.y} C ${c1.x} ${c1.y} ${c2.x} ${c2.y} ${calc_to.x} ${calc_to.y}`;
            },
        }
    }
</script>

<template>
        <g>
        <path @pointerdown="pointer_down" 
            :style="{ 'stroke-width': `${settings.enum_to_item ? 4 : 4}`,
                        'stroke-dasharray': `${settings.enum_to_item ? '11,5' : 'none'}`}"
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