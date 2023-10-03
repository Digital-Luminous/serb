const mix = require('laravel-mix');

const jsFiles = ['src/js/app.js'];
const scssFiles = ['src/scss/main.scss'];

mix.setPublicPath("./static").browserSync("protherics.local");

jsFiles.forEach(jsFile => {
    mix.js(jsFile, 'static/js/');
});

scssFiles.forEach(scssFile => {
    mix.sass(scssFile, 'static/css/').options({
        processCssUrls: false,
    });
});

if (!mix.inProduction()) {
  mix.sourceMaps(false, "source-map");
}
