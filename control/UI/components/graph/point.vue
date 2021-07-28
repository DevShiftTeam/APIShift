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
     * @author Ilan Dazanasvhili
     */
    
    // This shit is made for scripting
    module.exports = {
        mixins: [APIShift.API.getMixin('graph/graph_element')],
        props: {
        },
        data () {
            return {
                drawer: null,
                im_a_point: true
            }
        },
        created () {
            window.graph_elements[this.$props.index] = this;

            // Bind parent drag end controller to function
            this.expanded_functions.drag_end = this.drag_end_additional;
        }, 
        mounted () {
            let rect = this.$el.getBoundingClientRect();
            this.element_sizes = {
                width: rect.width,
                height: rect.height
            };
            graph_view.elements_loaded++;
        },
        methods: {
            drag_end_additional (event) {
                window.graph_elements[this.$props.data.parent_index].on_point_drag_end(event, this.$props.index);
            },
            // Do nothing
            on_delete () {
                
            },
        },
        computed: {
            from_position: function() {
                return {
                    x: this.$props.data.position.x + this.get_rect.width,
                    y: this.$props.data.position.y + this.get_rect.height / 2
                };
            },
            to_position: function() {
                return {
                    x: this.$props.data.position.x,
                    y: this.$props.data.position.y + this.get_rect.height / 2
                };
            }
        }
    }
</script>

<template>
    <div class="point" color="#8789ff"
        :style="transformation" 
        @pointerdown.prevent="drag_start"
        @pointerup.prevent="drag_end"
        @pointercancel.prevent="drag_end">
    </div>
</template>

<style scoped>
/* Please style this crap, with style */

.point {
    /* border: solid black 1px; */
    height: 15px;
    width: 15px;
    border-radius: 10px;
    padding: 5px;
    display: inline-block;
    position: absolute;
    cursor: copy ;
    background: rgb(187, 187, 187);
    box-shadow: 50px 50px 50px rgba(255, 242, 94, 0); /* Removing weird trace on chrome */
}

</style>