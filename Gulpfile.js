"use strict";

var gulp          = require("gulp"),
    buffer        = require("vinyl-buffer"),
    browserify    = require("browserify"),
    uglify        = require("gulp-uglify"),
    sass          = require("gulp-sass"),
    concat        = require("gulp-concat"),
    plumber       = require("gulp-plumber"),
    jshint        = require("gulp-jshint"),
    source        = require("vinyl-source-stream"),
    sourcemaps    = require("gulp-sourcemaps")
;

var paths = {
    src: {
        sass: "./assets/sass/calendar.scss",
        js: {
            app: "./assets/js/calendar.js",
            vendor: [
                "./node_modules/jquery/dist/jquery.min.js",
                "./node_modules/bootstrap-sass/assets/javascripts/bootstrap.min.js"
            ]
        },
        fonts: "./node_modules/font-awesome/fonts/*"
    },
    build: {
        css: "web/assets/css/",
        js: "web/assets/js/",
        fonts: "web/assets/fonts/"
    },
    watch: {
        sass:  "./assets/sass/**/*.scss",
        js: {
            app: "./assets/js/**/*.js",
            vendor: "./node_modules/**/*.js"
        }
    }
};

gulp.task("default", [ "watch" ]);
gulp.task("build", [ "js-deps", "js", "compile-sass", "fonts" ]);
gulp.task("watch", [ "build", "watch-js-deps", "watch-js", "watch-sass" ]);

gulp.task("compile-sass", function() {
    return gulp.src(paths.src.sass)
        .pipe(sourcemaps.init())
        .pipe(sass({
            outputStyle: 'compressed',
            includePaths: [
                "./node_modules/bootstrap-sass/assets/stylesheets",
                "./node_modules/normalize-scss/sass",
                "./node_modules/font-awesome/scss"
            ]
        }))
        .pipe(sourcemaps.write())
        .pipe(gulp.dest(paths.build.css))
    ;
});

gulp.task("watch-sass", function() {
    return gulp.watch(paths.watch.sass, function() {
        gulp.start("compile-sass");
    });
});

gulp.task("js-deps", function() {
    return gulp.src(paths.src.js.vendor)
        .pipe(plumber())
        .pipe(concat("vendor.js"))
        .pipe(sourcemaps.init())
        .pipe(uglify())
        .pipe(sourcemaps.write())
        .pipe(gulp.dest(paths.build.js))
    ;
});

gulp.task("watch-js-deps", function() {
    return gulp.watch(paths.src.js.vendor, function() {
        gulp.start("js-deps");
    });
});

gulp.task("js", function() {
    return browserify(paths.src.js.app)
        .bundle()
        .on("error", function(e) {
            console.log(e);
            this.emit("end");
        })
        .pipe(source("calendar.js"))
        .pipe(buffer())
        .pipe(sourcemaps.init({ loadMaps: true }))
        .pipe(uglify())
        .pipe(sourcemaps.write())
        .pipe(gulp.dest(paths.build.js))
    ;
});

gulp.task("watch-js", function() {
    return gulp.watch(paths.watch.js.app, function() {
        gulp.start("js");
    });
});

gulp.task("fonts", function() {
    return gulp.src(paths.src.fonts)
        .pipe(gulp.dest(paths.build.fonts))
    ;
});
