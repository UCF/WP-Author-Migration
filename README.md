# WP Author Migration #

Set of utilities for migrating authors from one site to another and remapping posts with the appropriate author IDs.


## Description ##

The WP Author Migration tool provides a set of wp-cli and GUI utilities for matching authors exported from one system and imported into another using the [All-in-One WP Migration plugin](https://wordpress.org/plugins/all-in-one-wp-migration/).

# The Challenge #

When importing a site using the All-in-One WP Migration plugin, it is common for author IDs to be updated, especially when migrating a single site into a multisite environment. While the user accounts are imported, the IDs will no longer match the `author_id` set on each post, causing the posts to be mapped to the wrong authors or no author at all.

# Our Solution #

This plugin provides tools for exporting author data to a JSON file, and then using that file to create a mapping between authors on the old site and the new. That author map is then used to update the posts on the new site to point to the correct authors.

# Basic Usage #

The basic usage of the command is as follows:

```
wp wpam migrate <path-or-url-to-file> [--default-author=<ID,username,email>] [--set-default=<true/false>]
```

More detailed information on the parameters can be found [on the WP Author Migration wiki](https://github.com/UCF/WP-Author-Migration/wiki#running-the-command).

## Documentation ##

Head over to the [WP Author Migration wiki](https://github.com/UCF/WP-Author-Migration/wiki) for detailed information about this plugin, installation instructions, and more.


## Changelog ##

### 1.0.2 ###
Documentation:
* Updated contributing doc.

### 1.0.1 ###
Enhancements:
* Updated a direct `file_get_contents()` call in `WPAM_Author_Migrate`'s constructor to use the unused `get_local_file()` method, which has been updated to verify that the provided path is a valid file on the system.

### 1.0.0 ###
* Initial release


## Upgrade Notice ##

n/a


## Development ##

[Enabling debug mode](https://codex.wordpress.org/Debugging_in_WordPress) in your `wp-config.php` file is recommended during development to help catch warnings and bugs.

### Requirements ###
* node
* gulp-cli
* wp-cli

### Instructions ###
1. Clone the WP-Author-Migration repo into your local development environment, within your WordPress installation's `plugins/` directory: `git clone https://github.com/UCF/WP-Author-Migration.git`
2. `cd` into the new WP-Author-Migration directory, and run `npm install` to install required packages for development into `node_modules/` within the repo
3. Optional: If you'd like to enable [BrowserSync](https://browsersync.io) for local development, or make other changes to this project's default gulp configuration, copy `gulp-config.template.json`, make any desired changes, and save as `gulp-config.json`.

    To enable BrowserSync, set `sync` to `true` and assign `syncTarget` the base URL of a site on your local WordPress instance that will use this plugin, such as `http://localhost/wordpress/my-site/`.  Your `syncTarget` value will vary depending on your local host setup.

    The full list of modifiable config values can be viewed in `gulpfile.js` (see `config` variable).
3. Run `gulp default` to process front-end assets.
4. If you haven't already done so, create a new WordPress site on your development environment to test this plugin against.
5. Activate this plugin on your development WordPress site.

### Other Notes ###
* This plugin's README.md file is automatically generated. Please only make modifications to the README.txt file, and make sure the `gulp readme` command has been run before committing README changes.  See the [contributing guidelines](https://github.com/UCF/WP-Author-Migration/blob/master/CONTRIBUTING.md) for more information.


## Contributing ##

Want to submit a bug report or feature request?  Check out our [contributing guidelines](https://github.com/UCF/WP-Author-Migration/blob/master/CONTRIBUTING.md) for more information.  We'd love to hear from you!
