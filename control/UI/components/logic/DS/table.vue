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
        mixins: [APIShift.API.getMixin('graph/logic/data_source')],
        data () {
            return {
                is_edit_mode: {
                    name: false,
                    entry: false
                },
                column: 'PASSWORD',
                column_where: 'USERNAME',
                entries: [['PASSWORD']],
                input: 'username',
                edit_name_width: 0,
                edit_entry_width: 0,
                header_min_width: 0,
                column_width: 0,
                input_width: 0,
                point_refs: [],
                active_connector_index: null,
                first_load: true,
                entries_counter: 0,
                offsetTop: 2,
            }
        },
        created () {
            window.graph_elements[this.$props.index] = this;


            this.entries_set = [
                'ID',
                'USERNAME',
                'PASSWORD',
                'CREATED_AT'
            ];

            this.flows_set = [
                'ID',
                'USERNAME',
                'PASSWORD',
                'CREATED_AT'
            ];
        }, 
        mounted () {
            this.expanded_functions.drag_start = this.drag_start_addition;
            this.expanded_functions.drag = this.drag_addition;
            this.expanded_functions.drag_end = this.drag_end_addition;
            graph_view.elements_loaded++;

            if(graph_view.first_load) {
                this.all_loaded();
                graph_view.bring_to_front(this.$props.index);
            }
        },
        methods: {
            all_loaded: function() {   
                let out_flows = [];

                graph_view.elements.forEach(element => {
                    if (element.component_id == 4 && (element.data.from.id == this.$props.index && element.from.component_id == graph_view.elements[this.$props.index].component_id))  
                        out_flows.push(element.name);
                });
            },
            drag_start_addition: function(event) {

            },
            drag_addition(event) {

            },
            drag_end_addition(event) {

            },
            on_delete() {
                let my_id = graph_view.elements[this.$props.index].id;

                // Reset enum sizes
                if (this.attached_enum != -1) {
                    window.graph_elements[this.attached_enum].data.types = window.graph_elements[this.attached_enum].data.types.filter(id => id !== my_id);
                    window.graph_elements[this.attached_enum].reset_enum_sizes();
                    window.graph_elements[this.attached_enum].reset_type_position();
                }

                // Removing element from screen
                graph_view.$set(graph_view.elements[this.$props.index], 'is_deleted', true);
            },
            on_context_addition () {
                graph_view.context_menu.actions = [
                    {
                        starter: () => {
                            this.is_edit_mode = true;
                            graph_view.context_menu.is_active = false;
                        },
                        name: 'Edit',
                        icon: 'mdi-pencil',
                    },
                    {
                        starter: () => {
                            graph_view.context_menu.is_active = false;
                        },
                        name: 'Duplicate',
                        icon: 'mdi-content-duplicate',
                    },
                    {
                        starter: () => {
                            this.on_delete();
                            graph_view.context_menu.is_active = false;
                        },
                        name: 'Delete',
                        icon: 'mdi-delete-outline',
                    },
                ]
            },
            on_point_drag_end (event, point_index) {                
                let line_index = graph_view.elements[point_index].data.line_index;
                let row_height = this.$el.querySelector('.row.demo-row').offsetHeight;
                let con_index = (graph_view.lines[line_index].data.offsetTopSrc | 0) / row_height;

                let target_element = -1, z_index = 0;

                for(let index in [...graph_view.elements.keys()]) {
                    let cmp_id = graph_view.elements[index].component_id;
                    // Skip non-connections
                    if(window.graph_elements[index] === undefined ||  (cmp_id != 2 && cmp_id != 3 && cmp_id != 4) || graph_view.elements[index].is_deleted)
                        continue;
                    
                    // Check collisions
                    if (window.graph_elements[index].data.z_index > z_index && graph_view.collision_check(graph_elements[point_index].get_rect, window.graph_elements[index].get_rect)) {
                        target_element = index;
                        z_index = graph_view.elements[index].data.z_index;
                    }
                }
                
                // Drop on a connectable element
                let res = window.graph_elements[target_element]?.on_point_drop(point_index);
                if (res) {                
                    this.$props.data.connections[con_index].id = graph_view.elements[target_element].id;
                    this.$props.data.connections[con_index].component_id = graph_view.elements[target_element].component_id;

                    graph_view.lines[line_index].to_index = target_element;
                } else 
                {
                    this.$props.data.connections[con_index].id = -1;
                    this.$props.data.connections[con_index].component_id = -1;
                    graph_view.$set(graph_view.lines[graph_view.elements[point_index].data.line_index], 'is_deleted', true);
                }

                // Delete point & line
                graph_view.elements[point_index].is_deleted = true;
            },
            refresh_inputs () {
            }
        },
        watch: {

        },
        computed: {
            from_position: function() {
                return {
                    x: this.$props.data.position.x + this.get_rect.width,
                    y: this.$props.data.position.y
                };
            },
            to_position: function() {
                return {
                    x: this.$props.data.position.x,
                    y: this.$props.data.position.y + 10
                };
            }
        },
    }
</script>

