const path = require('node:path');
const webpack = require('webpack');
const TerserPlugin = require('terser-webpack-plugin');

module.exports = async (env, argv) => {
  const { styles } = await import('@ckeditor/ckeditor5-dev-utils');
  const isProduction = argv.mode === 'production';

  return {
    mode: isProduction ? 'production' : 'development',
    devtool: isProduction ? false : 'source-map',
    entry: path.resolve(__dirname, 'js/src/index.js'),
    output: {
      path: path.resolve(__dirname, 'js/build'),
      filename: 'index.js',
      library: ['CKEditor5', 'font'],
      libraryTarget: 'umd',
      libraryExport: 'default',
    },
    optimization: {
      minimize: isProduction,
      minimizer: isProduction
        ? [
            new TerserPlugin({
              terserOptions: {
                format: {
                  comments: false,
                },
              },
              test: /\.js(\?.*)?$/i,
              extractComments: false,
            }),
          ]
        : [],
      moduleIds: 'named',
    },
    resolve: {
      alias: {
        'ckeditor5/src/font': '@ckeditor/ckeditor5-font',
      },
    },
    plugins: [
      new webpack.BannerPlugin('cspell:disable'),
      new webpack.DllReferencePlugin({
        manifest: require(path.resolve(
          __dirname,
          'node_modules/ckeditor5/build/ckeditor5-dll.manifest.json',
        )),
        scope: 'ckeditor5/src',
        name: 'CKEditor5.dll',
      }),
    ],
    module: {
      rules: [
        {
          test: /\.svg$/,
          type: 'asset/source',
        },
        {
          test: /ckeditor5-[^/\\]+[/\\]theme[/\\].+\.css$/,
          use: [
            {
              loader: 'style-loader',
              options: {
                injectType: 'singletonStyleTag',
                attributes: {
                  'data-cke': true,
                },
              },
            },
            'css-loader',
            {
              loader: 'postcss-loader',
              options: {
                postcssOptions: styles.getPostCssConfig({
                  themeImporter: {
                    themePath: require.resolve('@ckeditor/ckeditor5-theme-lark'),
                  },
                  minify: isProduction,
                }),
              },
            },
          ],
        },
      ],
    },
  };
};
