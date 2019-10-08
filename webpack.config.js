const path = require('path');
const CopyPlugin = require('copy-webpack-plugin');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const OptimizeCSSAssetsPlugin = require('optimize-css-assets-webpack-plugin');

module.exports = {
    entry: {
        anyname: './src/scss/app.scss',
    },
    output: {
        path: path.resolve(__dirname, 'src/dist'),
    },
    module: {
        rules: [
            {
                test: /\.scss$/,
                include: [
                    path.resolve(__dirname, 'src/css/icomoon/fonts'),
                ],
                use: [
                    { loader: MiniCssExtractPlugin.loader },
                    { loader: 'css-loader' },
                    { loader: 'sass-loader' },
                ],
            },
            {
                test: /\.css$/,
                loader: "css-loader"
            },
            {test: /\.(woff|woff2|eot|ttf|svg)$/,loader: 'url-loader?limit=100000'},
            {
                test: /\.woff(2)?(\?[a-z0-9]+)?$/,
                loader: "url-loader?limit=10000&mimetype=application/font-woff"
            },{
                test: /\.(ttf|eot|svg)(\?[a-z0-9]+)?$/,
                use: [{
                    loader: 'file-loader',
                }]
            }
        ]
    },
    plugins: [/*
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
        ]),*/
        new MiniCssExtractPlugin({filename: 'app.css'}),
    ],
    optimization: {
        minimizer: [new OptimizeCSSAssetsPlugin({})],
    },

}