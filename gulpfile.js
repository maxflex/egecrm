var config      = require('./.gulpconfig.json');
var gulp 		= require('gulp');
var coffee 		= require('gulp-coffee');
var concat 		= require('gulp-concat');
// var sass        = require('gulp-sass');

var browserSync = require('browser-sync').create();
var coffee_cnf  = config.coffee;

bower_packages = [
	'js/bower/angular-resource/angular-resource.min.js',
	'js/bower/vue/dist/vue.min.js',
	'js/bower/vue-resource/dist/vue-resource.min.js'
];

gulp.task('build-vendor', function() {
	gulp.src(bower_packages)
		.pipe(concat(config.bower.bundle))
		.pipe(gulp.dest(config.bower.dest));
});

gulp.task('sass', function () {
    gulp.src(config.sass.src)
        .pipe(sass())
        .pipe(gulp.dest(config.sass.dest));
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
    gulp.watch(config.sass.src, ['sass']);

	// gulp.watch(config.app.files, function() {
	// 	browserSync.reload();
	// });
});

gulp.task('default', ['assets', 'sass', 'ng-apps', 'build-vendor']);
