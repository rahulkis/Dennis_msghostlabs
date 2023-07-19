/*
 |--------------------------------------------------------------------------
 | Imports
 |--------------------------------------------------------------------------
 */

var dotenv = require('dotenv').config();
var gulp = require('gulp');
var browserSync = require('browser-sync').create();
var notify = require('gulp-notify');
var del = require('del');
var path = require('path');

/*
 * Style specific imports
 */
var sass = require('gulp-sass')(require('sass'));
var sourcemaps = require('gulp-sourcemaps');
var postcss = require('gulp-postcss');
var autoprefixer = require('autoprefixer');
var cssnano = require('cssnano');

/*
 * Script specific imports
 */
var uglify = require('gulp-uglify');
var concat = require('gulp-concat');

/*
 |--------------------------------------------------------------------------
 | Helper Functions
 |--------------------------------------------------------------------------
 */

/**
 * Convert camelCase to dashed-case
 * @param {String} str 
 */
function camelCaseToDash (str) {
	return str.replace(/([a-zA-Z])(?=[A-Z])/g, '$1-').toLowerCase();
}

/**
 * Get a random emoji for the build
 * @param {String} status 
 */
function randomEmoji (status) {
	var emojis;

	if (status == 'fail') {
		emojis = ['💩', '🥑😰','🙁','👎','❌'];
	} else {
		emojis = ['💯', '👍','😎','⭐️','🍻','😸','🤘','🐬','🔥','⚡️','🌈','⛄️','🚴'];
	}
	return emojis[Math.floor(Math.random()*emojis.length)];
}

/*
 |--------------------------------------------------------------------------
 | Config
 |--------------------------------------------------------------------------
 | Define a few config variables
 |
 */

/*
 * Default Environment
 * Options are 'dev' or 'prod'. You can override this by running `gulp prod` or `gulp dev`
 */
var env = 'dev';

/**
 * Create a .env file in your root directory and add PROJECT_URL=yourprojecturl.dev
 * If no .env file exists localhost will be used. The .env file is git ignored so this
 * allows each developer to have his own dev url setup for each project
 */
var projectURL = process.env.PROJECT_URL ? process.env.PROJECT_URL : false;

/**
 * Project Paths
 */
var paths = {
	styles: {
		src: './src/sass/**/*.scss',
		dest: './dist/css',
		watch: './src/sass/**/*.scss'
	},
	scripts: {
		src: './src/js/',
		dest: './dist/js/',
		watch: './src/js/**/*.js'
	},
	php: {
		watch: './**/*.php'
	}
};

/*
 * Browsers you care about for autoprefixing.
 */
var autoprefixerBrowsers = [
	'last 4 versions'
];

/**
 * Define an array of JS files to be concatenated into the main JS file
 */
var mainScripts = [
	'widowfix.js',
	'uri.js',
	'dropdown.js',
	'clipboard.js',
	'smooth-scroll.js',
	'skip-link-focus-fix.js',
	'cp-navigation.js',
	'cp-blog.js',
	'tablesaw.js',
	'modal.js',
	'cp-modal.js',
	'app.js'
];

/**
 * Define an array of modules to be concatenated into their own JS bundle
 * Key for module should be folder name converted to camelCase
 * Example: gallery-masonry should be galleryMasonry
 */
var modules = {
	accordion: [
		'accordion.js',
		'cp-accordion.js'
	],
	gallery: [
		'photoswipe.js',
		'photoswipe-ui-default.js',
		'cp-gallery.js'
	],
	galleryMasonry: [
		'imagesloaded.js',
		'masonry.js',
		'cp-gallery-masonry.js'
	],
	googleMap: [
		'cp-google-map.js'
	],
	slider: [
		'swiper.js',
		'cp-slider.js'
	],
	tabs: [
		'tab.js',
		'cp-tabs.js'
	]
};

/*
 |--------------------------------------------------------------------------
 | Styles
 |--------------------------------------------------------------------------
 | 1) Compile Sass to css
 | 2) Autoprefixer
 | 3) Minify css if production env
 | 4) Sourcemaps
 |
 */