<template>
    <div class="table" color="#8789ff" :class="{  }"
        :style="transformation" 
        @pointerdown.prevent="drag_start"
        @contextmenu.prevent="on_context"
        @pointerup.prevent="drag_end">
        <!-- Header  -->
        <div id="header" :style="{minWidth: header_min_width + 'px'}"
                @input="on_input"
                @dblclick.prevent="is_edit_mode.name = true">
                <v-icon size="25" left class="avatar darken-4">mdi-database</v-icon>
                <v-text-field
                    v-model="name"
                    v-show="is_edit_mode.name"
                    :style="{'width': edit_name_width + 'px', marginLeft: 'auto', marginRight: 'auto'}"
                    @input="requestAnimationFrame(() => edit_name_width = $el.querySelector('#name').offsetWidth + 30)"
                    >
                </v-text-field>
                <b :style="{  marginLeft: 'auto', marginRight: 'auto', fontSize: '120%' }" v-show="!is_edit_mode.name">{{name}}</b>
                <span id="name" style="position: absolute; top: -120%; color: rgba(0,0,0,0);">{{name}}</span>
        </div>

        <!-- Content -->
        <div id="content"
            @dblclick.prevent="is_edit_mode.entry = true">
            <v-col style="padding-bottom: 0px; padding-top: 0px">
                <v-row  v-for="(entry, index) in entries" :key="index" class="demo-row no-border" style="display: flex">
                    <v-combobox
                        :v-model="entries[index]"
                        :value="entries[index]"
                        @input="setTimeout(() => ui_refresher++)"
                        :items="entries_set"
                        multiple
                    ></v-combobox>
                    <div class="connector output" @pointerdown="output_press(event,index)"></div>
                    <div class="connector input"></div>
                </v-row>
                <v-tooltip top>
                    <template #activator="{ on }">
                        <v-row class="demo-row no-border" block v-on="on" style="padding: 0px;">
                            <v-btn @click="add_entry();" block v-on="on" style="padding: 0px;">
                                <v-icon>mdi-plus-circle</v-icon>
                            </v-btn>
                        </v-row>
                    </template>
                    <span>Add Entry</span>
                </v-tooltip>
            </v-col>
        </div>
    </div>
</template>

<style scoped>
/* Please style this crap, with style */
.avatar{
    background-color: #083D77;
}

#header {
    display: flex;
    flex-direction: row;
    border-bottom: 2px solid rgba(	100, 100, 220, 0.7);
    padding-bottom: 1.5px;
}

#header i {
    height: 70%;
    border-radius: 10px;
}

#content {
    display: flex;
    flex-direction: row; 
}

.table {
    position: absolute;
    cursor: copy ;
    background: #8789ff;
    box-shadow: 50px 50px 50px rgba(255, 242, 94, 0); /* Removing weird trace on chrome */
    border-radius: 2px;
    padding: 5px;
    padding-bottom: 0;
}

.type_over_enum {
    opacity: 0.7;
}


.v-messages {
    min-height: 0px
}

.v-text-field {
    padding-top: 0px; 
    margin-top: 0px;
}
.v-text-field input {
    padding: 0;
}
.v-text-field__details {
    min-height: 0px;
}

.v-select__selections input {
    width: 5px;
}


.v-select__selections {
    min-width: 65px;
}
.v-select__selection--comma {
    font-size: 15px;
    margin: 0;
    padding: 0;
    margin-right: 5px;
}

.v-select__selection--comma:last-of-type {
    margin-right: 0px;
}


.bold .v-select__selection--comma {
    font-weight: bold;
}

.v-input .v-select__slot {
    width: none;
}

.row.demo-row {
    display: flex;
    flex-wrap: nowrap;
    padding-top: 2.5px;
    padding-bottom: 2.5px;
    border-bottom: 2px solid rgba(	255, 255, 255, 0.2);
}

.row.demo-row:last-child {
  border-style: none !important;
}

.row.demo-row .v-btn::before {
  background-color: transparent;
}
#content > div > .demo-row > button {
  background-color: transparent;
  height: unset !important;
}

#content > div > .demo-row > button > span{
    padding: 2.5px;
}

#content > div > .demo-row > button > span > i{
    font-size: 18px;
}

.row.demo-row button {
  background-color: transparent;
}

.demo-row .v-select__slot {
    flex-direction: row-reverse;
}

.demo-row .connector {
  height: 15px;
  width: 15px;
  background-color: #bbb;
  border-radius: 50%;
  display: inline-block;
  margin-top: 6.5px;
  position: absolute;
  transition: all .2s ease-in-out;
}

.demo-row .connector.input {
    left: -7px;
}

.demo-row .connector.output {
    right: -7px;
}


.demo-row .connector:hover {
    transform: scale(1.11);
}


.rule {
    margin-right: 10px;
    margin-left: 10px;
}


.no-border .v-input__slot::before {
  border-style: none !important;
}

.no-border .v-text-field>.v-input__control>.v-input__slot:after {
      border-style: none !important;
}
.v-input__append-inner .v-input__append-inner {
    display: none;
}
#content div.v-input__slot > div.v-select__slot > div:nth-child(3) {
    display: none;

}

.outline {
    /* outline: 2px solid rgba(	255, 255, 255, 0.7); */
}

</style>