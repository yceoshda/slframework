var gulp = require('gulp'),
    livereload = require('gulp-livereload'),
    exec = require('child_process').exec,
    child;

gulp.task('default', function() {
  // place code for your default task here
});

gulp.task('watch', function() {
    var server = livereload();
    gulp.watch('./www/**').on('change', function(file) {
        server.changed(file.path);
        console.log("File changed: " + file.path);
    });

    gulp.watch('./design/sass/*.scss').on('change', function(file) {
        console.log("SCSS changed: " + file.path + " compiling");
        child = exec('compass compile', function(error, stdout, stderr){
            console.log('stdout: ' + stdout);
            console.log('stderr: ' + stderr);
            // if (error !== null) {
                console.log('Compilation done');
            // } else {
                // console.log('Failed to compile SCSS');
            // };
        });
    });
});