# CannaBuilder

## Table of Contents

- [Build Script](#markdown-header-build-script)
	- [Overview](#markdown-header-overview)
	- [Production and Development Environments](#markdown-header-production-and-development-environments)
		- [Dev Tasks](#markdown-header-dev-tasks)
		- [Prod Tasks](#markdown-header-prod-tasks)
	- [Gulp Watch](#markdown-header-gulp-watch)
- [Plugins](#markdown-header-plugins)
	- [Gravity Forms](#markdown-header-gravity-forms)
		- [Columns and Field Sizes](#markdown-header-columns-and-field-sizes)
- [Setup Guide](#markdown-header-setup-guide)
	- [SASS Variables](#markdown-header-sass-variables)
	- [Buttons](#markdown-header-buttons)
	- [TinyMCE Styles](#markdown-header-tinymce-styles)
	- [Menus](#markdown-header-menus)
	- [Fonts](#markdown-header-fonts)
	- [Modules](#markdown-header-modules)

## Build Script

### Overview

This theme uses Gulp 4 as a build script. The build script is located in `gulpfile.js` and all dependencies are declared in `package.json`. We suggest installing dependencies by running `yarn install`, if you prefer to use `npm` dependencies can be installed by running `npm install`.

### Production and Development Environments

The script has two environments, production and development. This can be set in `gulpfile.js` under the `env` variable. While in development you can set the `env` variable to `dev`. It is good practice to set the `env` variable to `prod` during launch, this way if someone else pulls down the theme and runs `gulp` all scripts and css will be minified. The `env` variable can be overridden at any time by running `gulp prod` for production or `gulp dev` for development.

#### Dev Tasks

- CSS
	- Compile Sass
	- Autoprefixer
	- Generate Source Maps
- JS
	- Move files from `src/js` to `dist/js`

#### Prod Tasks

- CSS
	- Compile Sass
	- Autoprefixer
	- Minify CSS
	- Generate Source Maps
- JS
	- Minify JS files


### Gulp Watch

Run `gulp watch` to fire up [Browersync](https://www.browsersync.io/) and watch CSS, JS, and PHP files for changes. Gulp looks to a `.env` file to set your dev URL. This `.env` file is git ignored so each developer can easily have his own unique dev URL. The `.env` file should have a variable `PROJECT_URL` set to your dev URL. To set up your `.env` file edit and rename `.env-sample`

## Plugins

CannaBuilder includes styling for Gravity Forms, Events Calendar Pro, and WooCommerce

### Gravity Forms

In order for the styling to look correct the following Gravity Forms setting should be set:

- Global Settings 
	- Output CSS - No
	- Output HTML5 - Yes

#### Columns and Field Sizes

In the appearance menu of a field you will see a size drop down for the following fields: text, textarea, select, multiselect, phone, website, option, and product. Choosing "Small" uses the `col-lg-4` selector resulting in 3 columns. Choosing "Medium" uses the `col-lg-6` selector resulting in 2 columns. Choosing "Large" uses the `col-lg-12` selector resulting in one column. If you need to create a custom layout you can override these sizes by entering your own CSS selector in the the "Custom CSS Class" field. 

If you would like a field to be on its own line no matter the "Field Size" chosen check the "Field on own line" field in the "Appearance" menu.

Any field that does not support the "Field Size" can still be changed by using a CSS selector in the "Custom CSS Class" field.

## Setup Guide

### SASS Variables

Setup your project variables in `src/sass/base/_variables`. Remove any colors that are not being used. You may have to change a few variable names in some of the other SASS partials if you remove a color.

### Buttons

Remove any buttons that are not being used in `src/sass/base/_buttons.scss`.

### TinyMCE Styles

Remove any buttons that are not being used in `inc/filters-and-actions/tiny-mce.php`

### Menus

Menus are setup to allow user to select a button style or an image for each menu item. If these features aren't being used remove them in the ACF interface. If buttons are being used remove the ones that the user should not be able to select (the ones that are not styled out)

### Fonts

Remove or update TypeKit code in `header.php`. If Google Fonts is being used update that in `inc/enqueue-scripts.php`

### Modules

Remove all modules that are not being used in the ACF interface. Remove references to them in `template-parts/content-page-builder.php` and `inc/enqueue-scripts.php`. Also delete the template-part and SASS partial for each module that is not being used.


