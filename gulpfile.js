const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const autoprefixer = require('gulp-autoprefixer');
const browserSync = require('browser-sync').create();

function styles() {
  return gulp
    .src('library/scss/**/*.scss')
    .pipe(sass().on('error', sass.logError))
    .pipe(
      autoprefixer({
        overrideBrowserslist: [
          'last 2 versions',
          'safari 5',
          'ie 9',
          'opera 12.1',
          'ios 6',
          'android 4'
        ],
        cascade: false
      })
    )
    .pipe(gulp.dest('library/css'))
    .pipe(browserSync.stream());
}

function serve() {
  browserSync.init({
    port: 8000,
    proxy: 'http://localhost:8000'
  });

  gulp.watch('library/scss/**/*.scss', styles);
  gulp.watch('./*.php').on('change', browserSync.reload);
}

gulp.task('sass', styles);
gulp.task('watch', gulp.series(styles, serve));
