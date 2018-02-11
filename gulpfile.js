// GULP TASKS

// include gulp
var gulp = require('gulp');

// include plugins
var babel = require('gulp-babel');
var cleanCSS = require('gulp-clean-css');
var concat = require('gulp-concat');
var rename = require('gulp-rename');
var sass = require('gulp-sass');
var uglify = require('gulp-uglify');
var util = require('gulp-util');

// base paths
var paths = {
	src: {
		js: 'src/js/',
		scss: 'src/scss/',
		icons: 'src/icons/fonts/'
	},
	dist: {
		js: 'dist/',
		css: 'dist/',
		icons: 'dist/fonts/'
	}
};

// JS build file lists
var jsFiles = {
	vendor: [
		// 3rd party libs
		'node_modules/clipboard/dist/clipboard.js',
	],
	app: [
		// fileDB lib
		paths.src.js + 'main.js'
	]
};


// compile sass
gulp.task('sass', function() {
	return gulp.src(paths.src.scss + 'main.scss')
		.pipe(sass())
		.pipe(rename('main.css'))
		//.pipe(gulp.dest(paths.dist.css))
		//.pipe(rename('main.min.css'))
		.pipe(cleanCSS())
		.pipe(gulp.dest(paths.dist.css))
		.on('error', util.log);
});

// copy icons
gulp.task('icons', function() {
	return gulp.src(paths.src.icons + '*')
		.pipe(gulp.dest(paths.dist.icons))
		.on('error', util.log);
});

// concatenate & minify vendor JS
gulp.task('scripts:vendor', function() {
	return gulp.src(jsFiles.vendor)
		.pipe(concat('vendor.js'))
		.pipe(gulp.dest(paths.dist.js))
		.on('error', util.log);
});

// build app-specific JS
gulp.task('scripts:app', function() {

	return gulp.src(jsFiles.app)
		.pipe(babel())
		.pipe(concat('app.js'))
		.pipe(gulp.dest(paths.dist.js))
		.on('error', util.log);
});

gulp.task('scripts:build', ['scripts:vendor', 'scripts:app'], function() {
	return gulp.src([paths.dist.js + 'vendor.js', paths.dist.js + 'app.js'])
		.pipe(concat('main.js'))
		// .pipe(uglify())
		.pipe(gulp.dest(paths.dist.js))
		.on('error', util.log);
});

gulp.task('scripts:app-watch', ['scripts:app'], function() {
	return gulp.src([paths.dist.js + 'vendor.js', paths.dist.js + 'app.js'])
		.pipe(concat('main.js'))
		.pipe(gulp.dest(paths.dist.js))
		.on('error', util.log);
});

// full JS build
gulp.task('scripts:default', ['scripts:vendor', 'scripts:app', 'scripts:build']);

// watch files for changes
// for JS, must run a single full build to create vendor file
gulp.task('watch', function() {
	gulp.watch(paths.src.js + '**/*.js', ['scripts:app-watch']);
	gulp.watch(paths.src.scss + '**/*.scss', ['sass']);
});

// Default Task
gulp.task('default', ['icons', 'sass', 'scripts:default']);
