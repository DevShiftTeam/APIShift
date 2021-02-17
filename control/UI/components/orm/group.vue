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

    //TODO: Design better z-index system.
    module.exports = {
        mixins: [APIShift.API.getMixin('orm/graph_element')],
        data () {
            return {
                uid: 'g',
                container_height: 0,
                container_width: 0,
                leftbound: Number.MAX_SAFE_INTEGER,
                topbound: Number.MAX_SAFE_INTEGER, 
                rightbound: -Number.MAX_SAFE_INTEGER,
                bottombound: -Number.MAX_SAFE_INTEGER,
                item_refs: []
            }
        },
        created () {
            const self = this;
            this.expanded_functions.drag_start = (event) => {
                this.z_index = 1;
                self.set_rect();
            };
            this.expanded_functions.drag = (event) => {
                for (const item_ref of this.item_refs) {
                    const item_instance = graph_view.$refs[item_ref];
                    var delta = { x: self.x_pos - self.leftbound, y: self.y_pos - self.topbound };
                    item_instance.move_by(delta.x, delta.y);
                }
                self.set_rect();
            };
            this.expanded_functions.drag_end = (event) => {

            };
        },
        mounted () {
            this.update_items();
            this.set_rect();
        },
        methods: {
            pointer_move (event) {
                const eventBuilder = new CustomEvent('pointermove', { 
                    clientX: event.clientX, 
                    clientY: event.clientY,
                    // bubbles: true,
                    cancelable: true
                });
                graph_view.$refs['gv_lines'].dispatchEvent(eventBuilder); 
            },
            update_items () {
                this.item_refs = [];
                for (const group_item of graph_view.group_items) {
                    if (group_item.group_id === this.component_id) {
                        this.item_refs.push('i'+group_item.item_id);
                        graph_view.$refs['i'+group_item.item_id].group_container = this;
                    }
                }
                if(this.item_refs.length <= 1) this.on_delete();
            }, 
            set_rect() {
                this.leftbound = Number.MAX_SAFE_INTEGER;
                this.topbound  = Number.MAX_SAFE_INTEGER;
                this.rightbound = -Number.MAX_SAFE_INTEGER;
                this.bottombound =  -Number.MAX_SAFE_INTEGER;
                for (const item_ref of this.item_refs) {
                    const item_instance  = graph_view.$refs[item_ref];
                    this.leftbound   =  Math.min(item_instance.x_pos, this.leftbound);
                    this.bottombound =  Math.max(item_instance.y_pos + item_instance.$el.offsetHeight , this.bottombound);
                    this.rightbound  =  Math.max(item_instance.x_pos + item_instance.$el.offsetWidth, this.rightbound);
                    this.topbound    =  Math.min(item_instance.y_pos, this.topbound);
                    item_instance.z_index = this.z_index + 1;
                }
                this.$props.position.x = this.leftbound;
                this.$props.position.y = this.topbound;
            },
            on_delete () {
                // Delete element from the graph
                let id = this.component_id;
                graph_view.group_items = graph_view.group_items.filter((group_item) => {
                    if (group_item.group_id !== id) {
                        return true;
                    } else {
                        graph_view.$refs['i'+group_item.item_id].group_container = null;
                    }
                    
                });
                graph_view.groups = graph_view.groups.filter((group) => group.id !== id);
            }
        }, 
        computed: {
            transformation () {
                return {
                    transform: `translate(${this.$props.position.x}px,${this.$props.position.y}px)`,
                    'z-index': 1
                }
            }
        }
    }
</script>

<template>
    <div class="group" color="#8789ff"
        :style="transformation"
        @pointerdown.prevent="drag_start"
        @pointermove.prevent="pointer_move"
        @pointerup.prevent="drag_end"
        >
        
        <div class="group_info"
        :style="{'min-width': `${Math.abs(leftbound-rightbound) + 1}px`}">

            <v-avatar left class="group_type darken-4 green">G</v-avatar>
            <div style="display: inline;">{{ name }}</div>
        </div>
        
        <div class="group_container" 
        :style="{'height': `${Math.abs(topbound-bottombound)}px`, 'width': `${Math.abs(leftbound-rightbound)}px`}">

        </div>
    </div>
</template>

<style scoped>
/* Please style this crap, with style */

.group_type {
    text-align: center;
    display: inline;
    padding-left: 7px;
    padding-right: 7px;
}

.group {
    border: solid white 1px;
    border-radius: 10px;
    display: flex;
    flex-direction: column-reverse;
    position: absolute;
    cursor: copy ;
    box-shadow: 50px 50px 50px rgba(255, 242, 94, 0); /* Removing weird trace on chrome */
}

.group_info {
    margin-top: 1px;
    background: #8789ff;
    border-bottom-left-radius: 9px;
    border-bottom-right-radius: 9px;
    padding-right: 5px;
    padding-left: 5px;
}
.group_container {
    pointer-events: none;
    opacity: 0;
}



.group.highlight {

}
</style>