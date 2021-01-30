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
        mixins: [APIShift.API.getMixin('orm/graph_element')],
        props: {
            relate_from: Number,
            relate_to: Number,
            relate_type: Number
        },
        data () {
            return {
                drawer: null,
            }
        },
        created () {
            const $this = this;
            // We use the type to differentiate between objects
            this.type = 'item';
            this.expanded_functions = { 
                drag_start: function(event) {
                    // console.log($this);
                }, 
                drag: function(event) {
                    // console.log('relation');
                },
                drag_end: function(event) {
                    // console.log('relation');
                }
            };
        }, 
        mounted () {
            const graphview = this.$parent;
            this.$el.ref = this.index; // TODO: change to comp_id $prop field, mistakenly identified it as a form of component index
            
            // Draw relation lines
            graphview.create_line(this.$props.relate_from, this.index, { item_to_relation: true, relate_type: this.$props.relate_type });
            graphview.create_line(this.index, this.$props.relate_to, { relation_to_item: true, relate_type: this.$props.relate_type });

            // console.log('Relate from ' + this.$props.relate_from);
            console.log('Relation has mounted');
        },
        methods: {
            render_needed () {
            }
        }
    }
</script>

<template>
    <div class="item" color="#8789ff"
        :style="transformation"
        @pointerdown.prevent="drag_start"
        @pointerup.prevent="drag_end">
            <v-avatar left class="item_type darken-4 purple">R</v-avatar>
            <div style="display: inline;">{{ name }}</div>
    </div>
</template>

<style scoped>
/* Please style this crap, with style */
.item_type {
    text-align: center;
    display: inline;
    padding-left: 7px;
    padding-right: 7px;
}

.item {
    border: solid white 1px;
    border-radius: 10px;
    padding: 5px;
    display: inline-block;
    position: absolute;
    cursor: copy !important;
    background: #8789ff;
}
</style>