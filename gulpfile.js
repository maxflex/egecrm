var config      = require('./.gulpconfig.json');
var gulp 		= require('gulp');
var coffee 		= require('gulp-coffee');
var concat 		= require('gulp-concat');

var browserSync = require('browser-sync').create();
var coffee_cnf  = config.coffee;

gulp.task('directives', function() {
	gulp.src([coffee_cnf.directives.src])
		.pipe(coffee(coffee_cnf.task.options))
		.pipe(concat(coffee_cnf.directives.bundle))
		.pipe(gulp.dest(coffee_cnf.directives.dest));

	browserSync.reload();
});

gulp.task('ng-apps', function() {
	gulp.src([coffee_cnf.ngapp.src])
		.pipe(coffee(coffee_cnf.task.options))
		.pipe(gulp.dest(coffee_cnf.ngapp.dest));

	browserSync.reload();
});

gulp.task('watch', function() {
	browserSync.init(config.browsersync.options);

	gulp.watch(coffee_cnf.directives.src, ['directives']);
	gulp.watch(coffee_cnf.ngapp.src, ['ng-apps']);
});


// var ngAnnotate 	= require('gulp-ng-annotate');
// var uglify 		= require('gulp-uglify');
// .pipe(ngAnnotate())
// .pipe(uglify())
