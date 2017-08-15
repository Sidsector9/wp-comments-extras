var gulp   = require( 'gulp' ),
	uglify = require( 'gulp-uglify' ),
	sass   = require( 'gulp-sass' ),
	concat = require( 'gulp-concat' ),
	rename = require( 'gulp-rename' );

// Minify JS
gulp.task( 'script', function() {
	return gulp.src( [
		'assets/js/src/*.js',
		'!assets/js/src/*.min.js'
	])
	.pipe( rename( { suffix: '.min' } ) )
	.pipe( uglify() )
	.pipe( gulp.dest( 'assets/js/src/' ) );
});

// SCSS to CSS
gulp.task( 'sass', function() {
	return gulp.src( [
		'assets/css/scss/*.scss'
	])
	.pipe( concat( 'wce-style.min.css' ) )
	.pipe( sass( { outputStyle: 'compressed'} ).on( 'error', sass.logError ) )
	.pipe( gulp.dest( 'assets/css/' ) );
});

gulp.task( 'watch', function() {
	gulp.watch( ['assets/css/scss/*.scss'], ['sass']);
	gulp.watch( ['assets/js/src/wce-script.js'], ['script']);
});

// Default Task
gulp.task( 'default', [
	'script',
	'sass',
	'watch'
]);
