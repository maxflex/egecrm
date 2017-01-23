var config      = require('./.gulpconfig.json');
var gulp 		= require('gulp');
var coffee 		= require('gulp-coffee');
var concat 		= require('gulp-concat');
var sass        = require('gulp-sass');
var addsrc      = require('gulp-add-src');

var browserSync = require('browser-sync').create();
var coffee_cnf  = config.coffee;

vendor_files = [
	'js/vendor/jquery.min.js',
	'js/vendor/jquery.cookie.js',
	'js/vendor/floatlabel.js',
	'js/vendor/nprogress.js',
	'js/vendor/mask.js',
	'js/vendor/jquery.inputmask.min.js',
	'js/vendor/angular.js',
	'js/vendor/angular-locale-ru.js',
	'js/vendor/angular-animate.js',
	'js/vendor/ngmap.min.js',
	'js/vendor/name.js',
	'js/vendor/bootstrap.min.js',
	'js/vendor/bootbox.js',
	'js/vendor/notify.js',
	'js/vendor/moment.min.js',
	'js/vendor/bootstrap-datepicker.min.js',
	'js/vendor/bootstrap-datetimepicker.js',
	'js/vendor/jquery.timepicker.js',
	'js/vendor/jquery.ui.widget.js',
	'js/vendor/jquery.iframe-transport.js',
	'js/vendor/jquery.fileupload.js',
	'js/vendor/underscore.js',
	'js/vendor/md5.js',
	'js/bower/vue/dist/vue.js',
	'js/vendor/spin.js',
	'js/vendor/ladda.js',
	'js/bower/angular-resource/angular-resource.min.js',
	'js/bower/vue/dist/vue.min.js',
	'js/bower/vue-resource/dist/vue-resource.min.js',
	'js/bower/jsSHA/src/sha256.js',
	'js/bower/cropper/dist/cropper.js',
	'js/bower/angular-bootstrap-colorpicker/js/bootstrap-colorpicker-module.min.js',
	'js/vendor/Chart.min.js',
	'js/vendor/angular-chart.js',
	'js/vendor/pusher.min.js',
	'js/bower/phoneapi/dist/js/pusher.js',
	'js/vendor/bootstrap-select.js',
	'js/vendor/jquery.simulate.js',
	'js/vendor/bs-slider.js',
	'js/vendor/ng-autocomplete.js'
];

css_files = [
	'css/vendor/bootstrap.css',
	'css/vendor/bootstrap-datepicker.min.css',
	'css/vendor/jquery.timepicker.css',
	'css/vendor/hint.css',
	'css/vendor/animate.css',
	'css/vendor/nprogress.css',
	'css/vendor/ng-showhide.css',
	'css/vendor/ios7switch.css',
	'css/vendor/ladda-themeless.css',
	'css/vendor/corner-morph.css',
	'js/bower/angular-bootstrap-colorpicker/css/colorpicker.min.css',
	'css/vendor/jquery.color-animation.css',
	'js/bower/cropper/dist/cropper.min.css',
	'js/bower/simple-hint/dist/simple-hint.css',
	'css/vendor/bootstrap-select.css',
	'css/vendor/bs-slider.css',
	'js/bower/phoneapi/dist/css/phone.css',
	'css/vendor/ng-autocomplete.css'
];

gulp.task('build-vendor', function() {
	gulp.src(vendor_files)
		.pipe(concat(config.bower.bundle))
		.pipe(gulp.dest(config.bower.dest));
});

gulp.task('sass', function () {
	gulp.src(config.sass.src)
		.pipe(sass())
		.pipe(addsrc(css_files))
		.pipe(concat(config.sass.bundle))
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

gulp.task('watch', ['default'], function() {
	browserSync.init(config.browsersync.options);

	gulp.watch(coffee_cnf.assets.src, ['assets']);
	gulp.watch(coffee_cnf.ngapp.src, ['ng-apps']);
	gulp.watch(config.sass.src, ['sass']);

	// gulp.watch(config.app.files, function() {
	// 	browserSync.reload();
	// });
});

gulp.task('default', ['assets', 'sass', 'ng-apps', 'build-vendor']);
