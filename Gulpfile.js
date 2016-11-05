"use strict";

var gulp          = require("gulp"),
    buffer        = require("vinyl-buffer"),
    uglify        = require("gulp-uglify"),
    sass          = require("gulp-sass"),
    concat        = require("gulp-concat"),
    plumber       = require("gulp-plumber"),
    jshint        = require("gulp-jshint"),
    source        = require("vinyl-source-stream"),
    sourcemaps    = require("gulp-sourcemaps"),
    templateCache = require("gulp-angular-templatecache"),
    ngAnnotate    = require("gulp-ng-annotate")
;

var paths = {
    src: {
        sass: "./assets/sass/calendar.scss",
        js: {
            app: "./assets/js/**/*.js",
            vendor: [
                "./node_modules/angular/angular.min.js",
                "./node_modules/angular-ui-router/release/angular-ui-router.min.js",
                "./node_modules/angular-ui-bootstrap/dist/ui-bootstrap-tpls.js",
                "./node_modules/moment/min/moment.min.js",
                "./node_modules/angular-moment/angular-moment.min.js"
            ]
        },
        fonts: "./node_modules/font-awesome/fonts/*",
        templates: "./assets/js/**/*.html"
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
        },
        templates: "./assets/js/**/*.html"
    }
};

gulp.task("default", [ "watch" ]);
gulp.task("build", [ "js-deps", "js", "compile-sass", "fonts", "templates" ]);
gulp.task("watch", [ "build", "watch-js-deps", "watch-js", "watch-sass", "watch-templates" ]);

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
        .pipe(ngAnnotate())
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
    return gulp.src(paths.src.js.app)
        .pipe(jshint())
        .pipe(jshint.reporter())
        .pipe(jshint.reporter("fail"))
        .on("error", function(err) {
            console.log(err);
            this.emit("end");
        })
        .pipe(sourcemaps.init({ loadMaps: true }))
        .pipe(ngAnnotate())
        .pipe(uglify())
        .pipe(concat("calendar.js"))
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

gulp.task("watch-templates", function() {
    return gulp.watch(paths.watch.templates, function() {
        gulp.start("templates");
    });
});

gulp.task("templates", function() {
    return gulp.src(paths.src.templates)
        .pipe(templateCache({root: "calendar/"}))
        .pipe(gulp.dest(paths.build.js))
    ;
});

