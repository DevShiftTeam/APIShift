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
     * @author Sapir Shemer
     * @contributor Ilan Dazanashvili
     */

    window.empty_function = (event) => {};

    // This shit is made for scripting
    module.exports = {
        mixins: [APIShift.API.getMixin('graph/graph_view', true)],
        data () {
            return {
                components: {
                    elements: [
                        APIShift.API.getComponent('orm/item', true),
                        APIShift.API.getComponent('orm/relation', true),
                        APIShift.API.getComponent('orm/enum_type', true),
                        APIShift.API.getComponent('orm/enum', true),
                        APIShift.API.getComponent('orm/group', true),
                        APIShift.API.getComponent('graph/point', true)
                    ],
                    line: APIShift.API.getComponent('graph/line', true),
                    selection: APIShift.API.getComponent('graph/selection_box', true),
                    side_menu: APIShift.API.getComponent('graph/side_menu', true),
                    context_menu: APIShift.API.getComponent('graph/context_menu',true),
                    dialog: APIShift.API.getComponent('graph/dialog/edit_dialog', true),
                },
                elements: [{"id":1,"component_id":0,"name":"Users","data":{"position":{"x":-331.0928587179182,"y":-87.60010148588768},"z_index":1}},
{"id":2,"component_id":0,"name":"Posts","data":{"position":{"x":270.1880220649608,"y":-64.07155210525946},"z_index":15}},
{"id":3,"component_id":1,"name":"UserLikes","data":{"position":{"x":26.99052416987227,"y":-303.55736108051866},"to":9,"type":0,"z_index":2,"from":1}},
{"id":4,"component_id":1,"name":"UserPosts","data":{"position":{"x":23.756495378279514,"y":-63.42524290022976},"from":1,"to":2,"type":1,"z_index":3}},
{"id":5,"component_id":1,"name":"UserFollowings","data":{"position":{"x":-369.953,"y":-171.915},"type":0,"z_index":4,"to":1,"from":1}},
{"id":6,"component_id":0,"name":"Comments","data":{"position":{"x":247.25984183662717,"y":-141.54359260950704},"z_index":16}},
{"id":7,"component_id":1,"name":"PostComments","data":{"position":{"x":442.59079211081684,"y":-62.165071798604565},"type":0,"z_index":5,"to":6,"from":2}},
{"id":8,"component_id":1,"name":"UserComments","data":{"position":{"x":6.850581285689288,"y":-142.163903256786},"type":0,"z_index":6,"to":6,"from":1}},
{"id":9,"component_id":0,"name":"Likes","data":{"position":{"x":268.8684343799391,"y":-303.6934583113779},"z_index":7}},
{"id":10,"component_id":1,"name":"CommentLikes","data":{"position":{"x":437.3783735315968,"y":-142.1994140447473},"type":0,"z_index":8,"from":6,"to":9}},
{"id":11,"component_id":1,"name":"SubComments","data":{"position":{"x":233.99623221684593,"y":-212.60691197305854},"type":0,"z_index":17,"from":6,"to":6}},
{"id":12,"component_id":1,"name":"UserStories","data":{"position":{"x":20.568670497996763,"y":-403.9952733237452},"type":0,"z_index":9,"from":1,"to":13}},
{"id":13,"component_id":0,"name":"Story","data":{"position":{"x":268.3961275871574,"y":-403.53479607879},"z_index":10}},
{"id":14,"component_id":0,"name":"Chat","data":{"position":{"x":274.1835944473998,"y":13.561731173898133},"z_index":11}},
{"id":15,"component_id":1,"name":"UserChats","data":{"position":{"x":22.730746891759473,"y":13.454766254036446},"type":0,"z_index":12,"to":14,"from":1}},
{"id":16,"component_id":0,"name":"Messages","data":{"position":{"x":254.5508163484731,"y":115.16031480782034},"z_index":18}},
{"id":17,"component_id":1,"name":"ChatMessages","data":{"position":{"x":442.8031392552688,"y":15.873727308144858},"type":0,"z_index":13,"from":14,"to":16}},
{"id":18,"component_id":1,"name":"MessageViewers","data":{"position":{"x":-0.30253793736002876,"y":115.02548992971262},"type":0,"z_index":14,"from":1,"to":16}}],
              side_menu_actions: [
                    {
                        starter: () => {
                            graph_view.cursor_state = "create";
                            graph_view.current_action = () => {
                                let id = Math.max(...graph_view.elements.filter(el => el.component_id == 0 || el.component_id == 1 || el.component_id == 4).map(el => el.id));
                                graph_view.elements.push({
                                    // TODO: Determine id on server-side
                                    id: isFinite(id) ? id + 1 : 1, component_id: 0, name: "Item", data: {
                                        position: window.mouse_on_graph,
                                        z_index: graph_view.elements.length
                                    }
                                });
                            }
                        },
                        icon: 'mdi-plus',
                        text: "Add Item" 
                    },
                    {
                        starter: () => {
                            graph_view.cursor_state = "create";
                            graph_view.current_action = () => {
                                let id = Math.max(...graph_view.elements.filter(el => el.component_id == 0 || el.component_id == 1 || el.component_id == 4).map(el => el.id));
                                graph_view.elements.push({
                                    // TODO: Determine id on server-side
                                    id: isFinite(id) ? id + 1 : 1, component_id: 1, name: "Relation", data: {
                                        position: window.mouse_on_graph,
                                        type: 0,
                                        z_index: graph_view.elements.length
                                    }
                                });
                            };
                        },
                        icon: 'mdi-arrow-right',
                        text: "Add Relation" 
                    },
                    {
                        starter: () => {
                            graph_view.cursor_state = "delete";
                            graph_view.current_action = (event) => {
                                let target_element = -1, z_index = 0;

                                for(let index in [...graph_view.elements.keys()]) {
                                    // Skip undefined or deleted
                                    if(window.graph_elements[index] === undefined || window.graph_elements[index].is_deleted)
                                        continue;
                                    
                                    // Check collisions
                                    let obj_rect = window.graph_elements[index].get_rect;
                                    if (window.graph_elements[index].data.z_index > z_index && (
                                        obj_rect.x < window.mouse_on_graph.x && obj_rect.x + obj_rect.width > window.mouse_on_graph.x
                                        && obj_rect.y < window.mouse_on_graph.y && obj_rect.y + obj_rect.height > window.mouse_on_graph.y
                                    )) {
                                        target_element = index;
                                        z_index = graph_view.elements[index].data.z_index;
                                    }
                                }
                                
                                // Delete targeted element
                                if (target_element !== -1) window.graph_elements[target_element].on_delete();
                            };
                        },
                        icon: 'mdi-delete-outline',
                        text: "Delete Tool" 
                    },
                    {
                        starter: () => {
                            graph_view.cursor_state = "create";
                            graph_view.current_action = () => {
                                let id = Math.max(...graph_view.elements.filter(el => el.component_id == 3).map(el => el.id));
                                graph_view.elements.push({
                                    // TODO: Determine id on server-side
                                    id: isFinite(id) ? id + 1 : 1, component_id: 3, name: "Enum", data: {
                                        position: window.mouse_on_graph,
                                        types: [],
                                        connected: [],
                                        z_index: graph_view.elements.length
                                    }
                                });
                            };
                        },
                        icon: 'fas fa-cubes',
                        text: "Add Enum" 
                    }, 
                    {
                        starter: () => {
                            graph_view.cursor_state = "create";
                            graph_view.current_action = () => {
                                let id = Math.max(...graph_view.elements.filter(el => el.component_id == 2).map(el => el.id));
                                graph_view.elements.push({
                                    // TODO: Determine id on server-side
                                    id: isFinite(id) ? id + 1 : 1, component_id: 2, name: "Type", data: {
                                        position: window.mouse_on_graph,
                                        z_index: graph_view.elements.length
                                    }
                                });
                            }
                        },
                        icon: 'fas fa-cube',
                        text: "Add Enum Type"
                    },
                    {
                        starter: () => {
                            graph_view.cursor_state = "crosshair";
                            graph_view.current_action = window.selection_box.start_select;
                        },
                        icon: 'fa-object-group', 
                        text: "Add Group" 
                    }
                ]
            }
        },
        mounted () {
            this.update_graph_position();
        },
        methods: {

        },
        watch: {
        }
    }
