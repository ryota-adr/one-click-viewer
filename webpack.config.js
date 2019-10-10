const path = require('path');
const CopyPlugin = require('copy-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const OptimizeCSSAssetsPlugin = require('optimize-css-assets-webpack-plugin');

module.exports = {
    entry: {
        style: './src/scss/app.scss',
        app: './src/ts/app.ts'
    },
    output: {
        path: path.resolve(__dirname, 'src/dist'),
    },
    resolve: {
        modules: [path.resolve(__dirname, "src/css/icomoon/fonts"), "node_modules"]
    },
    module: {
        rules: [
            {
                test: /\.ts$/,
                use: 'ts-loader',
                exclude: /node_modules/,
            },
            {
                test: /\.scss$/,
                use: [
                    { loader: MiniCssExtractPlugin.loader },
                    { loader: 'css-loader' },
                    { loader: 'sass-loader' },
                ],
            },
            {
                test: /\.css$/,
                loader: "css-loader"
            },{
                test: /\.(ttf|eot|svg|woff)(\?[a-z0-9]+)?$/,
                use: [{
                    loader: 'file-loader',
                }]
            }
        ]
    },
    plugins: [
        new CopyPlugin([
            {
                from: 'src/css/icomoon/fonts/icomoon.eot',
                to: 'fonts/icomoon.eot'
            },
            {
                from: 'src/css/icomoon/fonts/icomoon.svg',
                to: 'fonts/icomoon.svg'
            },
            {
                from: 'src/css/icomoon/fonts/icomoon.ttf',
                to: 'fonts/icomoon.ttf'
            },
            {
                from: 'src/css/icomoon/fonts/icomoon.woff',
                to: 'fonts/icomoon.woff'
            }
        ]),
        new CopyPlugin([
            {
                from: 'src/css/icomoon/fonts/icomoon.eot',
                to: '../scss/fonts/icomoon.eot'
            },
            {
                from: 'src/css/icomoon/fonts/icomoon.svg',
                to: '../scss/fonts/icomoon.svg'
            },
            {
                from: 'src/css/icomoon/fonts/icomoon.ttf',
                to: '../scss/fonts/icomoon.ttf'
            },
            {
                from: 'src/css/icomoon/fonts/icomoon.woff',
                to: '../scss/fonts/icomoon.woff'
            }
        ]),
        new MiniCssExtractPlugin({filename: 'app.css'}),
    ],
    optimization: {
        minimizer: [new OptimizeCSSAssetsPlugin({})],
    },
    stats: 'verbose'
}