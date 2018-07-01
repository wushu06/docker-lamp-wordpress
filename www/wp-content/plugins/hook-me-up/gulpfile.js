// Load Gulp...of course
var gulp         = require( 'gulp' );

// CSS related plugins
var sass         = require( 'gulp-sass' );
var autoprefixer = require( 'gulp-autoprefixer' );
var uglifycss    = require( 'gulp-uglifycss' );
var minify       = require( 'gulp-minify' );

// JS
var jsSRC        = './assets/src/app.js';
var jsApp      = 'main.js';
var jsDIST        = './assets/dist/';
var jsWatch      = './assets/src/**/*.js';

// Utility plugins
var rename       = require( 'gulp-rename' );
var sourcemaps   = require( 'gulp-sourcemaps' );

// Project related variables

var styleSRC     = './assets/src/app.scss';
var styleURL     = './assets/dist/';
var mapURL       = './';
var styleWatch   = './assets/src/**/*.scss';


// Browers related plugins
/*var browserSync  = require( 'browser-sync' ).create();
var reload       = browserSync.reload;*/

//Tasks

gulp.task( 'css', function() {
    gulp.src(  styleSRC  )
        .pipe( sourcemaps.init())
        .pipe( sass({
            errLogToConsole: true,
            outputStyle: 'compressed'
        }) )
       /* .pipe(uglifycss({
            "maxLineLen": 80,
            "uglyComments": true
        }))*/
        .on( 'error', console.error.bind( console ) )
        .pipe( rename( { suffix: '.min' } ) )
       // .pipe(autoprefixer("last 1 version", "> 1%", "ie 8", "ie 7"))
        .pipe(sourcemaps.write('.'))
        .pipe( gulp.dest( styleURL ) )
});

gulp.task( 'js', function() {
    gulp.src(jsSRC)
        /*.pipe(minify({
            ext:{
                src:'-debug.js',
                min:'.js'
            },
            exclude: ['tasks'],
        }))*/
        .pipe( gulp.dest( jsDIST ) );
});

gulp.task( 'default', ['css', 'js']);
gulp.task( 'watch', ['default'], function() {
    gulp.watch( styleWatch, [ 'css' ] );
    gulp.watch( jsWatch, [ 'js' ] );
});