var gulp = require('gulp');
var sass = require("gulp-ruby-sass");
var sftp = require("gulp-sftp");
var spritesmith = require("gulp.spritesmith");

gulp.task('default', function() {
    gulp.watch("./sass/**/*.scss", ["sass"]);
    gulp.watch("./www/css/**/*.css", ["css-upload"]);
    gulp.watch("./www/img/**/*", ["img-upload"]);
});

//scss -> css変換
gulp.task('sass', function() {
    return sass('./sass/**/*.scss', { compass: true })
        .on('error', sass.logError)
        .pipe(gulp.dest('./www/css'));
});

//cssスプライト画像と対応scss作成
//https://www.npmjs.com/package/gulp.spritesmith
//http://blog.e-riverstyle.com/2014/02/gulpspritesmithcss-spritegulp.html
gulp.task('sprite', function(){
    var spriteData = gulp.src('./images/*.png')
        .pipe(spritesmith({
            imgName: 'sprite.png',
            cssName: '_sprite.scss',
            imgPath: '/img/sprite.png',
            //cssFormat: 'scss',//この指定があるとscssにretina用の記述が追加されないらしい。参考：https://tech.recruit-mp.co.jp/front-end/post-6844/
            retinaSrcFilter: './images/*@2x.png',
            retinaImgName: 'sprite@2x.png',
            retinaImgPath: '/img/sprite@2x.png'
        }));
    spriteData.img.pipe(gulp.dest('./www/img/'));
    spriteData.css.pipe(gulp.dest('./sass/'));
});

//cssのアップロード
gulp.task('css-upload', function(){
    var host_conf = require("./config/host.json");
    return gulp.src('www/css/**/*.css')
        .pipe(sftp({
            host: host_conf.test.host,
            user: host_conf.test.user,
            remotePath: '/var/www/html/www/css/'
        }));
});

//imgのアップロード
gulp.task('img-upload', function(){
    var host_conf = require("./config/host.json");
    return gulp.src('www/img/**/*')
        .pipe(sftp({
            host: host_conf.test.host,
            user: host_conf.test.user,
            remotePath: '/var/www/html/www/img/'
        }));
});
