module.exports = {
    transpileDependencies: ["vuetify"],
    publicPath: "/control",
    pages: {
        index: {
            entry: "./src/main.js",
            template: "./public/index.html",
        },
    },
    runtimeCompiler: true,
    chainWebpack: (config) => {
        config.module
            .rule("i18n")
            .resourceQuery(/blockType=i18n/)
            .type("javascript/auto")
            .use("i18n")
            .loader("@intlify/vue-i18n-loader");
    },
};