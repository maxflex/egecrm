var config      = require('./.gulpconfig.json');
var gulp 		= require('gulp');
var coffee 		= require('gulp-coffee');
var concat 		= require('gulp-concat');

var browserSync = require('browser-sync').create();
var coffee_cnf  = config.coffee;

bower_packages = [
	'js/bower/angular-resource/angular-resource.min.js'
];

gulp.task('build-vendor', function() {
	gulp.src(bower_packages)
		.pipe(concat(config.bower.bundle))
		.pipe(gulp.dest(config.bower.dest));
});

gulp.task('assets', function() {
	gulp.src(coffee_cnf.assets.src)
		.pipe(coffee(coffee_cnf.task.options))
		.pipe(concat(coffee_cnf.assets.bundle))
		.pipe(gulp.dest(coffee_cnf.assets.dest));

	browserSync.reload();
});

gulp.task('ng-apps', function() {
	gulp.src(coffee_cnf.ngapp.src)
		.pipe(coffee(coffee_cnf.task.options))
		.pipe(gulp.dest(coffee_cnf.ngapp.dest));

	browserSync.reload();
});

gulp.task('watch', ['build-vendor'], function() {
	browserSync.init(config.browsersync.options);

	gulp.watch(coffee_cnf.assets.src, ['assets']);
	gulp.watch(coffee_cnf.ngapp.src, ['ng-apps']);

	// gulp.watch(config.app.files, function() {
	// 	browserSync.reload();
	// });
});

gulp.task('default', ['assets', 'ng-apps', 'build-vendor']);
