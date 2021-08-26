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
        mixins: [APIShift.API.getMixin('graph/line_parent')],
        data () {
            return {
                drawer: null,
                operations: [
                    'SELECT',
                    'SET',                    
                ],
                selected_operation: 'SELECT',      
                columns: [
                    'ID',
                    'USERNAME',
                    'PASSWORD',
                    'CREATED_AT'
                ],          
                is_edit_mode: {
                    name: false,
                    entry: false
                },
                column: 'PASSWORD',
                column_where: 'USERNAME',
                entries: ['USERNAME'],
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
                offsetTop: 2
            }
        },
        created () {
            window.graph_elements[this.$props.index] = this;
            window.t = this;

            // setInterval(() => {
            //     console.log(this.);
            // }, 1000);
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
                for (const connection_index in this.$props.data.connections) {
                    let connection = this.$props.data.connections[connection_index];
                    let to_index = graph_view.elements.findIndex((elem) => elem.id === connection.id && elem.component_id === connection.component_id);          

                    // Draw lines to elements
                    setTimeout(() => {
                        this.to_line_index = this.create_line(to_index ,{
                                is_curvy: true,
                                is_stroked: false,
                                is_interactive: true
                        }, true);
                    });
                }
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
            on_connection_click (event, connector_index) {
                let connection = this.$props.data.connections[connector_index];
                let connectors = this.$el.querySelectorAll(`#content .connector`);
                let offsetTop = connectors[connector_index].offsetTop - connectors[0].offsetTop;
                let graph_pos = document.body.querySelector("#graph_center").getBoundingClientRect();
                let point_index = this.create_point(false, { 
                        x: ( connectors[connector_index].getBoundingClientRect().x -  graph_pos.x) / graph_view.scale,
                        y: ( connectors[connector_index].getBoundingClientRect().y - graph_pos.y) / graph_view.scale
                    }
                );
                this.drag_end(event); 


                setTimeout(() => {
                    let line_index = Object.keys(this.line_connector_map).find(line_index => 
                                            this.line_connector_map[line_index] == connector_index);
                    if (line_index) 
                    {
                        graph_view.lines[line_index].to_index = point_index;
                        graph_view.lines[line_index].data.offsetTopDest = 0;
                    }
                    else this.create_line(point_index, {
                            is_curvy: true,
                            is_stroked: false,
                            offsetTopSrc: offsetTop,
                            is_interactive: true
                    }, true);
                    
                    // Emulate a click on newly created point - fold currently active event handlers
                    this.drag_end(event); 
                    graph_elements[point_index].drag_start(event);
                });

            },
            on_line_connected (line_index, is_left) {
                if (this.first_load && graph_view.lines[line_index].from_index == this.index) {
                    let con_index = this.line_connector_map[line_index] = this.entries_counter++;
                    this.first_load = this.entries_counter != this.$props.data.connections.length;
                }
                else if (graph_view.lines[line_index].from_index == this.index) {
                    // Calculate matching connector index by an inversion of offsetTopSrc property as a function of connector index
                    let row_height = this.$el.querySelector('.row.demo-row').offsetHeight;
                    let con_index = this.line_connector_map[line_index] = (graph_view.lines[line_index].data.offsetTopSrc | 0) / row_height;
                }
            },
            on_line_disconnected (line_index, is_left) {
                // Extract corresponding connector index
                let con_index = this.line_connector_map[line_index];

                // Delete corresponding connector by index 
                // this.$props.data.connections[con_index].is_con = false;

                // Delete line connector mapping
                delete this.line_connector_map[line_index];


                // console.log('disconnect: ', line_index, this.line_connector_map);
            },
            replace_connected_expanded(current_index, replace_index, line_index) {
                // console.log(current_index, replace_index, line_index);
                // Replace from point to non-point & delete point
                if (graph_view.elements[current_index].component_id == 5 && graph_view.elements[replace_index].component_id != 5) {
                    
                    // this.line_connector_map[line_index] = this.$props.data.connections.findIndex(con => con.id == graph_view.elements[replace_index].id 
                    //                                                 && con.component_id == graph_view.elements[replace_index].component_id);
                                                            
                }
                // Replace from non-point to point
                if (graph_view.elements[current_index].component_id != 5 && graph_view.elements[replace_index].component_id == 5) {
                    // Remove data

                }
            },
            on_point_drag_end () {
                let point_index = this.point_indices[this.point_indices.length - 1];
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
                    y: this.$props.data.position.y + this.get_rect.height / 2 + this.offsetTop
                };
            },
            to_position: function() {
                return {
                    x: this.$props.data.position.x,
                    y: this.$props.data.position.y + this.get_rect.height / 2
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

                <!-- <v-tooltip top>
                    <template #activator="{ on }">
                        <div v-on="on" id="header" :style="{minWidth: header_min_width + 'px'}"
                                @input="on_input"
                                @dblclick.prevent="is_edit_mode.name = true">
                                <v-avatar size="25" left class="avatar darken-4">T</v-avatar>
                                <v-text-field
                                    v-model="name"
                                    v-show="is_edit_mode.name"
                                    :style="{'width': edit_name_width + 'px', marginLeft: 'auto', marginRight: 'auto'}"
                                    @input="requestAnimationFrame(() => edit_name_width = $el.querySelector('#name').offsetWidth + 30)"
                                    >
                                </v-text-field>
                                <b :style="{  marginLeft: '5px', marginRight: 'auto', fontSize: '120%' }" v-show="!is_edit_mode.name">{{name}}</b>
                                <span id="name" style="position: absolute; top: -120%; color: rgba(0,0,0,0);">{{name}}</span>
                        </div>
                    </template>
                    <span>Query Database</span>
                </v-tooltip> -->

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
                <v-row v-for="(connection,index) in $props.data.connections" :key="index" class="demo-row no-border" style="display: flex">
                    <!-- <v-select
                        class="bold"
                        value="SELECT"
                        :items="operations"
                        @change="ui_refresher++"
                    ></v-select> -->

                    <v-combobox
                        v-model="connection.entries"
                        @input="setTimeout(() => ui_refresher++)"
                        :items="columns"
                        multiple
                    ></v-combobox>
                    <!-- <v-select
                        class="center"
                        style="width: 100%;"
                        value="SELECT"
                        v-model="entries[index]"
                        :items="columns"
                        clearable
                        @change="ui_refresher++"
                    ></v-select> -->
                    <div class="connector" @pointerdown="on_connection_click(event,index)"></div>
                </v-row>
                <v-tooltip top>
                    <template #activator="{ on }">
                        <v-row class="demo-row no-border" block v-on="on" style="padding: 0px;">
                            <v-btn @click="$props.data.connections.push({entries: []}); setTimeout(() => {ui_refresher++; offsetTop -= 21.5;});" block v-on="on" style="padding: 0px;">
                                <v-icon>mdi-plus-circle</v-icon>
                            </v-btn>
                        </v-row>
                    </template>
                    <span>Add Entry</span>
                </v-tooltip>
                <!-- <v-divider></v-divider> -->
                <!-- <v-row class="demo-row">
                    <b class="v-select__slot">WHERE</b>
                    <div style="display: flex">
                        <v-text-field
                            v-model="column"
                            class="outline"
                            :style="{'max-width': column_width - 30 + 'px', marginRight: '10px',  marginTop: '2.5px'}"
                            @input="requestAnimationFrame(() => { column_width = $el.querySelector('#column').offsetWidth + 30; ui_refresher++;})">
                        </v-text-field>
                        <span id="column" style="position: absolute; top: -120%;">{{column}}</span>
                        <v-select
                            class="center"
                            style="width: 150px; margin-left: 10px"
                            value="SELECT"
                            v-model="column_where"
                            :items="columns"
                            @change="ui_refresher++"
                        ></v-select>
                        <b class="rule">==</b>
                        <v-select
                            class="center"
                            style="width: 150px"
                            v-model="input"
                            :items="data.inputs"
                            @change="ui_refresher++"
                        ></v-select>
                        <v-text-field
                            v-model="input"
                            class="outline"
                            :style="{'max-width': input_width - 30 + 'px', marginLeft: '10px',  marginTop: '2.5px'}"
                            @input="requestAnimationFrame(() => { input_width = $el.querySelector('#input').offsetWidth + 30; ui_refresher++;})">
                        </v-text-field>
                        <span id="input" style="position: absolute; top: -120%;">{{input}}</span>
                    </div>
                </v-row> -->
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

/* .v-input {
    flex: none;
}

.v-input__control {
    flex-direction: column;
}
.v-input__slot {
    margin: 0;
}

.v-text-field .v-input__append-inner {
    margin: 0;
}

.v-application--is-ltr .v-text-field .v-input__append-inner {
    padding-left: 0px;
}

*/
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


/* .center .v-select__selection--comma {
    margin-left: 4px;
    margin-right: auto;
} */

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
  right: 0px;
  transform: translateX(50%);
}
.demo-row .connector:hover {
    transform: translateX(50%) scale(1.11);
}


.rule {
    margin-right: 10px;
    margin-left: 10px;
}
/* .v-select__slot {
    width: 100px;
} */

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
/* #content .demo-row div.v-input__append-inner {
    display: none;
} */

.outline {
    /* outline: 2px solid rgba(	255, 255, 255, 0.7); */
}

</style>