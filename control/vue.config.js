module.exports = {
    transpileDependencies: ["vuetify"],
    publicPath: "/control",
    pages: {
        index: {
            entry: "./src/main.js",
            template: "./public/index.html",
        },
    },
    devServer: {
        proxy: {
            "/api": {
                target: "https://apishift.local/",
                changeOrigin: true,
                pathRewrite: { "^/api": "" },
                logLevel: "debug",
            },
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