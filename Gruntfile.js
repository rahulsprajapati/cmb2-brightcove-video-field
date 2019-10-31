'use strict';
module.exports = function ( grunt ) {

	grunt.initConfig( {
		// Make POT file - Internationalize WordPress plugins.
		// Ref. https://www.npmjs.com/package/grunt-wp-i18n
		makepot: {
			cmb2BrightcoveVideoField: {
				options: {
					cwd: './', // Directory of files to internationalize.
					domainPath: 'languages/', // Where to save the POT file.
					exclude: [ 'node_modules/*', '.phpintel/*' ], // List of files or directories to ignore.
					mainFile: 'plugin.php', // Main project file.
					potFilename: 'cmb2-brightcove-video-field.pot', // Name of the POT file.
					potHeaders: { // Headers to add to the generated POT file.
						poedit: true, // Includes common Poedit headers.
						'Last-Translator': 'Rahul Prajapati <rahul.prajapati@live.in>',
						'Language-Team': 'Dev',
						'report-msgid-bugs-to': 'https://github.com/rahulsprajapati/cmb2-brightcove-video-field/issues',
						'x-poedit-keywordslist': true, // Include a list of all possible gettext functions.
					},
					type: 'wp-plugin', // Type of project (wp-plugin or wp-theme).
					updateTimestamp: true, // Whether the POT-Creation-Date should be updated without other changes.
				},
			},
		},

	} );

	grunt.loadNpmTasks( 'grunt-wp-i18n' );

	// Register task
	grunt.registerTask( 'default', [ 'makepot' ] );
};
