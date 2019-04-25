<?php
/**
 * Utility class that maps user data from one WP Instance
 * to users in a new WP Instance.
 */

require_once dirname( __FILE__ ) . '/class-wpam-author.php';

if ( ! class_exists( 'WPAM_Author_Migrate' ) ) {
	class WPAM_Author_Migrate {
		public
			$set_default_author, // Boolean that indicates if default author should be set.
			$default_author,     // Default author object
			$post_types,         // The post types to process
			$all_authors,        // All authors encountered on new system
			$authors,            // An array of authors
			$unmapped_authors,   // Users with no local mapping
			$total,              // The total number of posts processed
			$updated,            // Number of posts with updated users
			$not_updated,        // Indicates the number of posts that needed no updates
			$cannot_update;      // Number of posts that could not be mapped

		/**
		 * Constructs the WPAM_Author_Migrate object
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @param string $file_path The file path of the user export
		 * @param mixed $default_user the username or ID of the default user.
		 */
		public function __construct( $file_path, $default_user=1, $set_default=True, $post_type='any' ) {
			// Create empty arrays on author arrays
			$this->all_authors      = array();
			$this->authors          = array();
			$this->unmapped_authors = array();

			// Set all integers to 0
			$this->total         = 0;
			$this->updated       = 0;
			$this->not_updated   = 0;
			$this->cannot_update = 0;

			$this->set_default_author = $set_default;
			$this->post_types = array_map( 'trim', explode( ',', $post_type ) );

			$this->verify_post_types();

			if ( $this->set_default_author ) {
				$this->default_author = $this->get_default_author( $default_user );
			}

			$file_data = null;

			// Take care of getting the file data first.
			if ( preg_match( "/^(http\:\/\/|https\:\/\/)/", $file_path ) ) {
				$file_data = $this->get_remote_file( $file_path );
			} else {
				$file_data = file_get_contents( $file_path );
			}

			$user_data = json_decode( $file_data );

			if ( ! $user_data ) {
				throw new Exception(
					'Unable to decode the JSON within the specified file.'
				);
			}

			$this->create_user_mappings( $user_data );
		}

		/**
		 * Runs the migration steps
		 * @author Jim Barnes
		 * @since 1.0.0
		 */
		public function migrate() {
			$query = new WP_Query( array(
				'post_type'      => $this->post_types,
				'posts_per_page' => -1
			) );

			$this->total = $query->post_count;

			$progress = WP_CLI\Utils\make_progress_bar(
				'Updating author IDs...',
				$this->total
			);

			foreach( $query->posts as $post ) {
				$this->update_post_author( $post );
				$progress->tick();
			}

			$progress->finish();
		}

		/**
		 * Displays the results of the migration
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @return string The output string
		 */
		public function get_stats() {
			$total_authors = count( $this->all_authors );
			$mapped_count = count( $this->authors );
			$unmapped_count = count( $this->unmapped_authors );
			$all_file_authors = $mapped_count + $unmapped_count;

			$default_author = isset( $this->default_author )
				? $this->default_author->user_login
				: "No default set";

			$retval =
"
Finished updating post authors.

Default Author   : $default_author

Authors Found    : $total_authors
Authors in File  : $all_file_authors
Authors Mapped   : $mapped_count
Authors Unmapped : $unmapped_count

Total Processed  : $this->total

Posts Updated    : $this->updated
Posts Skipped    : $this->not_updated
Unable to Update : $this->cannot_update
";

			return $retval;

		}

		/**
		 * Helper function that verifies that the
		 * provided post types exist.
		 * @author Jim Barnes
		 * @since 1.0.0
		 */
		private function verify_post_types() {
			$invalid = array();
			$throw = false;

			// Short curcuit if the only parameter is 'any'
			if ( count( $this->post_types ) === 1 && $this->post_types[0] === 'any' ) {
				return;
			} else if ( in_array( 'any', $this->post_types ) ) {
				// If we get here, there are multiple post_types defined
				// but the `any` keyword is present.
				// Update $this->post_types to be a single string.
				$this->post_types = 'any';
				return;
			}

			foreach( $this->post_types as $post_type ) {
				if ( ! post_type_exists( $post_type ) ) {
					$invalid[] = $post_type;
					$throw = true;
				}
			}

			if ( $throw ) {
				$message = '';

				if ( count( $invalid ) > 1 ) {
					$post_types = "\"" . implode( "\", \"", array_slice( $invalid, 0, -1 ) ) . "\" and \"" . end( $invalid ) . "\"";

					$message = "
The post types $post_types are not valid post types
on this WordPress instance.
					";
				} else {
					$message = "
The post type \"$invalid[0]\" is not a valid post type
on this WordPress instance.
					";
				}

				throw new Exception( $message );
			}
		}

		/**
		 * Resolves the default user data to an object
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @param mixed $default_author The ID, username or email of the default user.
		 * @return WPAM_Author
		 */
		private function get_default_author( $default_author ) {
			$user = null;

			if ( is_int( $default_author ) ) {
				$user = get_user_by( 'id', $default_author );
			} else if ( is_string( $default_author ) && strpos( $default_author, '@' ) ) {
				$user = get_user_by( 'email', $default_author );
			} else if ( is_string( $default_author ) ) {
				$user = get_user_by( 'login', $default_author );
			}

			if ( $user ) {
				$user_data = (object)array(
					'old_id'       => $user->ID,
					'user_login'   => $user->user_login,
					'display_name' => $user->display_name,
					'user_email'   => $user->user_email
				);

				return new WPAM_Author( $user_data );
			}

			// If it gets here, it means we weren't able to resolve the default user.
			throw new Exception(
				"
Unable to retrieve default author.
Check the value being passed in.
Alternatively, you can set no default user using --set-default=false
				"
			);
		}

		/**
		 * Helper function for getting remote json file.
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @param string $url The url of the remote file.
		 * @return string
		 */
		private function get_remote_file( $url ) {
			$retval = false;

			$args = array(
				'timeout' => 10
			);

			$response     = wp_remote_get( $url, $args );
			$reponse_code = wp_remote_retrieve_response_code( $response );

			if ( is_array( $response ) && is_int( $response_code ) && $response_code < 400 ) {
				$retval = wp_remote_retrieve_body( $response );
			} else {
				throw new Exception(
					'Unable to retrieve remote user JSON data.',
					'Verify the path is valid and that you can retrieve it.'
				);
			}

			return $retval;
		}

		private function get_local_file( $file_path ) {
			$retval = file_get_contents( $file_path );

			if ( $retval === false ) {
				throw new Exception(
					'Unable to retrieve local user JSON data.',
					'Verify the path is valid and that its permissions are set appropriately.'
				);
			}

			return $retval;
		}

		/**
		 * Reads the data from export file and builds
		 * the users array.
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @param array $user_data The unserialized user data from the user export file.
		 */
		private function create_user_mappings( $user_data ) {
			foreach( $user_data as $user ) {
				$author = new WPAM_Author( $user );

				if ( $author->mapped ) {
					$this->authors[$author->old_id] = $author;
				} else {
					$this->unmapped_authors[] = $author;
				}
			}
		}

		/**
		 * Updates the post author if a mapped author is found
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @param WP_Post $post The post to update
		 */
		private function update_post_author( $post ) {
			// Get the current post author
			$current_author = (int)$post->post_author;

			if ( ! isset( $this->all_authors[$current_author] ) ) {
				$this->all_authors[$current_author] = $current_author;
			}

			$author = isset( $this->authors[$current_author] )
				? $this->authors[$current_author]
				: null;

			if ( ! $author ) {
				if ( $this->set_default_author ) {
					$author = $this->default_author;
				} else {
					$this->cannot_update++;
					return;
				}
			}

			global $wpdb;

			// If the author needs to be updated run through the update process
			if ( $author->needs_update && $current_author !== $author->new_id ) {
				$update_status = $wpdb->update( $wpdb->posts, array( 'post_author' => $author->new_id ), array( 'ID' => $post->ID ) );

				if ( $update_status !== false ) {
					$this->updated++;
					clean_post_cache( $post->ID );
				}

			} else if ( $author->needs_update === false ) {
				$this->not_updated++;
				return;
			} else {
				$this->cannot_update++;
				return;
			}
		}
	}
}
