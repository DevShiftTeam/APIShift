<template>
        <div id="app">
        <v-app id="inspire">
            <!-- Notifications -->
            <component ref="notifications" :is="app_notifications" :alerts="alerts"></component>

            <!-- Loader -->
            <component ref="loader" :is="app_loader" v-if="loader.visible" :message="loader.message"></component>

            <!-- Navigator -->
            <component ref="navigator" :is="app_navigator"></component>

            <!-- Page Body -->
            <router-view></router-view>

            <!-- Footer -->
            <component ref="footer" :is="app_footer"></component>
        </v-app>
    </div>
</template>

<script>
export default {
    name: "App"
}
</script>