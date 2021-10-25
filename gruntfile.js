module.exports = function(grunt) {
	require("load-grunt-tasks")(grunt);

	// grun tasks
	grunt.initConfig({
		pkg: grunt.file.readJSON("package.json"),
		config: {
			js: "build/minify-compare.js",
		},
		rollup: {
			es6: {
				options: {
					format: "es",
					sourcemap: true
				},
				src: "src/javascript/compare.js",
				dest: "<%= config.js %>"
			}
		},
		babel: {
			es6: {
				files: {
					"<%= config.js %>": "<%= config.js %>"
				},
				options: {
					sourceMap: true,
					presets: [
						["minify", {mangle: {exclude: ["$"], topLevel: true}}]
					],
					comments: false
				}
			}
		},
		watch: {
			js: {
				files: ["src/**/*.js"],
				tasks: ["rollup:es6"]
			},
			gruntfile: {
				files: ["gruntfile.js", "package.json"],
				tasks: ["rollup:es6"]
			}
		}
	});

	grunt.registerTask("default", ["rollup", "babel"]);
};
