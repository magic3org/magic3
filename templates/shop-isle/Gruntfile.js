// jshint node:true

module.exports = function( grunt ) {
    'use strict';

    var loader = require( 'load-project-config' ),
        config = require( 'grunt-theme-fleet' );
    config = config();
    config.files.js.push( 'assets/js/*.js', '!assets/js/vendor/**/*.js', '!assets/bootstrap/js/**/*.js', '!grunt/**/*.js', '!inc/customizer/customizer-repeater/**/*.js', '!inc/customizer/customizer-alpha-color-picker/js/alpha-color-picker.js' );
    config.files.css.push( 'assets/css/*.css', '!assets/css/vendor/**/*.css', '!assets/bootstrap/css/**/*.css' );
    config.files.php.push( '!class-tgm-plugin-activation.php', '!inc/customizer/customizer-repeater/class/customizer-repeater-control.php', '!inc/customizer/customizer-repeater/functions.php', '!inc/customizer/customizer-range-value-control/class-customizer-range-value-control.php', '!inc/customizer/customizer-repeater/inc/customizer.php' );

    //Add Copy Task for ShopIsle screenshot
    config.taskMap.copy = 'grunt-contrib-copy';

    //Add Version Task for ShopIsle versioning
    config.taskMap.version = 'grunt-version';

    loader( grunt, config ).init();
};
