module.exports = function (grunt) {
	var js_files = [
		'js/dashboard.js',
		'js/email_accounts.js',
		'js/email_conversation.js',
		'js/permissions.js',
		'js/predefined-replies.js',
		'js/preferences.js',
		'js/respondents-autocomplete.js',
		'js/statistics.js',
		'js/ticket_attachments.js',
		'js/tickets.js',
		'js/auto_save.js',
	];
	var css_files = [
		'css/admin.css',
		'css/dashboard.css',
	];

	grunt.initConfig({
		uglify: {
			options: {
				banner: '/*! SupportFlow minified version */\n'
			},
			build  : {
				src : js_files,
				dest: 'js/supportflow.min.js'
			}
		},
		cssmin: {
			options: {
				banner: '/*! SupportFlow minified version */\n'
			},
			build  : {
				src : css_files,
				dest: 'css/supportflow.min.css'
			}
		},
		watch : {
			scripts: {
				files: js_files,
				tasks: ['uglify'],
			},
			styles : {
				files: css_files,
				tasks: ['cssmin'],
			},
		},
	});

	// Load the required plugins.
	grunt.loadNpmTasks('grunt-contrib-uglify');
	grunt.loadNpmTasks('grunt-contrib-watch');
	grunt.loadNpmTasks('grunt-contrib-cssmin');

	// Running these tasks by default
	grunt.registerTask('default', ['uglify', 'cssmin']);

};