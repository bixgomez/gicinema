// Requires Gulp v4.
// $ npm uninstall --global gulp gulp-cli
// $ rm /usr/local/share/man/man1/gulp.1
// $ npm install --global gulp-cli
// $ npm install

const PROJECT_URL   = 'https://gicinema.local';
const ROOT          = './';
const STYLES_MAIN   = './sass/style.scss';
const STYLES_ADMIN  = './sass/admin.scss';
const STYLES_SOURCE = './sass/**/*.scss';
const STYLES_DEST   = ROOT;
const JS_SOURCE     = './assets/js/src/**/*.js';
const JS_DEST       = './assets/js/';
const ALL_PHP       = './**/*.php';

const { src, dest, watch, series, parallel } = require('gulp');
const browsersync = require('browser-sync').create();
const sass = require('gulp-sass')(require('sass'));
const autoprefixer = require('gulp-autoprefixer');
const sourcemaps = require('gulp-sourcemaps');
const plumber = require('gulp-plumber');
const sasslint = require('gulp-sass-lint');
const cache = require('gulp-cached');
const notify = require('gulp-notify');
// const beeper = require('beeper');
const sassGlob = require('gulp-sass-glob');

// Compile CSS from Sass.
function buildStyles() {
  return src( STYLES_MAIN, STYLES_ADMIN )
    .pipe(plumbError()) // Global error handler through all pipes.
    .pipe(sourcemaps.init())
    .pipe(sassGlob())
    .pipe(sass({
      includePaths: [
        './node_modules/breakpoint-sass/stylesheets/',
        './node_modules/@fortawesome/fontawesome-free/scss'
      ],
      errLogToConsole: true,
      outputStyle: 'compressed'
    }))
    .pipe(autoprefixer(['last 15 versions', '> 1%', 'ie 8', 'ie 7']))
    .pipe(sourcemaps.write())
    .pipe(dest( STYLES_DEST ))
    .pipe(browsersync.reload({ stream: true }));
}

// Watch changes on all *.scss files, lint them and
// trigger buildStyles() at the end.
function watchFilesOld() {
  watch(
    ['sass/*.scss', 'sass/**/*.scss'],
    { events: 'all', ignoreInitial: false },
    series(sassLint, buildStyles)
  );
}

function watchFiles() {
  watch( STYLES_SOURCE, buildStyles );
  // watch( JS_SOURCE, scriptsJS );
  watch( ALL_PHP, reload );
}

// Init BrowserSync.
function browserSync(done) {
  browsersync.init({
    proxy: PROJECT_URL,
    socket: {
      domain: 'localhost:3000'
    }
  });
  done();
}

function reload(done) {
  browsersync.reload();
  done();
}

// Init Sass linter.
function sassLint() {
  return src(['sass/*.scss', 'sass/**/*.scss'])
    .pipe(cache('sasslint'))
    .pipe(sasslint({
      configFile: '.sass-lint.yml'
    }))
    .pipe(sasslint.format())
    .pipe(sasslint.failOnError());
}

// Error handler.
function plumbError() {
  return plumber({
    errorHandler: function(err) {
      notify.onError({
        templateOptions: {
          date: new Date()
        },
        title: "Gulp error in " + err.plugin,
        message:  err.formatted
      })(err);
      // beeper();
      this.emit('end');
    }
  })
}

// Export commands.
exports.default = parallel(browserSync, watchFiles); // $ gulp
exports.sass = buildStyles; // $ gulp sass
exports.watch = watchFiles; // $ gulp watch
exports.build = series(buildStyles); // $ gulp build
