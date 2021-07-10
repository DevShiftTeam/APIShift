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
    // Functional data
    src_ref: Object,
    dest_ref: Object,
    data: Object,
    index: Number
  },
  data() {
    return {
    }
  },
  created () {
    window.graph_lines[this.$props.index] = this;
  },
  methods: {
    pointer_down(event) {
      if(graph_view.drag_end_lock) return;

      let obj_holder = this.$props.data.is_parent_left ? this.src_ref : this.dest_ref;
      obj_holder.on_line_click(event, this.$props.index);
    },
  },
  computed: {
    // Just for testing
    path_data() {
        if (graph_view.lines[this.$props.index].is_deleted) return;
        
        let src_point = Object.assign({}, this.src_ref.from_position);
        src_point.x += !!this.$props.data.marker_start*5;

        let dest_point = Object.assign({}, this.dest_ref.to_position);
        dest_point.x -= !!this.$props.data.marker_end*5;

        const bezierWeight = 0.675; // Amount to offset control points

        const dx = Math.abs(src_point.x - dest_point.x ) * bezierWeight * this.$props.data.is_curvy;
        const c1 = { x: src_point.x + dx, y: src_point.y };
        const c2 = { x: dest_point.x - dx , y: dest_point.y };

        return `M ${src_point.x} ${src_point.y} C ${c1.x} ${c1.y} ${c2.x} ${c2.y} ${dest_point.x } ${dest_point.y}`;
    },
  }
};
</script>

<template>
    <svg :style="{ 'z-index': this.src_ref.data.z_index > this.dest_ref.data.z_index ? this.dest_ref.data.z_index - 1 : this.src_ref.data.z_index - 1}" style="position: absolute; overflow: visible; width: 1px; height: 1px;">
        <defs>
            <marker id="many-arrow-head" markerWidth="4" markerHeight="5" refX="5.5" refY="5" 
            viewBox="0 0 10 10" orient="auto-start-reverse">
                <path d="M 0 0 L 10 5 L 0 10 z" />
            </marker>
            <marker id="one-arrow-head" viewBox="0 0 55.752 55.752" refX="32" refY="27" markerWidth="4" markerHeight="4" orient="auto-start-reverse">
                <path d="M43.006,23.916c-0.28-0.282-0.59-0.52-0.912-0.727L20.485,1.581c-2.109-2.107-5.527-2.108-7.637,0.001   c-2.109,2.108-2.109,5.527,0,7.637l18.611,18.609L12.754,46.535c-2.11,2.107-2.11,5.527,0,7.637c1.055,1.053,2.436,1.58,3.817,1.58   s2.765-0.527,3.817-1.582l21.706-21.703c0.322-0.207,0.631-0.444,0.912-0.727c1.08-1.08,1.598-2.498,1.574-3.912   C44.605,26.413,44.086,24.993,43.006,23.916z"/>
            </marker>
        </defs> 
        <g>
            <path @pointerdown.prevent="pointer_down" 
                :style="{ 'stroke-dasharray': `${this.$props.data.is_stroked ? '11,5' : 'none'}`}"
                :d="path_data"
                :marker-end="$props.data.marker_end"
                :marker-start="$props.data.marker_start"
                >
            </path>
            
        </g>
    </svg>
</template>

<style scoped>
/* Please style this crap, with style */
g > path {
  fill: none;
  stroke: dodgerblue;
  stroke-width: 4;
  cursor: pointer;
}
path {
    fill: dodgerblue;
}
</style>