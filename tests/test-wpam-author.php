<?php

class WPAM_Author_Test extends WP_UnitTestCase {
	/**
	 * Primary setup function.
	 */
	public function setUp() {
		parent::setUp();

		$user = get_user_by( 'ID', 1 );
		$this->user = $user;

		$this->class_instance = new WPAM_Author((object) array(
			'ID'           => 1,
			'user_login'   => $user->user_login,
			'display_name' => $user->display_name,
			'user_email'   => $user->user_email
		));
	}

	/**
	 * Ensures the correct user is mapped.
	 */
	public function test_new_id() {
		$new_id = $this->class_instance->new_id;
		$expected = $this->user->ID;

		$this->assertEquals( $expected, $new_id );
	}

	/**
	 * Ensures the correct user is mapped.
	 */
	public function test_username() {
		$login = $this->class_instance->user_login;
		$expected = $this->user->user_login;

		$this->assertEquals( $expected, $login );
	}

	/**
	 * Ensures the correct user is mapped.
	 */
	public function test_email() {
		$email = $this->class_instance->user_email;
		$expected = $this->user->user_email;

		$this->assertEquals( $expected, $email );
	}

	/**
	 * Ensures the mapped property is set correctly.
	 */
	public function test_mapped() {
		$mapped = $this->class_instance->mapped;

		$this->assertTrue( $mapped );
	}

	/**
	 * Ensures the needs_update property is set correctly.
	 */
	public function test_needs_update() {
		$needs_update = $this->class_instance->needs_update;

		$this->assertFalse( $needs_update );
	}
}
