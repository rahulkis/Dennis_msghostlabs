const path = require('path');
const TerserPlugin = require("terser-webpack-plugin");

module.exports = {
    mode: "production",
    entry: {
        "sb-instagram": "./js/sb-instagram.js",
        "sbi-scripts": "./js/sbi-scripts.js"
    },
    output: {
        path: path.resolve(__dirname, 'js'),
        filename: '[name].min.js',
        sourceMapFilename: '[name].js.map'
    },
    optimization: {
        minimize: true,
        minimizer: [new TerserPlugin({
            extractComments: false,
        })],
    },
    externals: {
        "jquery": "jQuery"
    },
    devServer: {
        static: './js',
        hot: true,
    },
};