<?php

if ( ! defined( 'WP_CLI_ROOT' ) ) {
	define( 'WP_CLI_ROOT', WPAM_PLUGIN_DIR . 'vendor/wp-cli/wp-cli' );
}

include WP_CLI_ROOT . '/php/utils.php';
include WP_CLI_ROOT . '/php/dispatcher.php';
include WP_CLI_ROOT . '/php/class-wp-cli.php';
include WP_CLI_ROOT . '/php/class-wp-cli-command.php';

\WP_CLI\Utils\load_dependencies();

class WP_Author_MigrationTest extends WP_UnitTestCase {
	/**
	 * Primary setup function.
	 */
	public function setUp() {
		parent::setUp();

		$users = self::factory()->user->create_many( 3 );

		$user = self::factory()->user->create( array(
			'user_login'   => 'specialtestuser',
			'display_name' => 'Special Test User',
			'user_email'   => 'specialtestuser@test.com'
		) );

		$this->target_user_id = $user;

		self::factory()->post->create_many( 45, array(
			'post_author' => 2
		) );
	}

	/**
	 * Checks to make sure we have a mapped author
	 */
	public function test_mapped_authors() {
		$this->class_instance = new WPAM_Author_Migrate(
			WPAM_PLUGIN_DIR . 'tests/users.json'
		);

		$mapped_users = $this->class_instance->authors;

		$target_user = null;

		foreach( $mapped_users as $user ) {
			if ( $user->old_id === 2 ) {
				$target_user = $user;
			}
		}

		$this->assertNotEmpty( $target_user );
		$this->assertTrue( $target_user->mapped );
		$this->assertTrue( $target_user->needs_update );
	}

	/**
	 * Runs the actual migration against test data.
	 */
	public function test_migration() {
		$this->class_instance = new WPAM_Author_Migrate(
			WPAM_PLUGIN_DIR . 'tests/users.json'
		);

		$this->class_instance->migrate();

		$posts = get_posts( array(
			'posts_per_page' => -1,
			'author__not_in' => array( 0 )
		) );

		foreach( $posts as $post ) {
			self::assertEquals( (int)$this->target_user_id, (int)$post->post_author );
		}
	}
}
