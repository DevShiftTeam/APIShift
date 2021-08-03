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


    // Container is an abstract element that keeps proportions of its contained elements under transformations
    module.exports = {
        mixins: [APIShift.API.getMixin('graph/graph_element')],
        data() {
            return {
                im_a_container: true,
                indices: []
            }
        },
        created () {
            // Construct additional functions and generate runtime warnings on missing implementation
            ['update_indices', 'update_size', 'remove_contained', 'add_contained'].forEach(val => {
                this.expanded_functions[val] = () => {
                    console.warn(`Expanded function ${val} not implemented`);
                }
            });
        },
        methods: {
            remove_contained (index) {
                this.expanded_functions.remove_contained(index);
                this.update_indices();
                this.update_size();
            },
            add_contained (index) {
                this.expanded_functions.add_contained(index);
                this.update_indices();
                this.update_size();
            },
            update_indices () {
                // Step 1 - Execute additional procedures
                this.expanded_functions.update_indices();
            },
            update_size () {
                console.log('[op[');
                this.container_indices.forEach(() => {
                    console.log('asd');
                }); 

                // Step 1 - Execute additional procedures
                this.expanded_functions.update_size();
            }
        },
    };
</script>