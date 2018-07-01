// Load Gulp...of course
var gulp         = require( 'gulp' );

// CSS related plugins
var sass         = require( 'gulp-sass' );
var autoprefixer = require( 'gulp-autoprefixer' );
var uglifycss    = require( 'gulp-uglifycss' );
var minify       = require( 'gulp-minify' );
var concat = require('gulp-concat');
php = require('gulp-connect-php');

// JS
var jsSRC        = './assets/src/js/**/*.js';
var jsApp      = 'main.js';
var jsDIST        = './assets/dist/js/';
var jsWatch      = './assets/src/js/**/*.js';

// Utility plugins
var rename       = require( 'gulp-rename' );
var sourcemaps   = require( 'gulp-sourcemaps' );

// Project related variables

var styleSRC     = './assets/src/sass/app.scss';
var styleURL     = './assets/dist/css/';
var mapURL       = './';
//var styleWatch   = './assets/stylesheets/**/*.css';
var styleWatch   = './assets/src/sass/**/*.scss';
var phpWatch     = './**/*.php';


// Browers related plugins
var browserSync  = require( 'browser-sync' ).create();
var reload       = browserSync.reload;

//Tasks

var browserSyncWatchFiles = [
    './css/*.min.css',
    './js/*.min.js',
    './**/*.php'
];
var browserSyncOptions = {
    proxy: "192.168.1.10:8888/checkfire_live/",
    notify: true
};


function swallowError (error) {

    // If you want details of the error in the console
    console.log(error.toString())

    this.emit('end')
}



gulp.task('browser-sync', function() {
    browserSync.init(browserSyncWatchFiles, browserSyncOptions);
});

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
        .pipe( browserSync.stream() );
});

gulp.task('js', function() {
    return gulp.src(['./assets/src/js/*.js'])
        .pipe(concat('app.js'))
        /*.pipe(minify({
            ext:{
                src:'-debug.js',
                min:'.js'
            },
            exclude: ['tasks'],
        }))*/
        .on('error', swallowError)
        .pipe(gulp.dest('./assets/dist/js'))
        .pipe( browserSync.stream() );
});

gulp.task( 'default', ['css', 'js']);

/*gulp.task( 'watch', ['default' , 'browser-sync'], function() {
   // gulp.watch( phpWatch, reload );
    gulp.watch( styleWatch, [ 'css', reload ] );
    gulp.watch( jsWatch, [ 'js' , reload ] );
});
*/
gulp.task( 'watch', ['default'], function() {
    // gulp.watch( phpWatch, reload );
    gulp.watch( styleWatch, [ 'css' ] );
    gulp.watch( jsWatch, [ 'js'  ] );
});