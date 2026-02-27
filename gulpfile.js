const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const autoprefixer = require('gulp-autoprefixer');
const browserSync = require('browser-sync').create();

// Only compile entry SCSS files (they @use partials). Avoids duplicate output from partials.
const SCSS_ENTRIES = [
  'library/scss/style.scss',
  'library/scss/editor-style.scss',
  'library/scss/login.scss',
  'library/scss/admin.scss'
];

function styles() {
  return gulp
    .src(SCSS_ENTRIES)
    .pipe(sass({ includePaths: ['library/scss'] }).on('error', sass.logError))
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

function stylesProduction() {
  const cleanCSS = require('gulp-clean-css');
  return gulp
    .src(SCSS_ENTRIES)
    .pipe(sass({ includePaths: ['library/scss'], outputStyle: 'compressed' }).on('error', sass.logError))
    .pipe(autoprefixer({ overrideBrowserslist: ['last 2 versions'], cascade: false }))
    .pipe(cleanCSS({ level: 1 }))
    .pipe(gulp.dest('library/css'));
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
gulp.task('build', gulp.series(styles));
gulp.task('build:production', stylesProduction);
gulp.task('watch', gulp.series(styles, serve));
