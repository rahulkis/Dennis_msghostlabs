## Setup Environment Variable

Copy .env-sample file under setup-wizard root dir and rename it as .env.
Replace DOMAIN value with your dev site url.

Add the following in your themes function.php file

```
define('WWOF_DEV', true);

//  NEEDED FOR DEVELOPING WWOF V2
add_action('init', function () {
    // If developing under cra dev server
    header("Access-Control-Allow-Origin: *");
});
```

The setup_wizard_options wp_localize_script variable is located in public/index.html

# Getting Started with Create React App

This project was bootstrapped with [Create React App](https://github.com/facebook/create-react-app).

## Available Scripts

In the project directory, you can run:

### `yarn start`

Runs the app in the development mode.\
Open [http://localhost:3000](http://localhost:3000) to view it in the browser.

The page will reload if you make edits.\
You will also see any lint errors in the console.

### `yarn build`

Builds the app for production to the `build` folder.\
It correctly bundles React in production mode and optimizes the build for the best performance.

The build is minified and the filenames include the hashes.\
Your app is ready to be deployed!

See the section about [deployment](https://facebook.github.io/create-react-app/docs/deployment) for more information.