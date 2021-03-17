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
  },
  methods: {
    pointer_down(event) {
      // Find point
      let obj_holder = this.src_ref;

      if(obj_holder.im_a_point === undefined) {
          obj_holder = this.dest_ref;

          if(obj_holder.im_a_point === undefined) {
            let graph_center_rect = graph_view.$el.querySelector('#graph_center').getBoundingClientRect();

            // If a relation then create a point
            if(this.$props.data.is_rel_source !== undefined) {
              let rel_holder = this.data.is_rel_source ? this.src_ref : this.dest_ref;
              let point_index = rel_holder.create_point(this.data.is_rel_source, {
                x: (event.clientX - graph_center_rect.x) / graph_view.scale - 5,
                y: (event.clientY - graph_center_rect.y) / graph_view.scale - 5
              });


              // Scheduling task to the end of event-loop in order to prevent race conditions
              let self = this;
              setTimeout(() => {
                let point_ref = window.graph_elements[point_index];
                point_ref.data.position.x = (event.clientX - graph_center_rect.x) / graph_view.scale - point_ref.get_rect().width / 2;
                point_ref.data.position.y = (event.clientY - graph_center_rect.y) / graph_view.scale - point_ref.get_rect().height / 2;
                if(self.data.is_rel_source) {
                  rel_holder.data.to = undefined;
                  graph_view.lines[this.index].to_index = graph_view.elements.length - 1;
                }
                else {
                  rel_holder.data.from = undefined;
                  graph_view.lines[this.index].from_index = graph_view.elements.length - 1;
                }
                point_ref.drag_start(event);
              });
            }
            else {
              // Handle enum
            }

            return;
          }
      }

      let graph_center_rect = graph_view.$el.querySelector('#graph_center').getBoundingClientRect();

      obj_holder.data.position.x = (event.clientX - graph_center_rect.x) / graph_view.scale - obj_holder.get_rect().width / 2;
      obj_holder.data.position.y = (event.clientY - graph_center_rect.y) / graph_view.scale - obj_holder.get_rect().height / 2;

      obj_holder.drag_start(event); 
    },
  },
  computed: {
    // Just for testing
    path_data() {
        let src_point = this.src_ref.from_position;
        let dest_point = this.dest_ref.to_position;

        const bezierWeight = 0.675; // Amount to offset control points

        const dx = Math.abs(src_point.x - dest_point.x) * bezierWeight * this.$props.data.is_curvy;
        const c1 = { x: src_point.x + dx, y: src_point.y };
        const c2 = { x: dest_point.x - dx, y: dest_point.y };

        return `M ${src_point.x - 5} ${src_point.y} C ${c1.x} ${c1.y} ${c2.x} ${c2.y} ${dest_point.x} ${dest_point.y}`;
    },
  }
};
</script>

<template>
    <svg :style="{ 'z-index': this.src_ref.data.z_index > this.dest_ref.data.z_index ? this.dest_ref.data.z_index - 1 : this.src_ref.data.z_index - 1}" style="position: absolute; overflow: visible; width: 1px; height: 1px;">
        <defs>
            <marker id="black-arrow" markerWidth="5" markerHeight="5" refX="0" refY="5"
            viewBox="0 0 10 10" orient="auto-start-reverse" style="opacity: 0.85">
                <path d="M 0 0 L 10 5 L 0 10 z" />
            </marker>
            <marker id="arrow" markerWidth="10" markerHeight="10" refX="10" refY="3" orient="auto" markerUnits="strokeWidth">
                <path d="M0,0 L0,6 L9,3 z" fill="rgba(255,0,0,0.9)" />
            </marker>
            <marker id="arrow1" viewBox="0 0 492.004 492.004" markerWidth="5" markerHeight="5" orient="auto-start-reverse">
                <path d="M382.678,226.804L163.73,7.86C158.666,2.792,151.906,0,144.698,0s-13.968,2.792-19.032,7.86l-16.124,16.12
                c-10.492,10.504-10.492,27.576,0,38.064L293.398,245.9l-184.06,184.06c-5.064,5.068-7.86,11.824-7.86,19.028
                c0,7.212,2.796,13.968,7.86,19.04l16.124,16.116c5.068,5.068,11.824,7.86,19.032,7.86s13.968-2.792,19.032-7.86L382.678,265
                c5.076-5.084,7.864-11.872,7.848-19.088C390.542,238.668,387.754,231.884,382.678,226.804z"/>
            </marker>
        </defs> 
        <g>
            <path @pointerdown.prevent="pointer_down" 
                :style="{ 'stroke-dasharray': `${this.$props.data.is_stroked ? '11,5' : 'none'}`}"
                :d="path_data"
                >
            </path>
            <!-- <polygon points="0 0, 10 3.5, 0 7" /> -->
        </g>
    </svg>
</template>

<style scoped>
/* Please style this crap, with style */
path {
  fill: none;
  stroke: dodgerblue;
  stroke-width: 4;
  cursor: pointer;
}
</style>