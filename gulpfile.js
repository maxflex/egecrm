var config = require('./.gulpconfig.json');

var gulp 		= require('gulp');
var coffee 		= require('gulp-coffee');
var concat 		= require('gulp-concat');
// var ngAnnotate 	= require('gulp-ng-annotate');
// var uglify 		= require('gulp-uglify');

var browserSync = require('browser-sync').create();

gulp.task('default', function() {
    return gulp.src(['js/directives/*.coffee'])
		.pipe(coffee({bare: true}))
		// .pipe(ngAnnotate())
		// .pipe(uglify())
		.pipe(concat('directives.js'))
		.pipe(gulp.dest('js'));
});

gulp.task('watch', function() {
	browserSync.init(config.browsersync);

	gulp.watch('js/directives/*.coffee', ['default']);
	gulp.watch('js/*.js').on('change', browserSync.reload);
});