</script>

<template>
        <v-container fluid fill-height>
            <v-card class="mx-auto" width="100%" min-height="75%" elevation-2>
                <!-- Header -->
                <v-app-bar>
                    <v-menu offset-y style="z-index: 100">
                        <template v-slot:activator="{ on }">
                            <v-toolbar-title v-on="on">
                                <v-btn>
                                    {{ holder.getPageTitle() }}
                                    
                                    <v-icon right>mdi-menu-down</v-icon>
                                </v-btn>
                            </v-toolbar-title>
                        </template>
                        <v-list>
                            <v-list-item to="/database">Main Page</v-list-item>
                            <v-list-item v-for="(item, key) in holder.sub_pages" :key="key" :to="item.url">{{ item.title }}</v-list-item>
                        </v-list>
                    </v-menu>
                    <v-divider class="mx-4" inset vertical></v-divider>
                    <v-spacer></v-spacer>
                    <v-btn @click="on_save">SAVE</v-btn>
                </v-app-bar>

                <!-- Body -->
                <div class="overflow-box">
                    <div id="graph_view"
                        @wheel.prevent="wheel"
                        @pointermove.prevent="drag_handler"
                        @touchmove.prevent="() => {}"
                        @pointerdown="pointer_down"
                        @pointerup="pointer_up"
                        @pointercancel="pointer_up"
                        @contextmenu.prevent="window.empty_function"
                        :style="{ 'cursor' : cursor_state }">
                        <component ref="side_menu"
                            :is="components.side_menu"
                            :actions="side_menu_actions"
                        >
                        </component>
                        
                        <!-- The center element allow us to create a smart camera that positions the elements without needed to re-render for each element -->
                        <div ref="gv_center" id="graph_center" :style="{ 'transform': 'translate(' + camera.x + 'px, ' + camera.y + 'px) scale(' + scale + ')'}">
                            <!-- Elements -->
                            <component
                                v-for="(element, index) in elements"
                                v-show="!element.is_deleted"
                                :is="components.elements[element.component_id]"
                                :key="index"
                                :index="index"
                                :id="element.id"
                                :name="element.name"
                                :data="element.data">
                            </component>

                            <!-- Lines -->
                            <component
                                v-for="(line, index) in lines"
                                :key="index"
                                :index="index"
                                :is="components.line"
                                :src_ref="window.graph_elements[line.from_index]"
                                :dest_ref="window.graph_elements[line.to_index]"
                                :data="line.data">
                            </component>
                        </div>
                        <component ref="s_box" 
                            v-show="selection_active"
                            :is="components.selection">
                        </component>
                        <component ref="c_menu"
                            v-show="contextmenu.is_active"
                            :actions="contextmenu.actions"
                            :position="contextmenu.position"
                            :is="components.context_menu"> 
                        </component>
                    </div>

                    <!-- Dialog -->
                    <component ref="dialog"
                        v-if="dialog_open"
                        :is="components.dialog"
                        :data="dialog_data"
                    >
                    </component>    
                </div>
            </v-card>
        </v-container>
</template>

<style scoped>
/* Please style this crap, with style */

/* Disables all cursor overrides when body has this class. */
#graph_view {
    overflow: hidden;
}

#graph_view, #graph_center {
    position: relative;
    width: 100%;
    height: 100%;
    user-select: none;
}
#svg_viewport {
    position: relative; 
    height: 100%; 
    width: 100%;
    overflow: visible;
}

.shadow {
    border: solid white 1px;
    display: inline-block;
    position: absolute;
    cursor: copy ;
    height: 50px;
    width: 50px;
    background: #8789ff;
    box-shadow: 50px 50px 50px rgba(255, 242, 94, 0); /* Removing weird trace on chrome */
}
#graph_view.cursor_delete {
    cursor: not-allowed !important;
}
#graph_view.cursor_select {
    cursor: se-resize !important;
}
#graph_view.cursor_create {
    cursor: copy !important;
}
#graph_view.cursor_default {
    cursor: inherit !important;
}

.ge_text {
    display: inline-block;
  width: fit-content; 

    padding: 0;
}
.ge_text input {
    width: fit-content;
    padding: 0;
}

.ge_text .v-text-field__details {
    min-height: 0px;
}

.ge_text .v-text-field__details .v-messages{
    min-height: 0px;
}
</style>