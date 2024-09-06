let mix = require('laravel-mix')

require('./nova.mix')

mix
    .setPublicPath('dist')
    .js('resources/js/field.js', 'js')
    .vue({ version: 3 })
    .autoload({ jquery: ['$', 'window.jQuery', 'jQuery'] })
    .webpackConfig({
        resolve: {
            symlinks: false,
        },
    })
    .sass('resources/sass/field.scss', 'css')
    .nova('hylark/article-content')
