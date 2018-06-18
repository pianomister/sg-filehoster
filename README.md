# SG Filehoster
Simple PHP library to run your own filehoster.

## Features
- Clean web UI included that allows to upload, access and manage files.
- Several obfuscation and low security mechanisms available to hide and protect your files.
- Session-based access control to prevent direct deep-linking to files.
- Several configuration parameters to customize the script and UI.
- No database needed, everything is stored in files.

## Requirements
- PHP 7.0+
- Composer
- npm (node)

## Installation
To install the filehoster library, run `composer install` in the root directory of this project to install all PHP library dependencies.
To install the UI, you need to run `npm install` and then `npm run build-prod` in the root directory to generate CSS and JS files inside the dist folder, which will be used by the UI.

After installation of dependencies, adjust the setup of this library according to your needs in `lib/constants.php`. You should change the salt used for passwords, and set a username and generate a password for the admin user, in case you would like to use the admin panel.
For both, you can use the helper script `lib/pwgenerator.php`, which generates a random salt and password hashes for you.

Finally, upload all files to your webspace, and start using it! You may omit the package and build config files when uploading the library.

### Wish list
- statistics for downloads / files
