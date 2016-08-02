var gulp = require('gulp');
var sass = require("gulp-ruby-sass");

gulp.task('default', function() {
    gulp.watch("./sass/**/*.scss", ["sass"]);
});

gulp.task('sass', function() {
    return sass('./sass/**/*.scss')
        .on('error', sass.logError)
        .pipe(gulp.dest('./www/css'));
});
