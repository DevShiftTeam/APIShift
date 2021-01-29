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


    // This shit is made for scripting
    module.exports = {
        props: {
            // Functional data
            from_index: Number,
            to_index: Number,
            options: Object,
            // View data 
            relative: Object,
            scale: Number,
        },
        data () {
            return {
                uid: '',
                srcp: { x: 0, y: 0},
                p1x: 0,
                p1y: 0,
                p2x: 0,
                p2y: 0,
                destp: { x: 0, y: 0},
                z: 0,
                drawer: null
            }
        },
        created () {
            this.uid = `${this.$props.from_index}c${this.$props.to_index}`;
        },
        mounted () {
            const graphview = this.$parent;
            this.$el.ref = this.uid;
            graphview.$refs[this.uid] = this;
            // this.$el.querySelector('path') ? this.querySelector('path').ref = this.uid : null;
            console.log(`New ${this.uid} line from ${this.$props.from_index} to ${this.$props.to_index}`);


            this.update();
        },
        methods: {
            update() {
                const self = this;
                const graphview = this.$parent;
                let src_item = graphview.$refs[this.$props.from_index];
                let dest_item = graphview.$refs[this.$props.to_index];

                // Update on animation frame rate
                requestAnimationFrame(() => {
                    self.p1x = src_item.left; self.p1y = src_item.top;
                    self.p2x = dest_item.left; self.p2y = dest_item.top;
                })
                // Object.assign(this.srcp, { x: src_item.x_pos, y: src_item.y_pos });
                // Object.assign(this.destp, { x: dest_item.x_pos, y: dest_item.y_pos });
                // this.update();
                // console.log(this.srcp);
                // this.z += 0.000000000001;
                // requestAnimationFrame(this.update);
            }
        },
        computed: {
            // Just for testing 
            path_data () {
                const bezierWeight = 0.675; // Amount to offset control points
                const dx           = Math.abs(this.p1x - this.p2x) * bezierWeight;
                const c1           = { x: this.p1x - dx, y: this.p1y };
                const c2           = { x: this.p2x + dx, y: this.p2y };

                console.log(this);
                console.log(`M ${this.p1x} ${this.p1y} C ${c1.x} ${c1.y} ${c2.x} ${c2.y} ${this.p1x} ${this.p2y}`);
                return `M ${this.p1x} ${this.p1y} C ${c1.x} ${c1.y} ${c2.x} ${c2.y} ${this.p2x} ${this.p2y}`;
            }
        }
    }
</script>

<template>
    <svg style="position:absolute; height:100%; width:100%;">
        <path
            :d="path_data"
            >
        </path>
    </svg>
</template>

<style scoped>
/* Please style this crap, with style */
    path {
        /* height: 100%;
        width: 100%; */
        fill: none;
        stroke: dodgerblue;
        stroke-width: 6;
    }
    path.hover {
        cursor: pointer;
    }
</style>