function styles() {
	//PostCSS Plugins
	var plugins = [
		autoprefixer(autoprefixerBrowsers),
	];

	//If production environment use cssnano
	if (env == 'prod') {
		plugins.push(cssnano({zindex: false}));
	}

	return gulp.src(paths.styles.src)
		.pipe(sourcemaps.init())
		.pipe(sass())
		.on('error', function(err) {
			err.message = randomEmoji('fail') + ' ' + err.message;
			notify().write(err);
			this.emit('end');
		})
		.pipe(postcss(plugins))
		.pipe(sourcemaps.write('/'))
		.pipe(gulp.dest(paths.styles.dest))
		.pipe(notify({
			message: randomEmoji('success') + ' Build Complete',
			onLast: true,
			contentImage: path.resolve(__dirname, 'dist/img/build-script-icon.png')
		}))
		.pipe(browserSync.stream());
}

/*
 |--------------------------------------------------------------------------
 | JavaScript
 |--------------------------------------------------------------------------
 |
 */

/**
 * Process main scripts
 */
function processMainScripts() {
	var mainScriptsFullPath = mainScripts.map(function (script) {
		return paths.scripts.src + script;
	});

	//If production environment minify JS else move js files from src to dist
	if (env == 'prod') {
		gulp.src(mainScriptsFullPath)
			.pipe(concat('app.js'))
			.pipe(uglify())
			.on('error', console.error.bind(console))
			.pipe(gulp.dest(paths.scripts.dest));
	} else {
		gulp.src(mainScriptsFullPath)
			.pipe(concat('app.js'))
			.on('error', console.error.bind(console))
			.pipe(gulp.dest(paths.scripts.dest));
	}
}

/**
 * Process module scripts
 */
function processModuleScripts() {
	for (var module in modules) {
		var moduleName = camelCaseToDash(module);
		var scripts = modules[module].map(function (script) {
			return paths.scripts.src + 'modules/' + moduleName + '/' + script;
		});

		//If production environment minify JS else move js files from src to dist
		if (env == 'prod') {
			gulp.src(scripts)
				.pipe(concat(moduleName + '.js'))
				.pipe(uglify())
				.on('error', console.error.bind(console))
				.pipe(gulp.dest(paths.scripts.dest));
		} else {
			gulp.src(scripts)
				.pipe(concat(moduleName + '.js'))
				.on('error', console.error.bind(console))
				.pipe(gulp.dest(paths.scripts.dest));
		}
	}
}

function scripts(done) {
	processMainScripts();
	processModuleScripts();

	browserSync.reload();
	done();
}

/*
 |--------------------------------------------------------------------------
 | Watch
 |--------------------------------------------------------------------------
 |
 */

function watch() {
	if (!projectURL) {
		var err = new Error(randomEmoji('fail') + 'No .env file exists, please add a .env file in theme root with PROJECT_URL=yourprojecturl.dev');
		notify().write(err);
	} else {
		browserSync.init({
			proxy: projectURL,
			notify: {
				styles: {
					top: 'auto',
					bottom: '0',
					margin: '0px',
					position: 'fixed',
					zIndex: '9999',
					borderRadius: '5px 0px 0px',
					color: 'white',
					backgroundColor: 'rgba(76,225,170,1)'
				}
			}
		});

		gulp.watch(paths.scripts.watch, scripts);
		gulp.watch(paths.styles.watch, styles);
		gulp.watch(paths.php.watch).on('change', browserSync.reload);
	}
}

/*
 |--------------------------------------------------------------------------
 | Define tasks
 |--------------------------------------------------------------------------
 |
 */

var tasks = gulp.parallel(styles, scripts);

/*
 * Dev Build
 */
gulp.task('dev', function (done) {
	env = 'dev';
	tasks();
	done();
});

/*
 * Production Build
 */
gulp.task('prod', function (done) {
	env = 'prod';
	tasks();
	done();
});

/*
 * Watch files
 */
gulp.task('watch', function (done) {
	env = 'dev';
	tasks(function() {
		watch();
	});
	done();
});

/*
 * Default Tasks
 */
gulp.task('default', tasks);
