<?php
/**
 * WP CLI Command to run the author migration
 */
if ( ! class_exists( 'WPAM_WP_CLI_Author_Migrate' ) ) {
	class WPAM_WP_CLI_Author_Migrate {
		/**
		 * Updates post_author IDs imported from one WordPress instance to another.
		 *
		 * ## Options
		 *
		 * <author_map>
		 * : A file or URL of an author output file. This output file should be the JSON output from wp user list.
		 *
		 * [--default-author=<default-author>]
		 * : The ID, username or email of the default user to map posts to. Defaults to the site admin.
		 *
		 * [--set-default=<set-default>]
		 * : Whether a default author should be set.
		 *
		 * [--post-type=<post_type>]
		 * : The post types to convert. Can be a single post-type or multiple, comma-separated.
		 *
		 * ## Examples
		 *
		 * 	# Migrate using a remote author file.
		 * 	wp wpam migrate https://example.com/users.json
		 *
		 * 	# Migrate using a local author file.
		 * 	wp wpam migrate ~/users.json
		 *
		 * 	# Migrate using a remote author file and set the default author to the user with an ID of 4.
		 * 	wp wpam migrate https://example.com/users.json --default-author=4
		 *
		 * 	# Migrate using a remote author file and set the default author to the user with a username of "john".
		 * 	wp wpam migrate https://example.com/users.json --default-author=john
		 *
		 * 	# Migrate using a remote author file and set the default author to the user with an email address of "john@example.com".
		 * 	wp wpam migrate https://example.com/users.json --default-author=john@example.com
		 *
		 * # Migrate using a remote author file, set the default author to the user with an ID of 4 and process post types of `post` and `externalstory`.
		 * wp wpam migrate https://example.com/users.json --default-author=4 --post-type=post,externalstory
		 */
		public function __invoke( $args, $assoc_args ) {
			list( $author_map ) = $args;

			// Get the author arg
			$default_author = isset( $assoc_args['default-author'] )
				? $assoc_args['default-author']
				: 1;

			$set_default = isset( $assoc_args['set-default'] )
				? filter_var( $assoc_args['set-default'], FILTER_VALIDATE_BOOLEAN )
				: true;

			$post_types = isset( $assoc_args['post-type'] )
				? $assoc_args['post-type']
				: 'any';

			try {
				$cmd = new WPAM_Author_Migrate( $author_map, $default_author, $set_default, $post_types );
				$cmd->migrate();

				WP_CLI::Success( $cmd->get_stats() );

			} catch (Exception $e) {
				WP_CLI::error( $e->getMessage(), $e->getCode() );
			}

		}
	}

	WP_CLI::add_command( 'wpam migrate', 'WPAM_WP_CLI_Author_Migrate' );
}
