var gulp = require('gulp');
var sass = require("gulp-ruby-sass");
var sftp = require("gulp-sftp");

gulp.task('default', function() {
    gulp.watch("./sass/**/*.scss", ["sass"]);
    gulp.watch("./www/css/**/*.css", ["css-upload"]);
});

gulp.task('sass', function() {
    return sass('./sass/**/*.scss', { compass: true })
        .on('error', sass.logError)
        .pipe(gulp.dest('./www/css'));
});

gulp.task('css-upload', function(){
    var host_conf = require("./config/host.json");
    return gulp.src('www/css/**/*.css')
        .pipe(sftp({
            host: host_conf.test.host,
            user: host_conf.test.user,
            remotePath: '/var/www/html/www/css/'
        }));
});
