<?php
/**
 * Utility object for migrating authors
 */
if ( ! class_exists( 'WPAM_Author' ) ) {
	class WPAM_Author {
		public
			$new_id,         // The ID of the user in the new site.
			$mapped = false, // If the user has been mapped to an existing user account.
			$old_id,         // The ID of the user in the old site.
			$user_login,     // The login of the user in the old site.
			$display_name,   // The display name of the user in the old site.
			$user_email,     // The email of the user in the old site.
			$needs_update;   // Indicates if the author id is different between systems.

		/**
		 * Constructs the WPAM_Author object
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @param object $json_data The deserialized json data of the author
		 */
		public function __construct( $json_data ) {
			$this->old_id       = $json_data->ID;
			$this->user_login   = $json_data->user_login;
			$this->display_name = $json_data->display_name;
			$this->user_email   = $json_data->user_email;

			$this->mapped = $this->map_existing_user();

			if ( $this->mapped ) {
				$this->needs_update = ! ( $this->old_id === $this->new_id );
			} else {
				$this->needs_update = false;
			}
		}

		/**
		 * Maps incoming user data to an existing user account.
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @return bool True if the user is mapped, false if it is not mapped.
		 */
		private function map_existing_user() {
			$user = null;

			$user = get_user_by( 'login', $this->user_login );

			if ( $user ) {
				$this->new_id = $user->ID;
				return true;
			}

			$user = get_user_by( 'email', $this->user_email );

			if ( $user ) {
				$this->new_id = $user->ID;
				return true;
			}

			return false;
		}
	}
}
