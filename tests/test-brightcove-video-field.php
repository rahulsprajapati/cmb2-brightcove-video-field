<?php
/**
 * Test for Brightcove Video Field register.
 *
 * @package cmb2-brightcove-video-field
 */

namespace CMB2\Tests\BrightcoveVideoField;

use CMB2\BrightcoveVideoField;
use CMB2_Bootstrap_260;
use CMB2_Field;
use CMB2_Types;
use WP_UnitTestCase;

/**
 * Brightcove Video Field test case.
 */
class Brightcove_Video_Field extends WP_UnitTestCase {

	/**
	 * Cmb2 Plugin File Path.
	 *
	 * @var string
	 */
	static $cmb2_plugin = WP_PLUGIN_DIR . '/cmb2/init.php';

	/**
	 * Brightcove Plugin File Path.
	 *
	 * @var string
	 */
	static $brightcove_plugin = WP_PLUGIN_DIR . '/brightcove-video-connect/brightcove-video-connect.php';

	/**
	 * Test to check cmb2 plugin availability.
	 */
	public function test_cmb2_plugin_exists() {
		$this->assertTrue(
			file_exists( self::$brightcove_plugin ),
			'CMB2 Plugin Not Available.'
		);
	}

	/**
	 * Test to check brightcove plugin availability.
	 */
	public function test_brightcove_plugin_exists() {
		$this->assertTrue(
			file_exists( self::$brightcove_plugin ),
			'Brightcove Video Connect Plugin Not Available.'
		);
	}

	/**
	 * Test bootstrap.
	 */
	public function test_bootstrap() {
		BrightcoveVideoField\bootstrap();

		$this->assertEquals( 10, has_action( 'plugins_loaded', 'CMB2\\BrightcoveVideoField\\load_textdomain' ) );
		$this->assertEquals( 10, has_action( 'plugins_loaded', 'CMB2\\BrightcoveVideoField\\load_plugin' ) );
	}

	/**
	 * Test case for admin notice.
	 */
	public function test_dependency_admin_notice() {
		// Actual data.
		ob_start();
		do_action( 'admin_notices' );
		$admin_notices_content = ob_get_contents();
		ob_end_clean();

		$this->assertContains(
			htmlentities2( '"CMB2 BrightCove Video Field" plugin can\'t be loaded' ),
			$admin_notices_content
		);
	}

	/**
	 * Test case for plugin load with/without dependency plugins.
	 */
	public function test_brightcove_video_field_plugin_loaded() {

		$this->plugin_not_loaded_test_cases();

		activate_plugin( self::$cmb2_plugin );

		$this->plugin_not_loaded_test_cases();

		activate_plugin( self::$brightcove_plugin );

		$this->maybe_remove_admin_notices();

		$this->plugin_loaded_test_cases();
	}

	/**
	 * Test case for plugin load without dependency plugins.
	 */
	public function plugin_not_loaded_test_cases() {
		BrightcoveVideoField\load_plugin();

		$this->assertFalse( BrightcoveVideoField\is_dependency_loaded() );

		$this->assertEquals(
			10,
			has_action( 'admin_notices', 'CMB2\\BrightcoveVideoField\\dependency_admin_notice' )
		);
		$this->assertEquals(
			10,
			has_action( 'network_admin_notices', 'CMB2\\BrightcoveVideoField\\dependency_admin_notice' )
		);

		$this->assertNotEquals(
			11,
			has_action( 'admin_enqueue_scripts', 'CMB2\\BrightcoveVideoField\\enqueue_scripts' )
		);
		$this->assertNotEquals(
			10,
			has_action( 'cmb2_render_brightcove_video', 'CMB2\\BrightcoveVideoField\\cmb2_render_callback_for_brightcove_video' )
		);
		$this->assertNotEquals(
			10,
			has_action( 'admin_footer', 'CMB2\\BrightcoveVideoField\\js_wp_templates' )
		);
	}

	/**
	 * Test case for plugin load with dependency plugins.
	 */
	public function plugin_loaded_test_cases() {
		BrightcoveVideoField\load_plugin();

		$this->assertTrue( BrightcoveVideoField\is_dependency_loaded() );

		$this->assertNotEquals(
			10,
			has_action( 'admin_notices', 'CMB2\\BrightcoveVideoField\\dependency_admin_notice' )
		);
		$this->assertNotEquals(
			10,
			has_action( 'network_admin_notices', 'CMB2\\BrightcoveVideoField\\dependency_admin_notice' )
		);

		$this->assertEquals(
			11,
			has_action( 'admin_enqueue_scripts', 'CMB2\\BrightcoveVideoField\\enqueue_scripts' )
		);
		$this->assertEquals(
			10,
			has_action( 'cmb2_render_brightcove_video', 'CMB2\\BrightcoveVideoField\\cmb2_render_callback_for_brightcove_video' )
		);
		$this->assertEquals(
			10,
			has_action( 'admin_footer', 'CMB2\\BrightcoveVideoField\\js_wp_templates' )
		);
	}

	/**
	 * Remove admin notices if already added from `BrightcoveVideoField\load_plugin()`
	 * before activating dependency plugins.
	 */
	public function maybe_remove_admin_notices() {

		if ( has_action( 'admin_notices', 'CMB2\\BrightcoveVideoField\\dependency_admin_notice' ) ) {
			remove_action( 'admin_notices', 'CMB2\\BrightcoveVideoField\\dependency_admin_notice' );
		}

		if ( has_action( 'network_admin_notices', 'CMB2\\BrightcoveVideoField\\dependency_admin_notice' ) ) {
			remove_action( 'network_admin_notices', 'CMB2\\BrightcoveVideoField\\dependency_admin_notice' );
		}
	}

	/**
	 * Test case for enqueue_scripts function.
	 */
	public function test_enqueue_scripts() {
		$script = 'cmb2-brightcove-video-field-js';
		$style  = 'cmb2-brightcove-video-field-css';

		BrightcoveVideoField\enqueue_scripts( '' );

		$this->assertFalse( wp_script_is( $script ) );
		$this->assertFalse( wp_style_is( $style ) );

		BrightcoveVideoField\enqueue_scripts( 'post.php' );

		$this->assertTrue( wp_script_is( $script ) );
		$this->assertTrue( wp_style_is( $style ) );
	}

	/**
	 * Test case for brightcove video field type render.
	 */
	public function test_cmb2_field_render() {

		$cmb_plugin_instance = CMB2_Bootstrap_260::initiate();
		$cmb_plugin_instance->include_cmb();

		BrightcoveVideoField\add_brightcove_video_field();

		$video_data = [
			'bc_video_id'       => '1236242437001',
			'bc_video_duration' => '2:29',
			'bc_player_id'      => '61hrf3oYj',
			'bc_account_id'     => '8926567543001',
		];

		$args = [
			'name'    => esc_html__( 'Brightcove Video', 'cmb2-brightcove-video-field' ),
			'id'      => 'brightcove_featured_video',
			'type'    => 'brightcove_video',
			'default' => $video_data,
		];

		$cmb_field = new CMB2_Field( [
			'field_args' => $args,
		] );

		$field_type_obj = new CMB2_Types( $cmb_field );

		ob_start();
		$field_type_obj->render();
		$field_render_html = ob_get_contents();
		ob_end_clean();

		$field_render_html = $this->trim_html_markup( $field_render_html );

		$hash_id = $cmb_field->hash_id();

		// Test Brightcove Video Preview.
		$this->assertContains(
			'https://players.brightcove.net/8926567543001/61hrf3oYj_default/index.html?videoId=1236242437001',
			$field_render_html
		);

		$bc_video_id_input_field = sprintf(
			'<input
				type="hidden"
				class="bc_video_id"
				name="brightcove_featured_video[bc_video_id]"
				id="brightcove_featured_video_bc_video_id"
				value="%s"
				data-hash=\'%s\' />',
			$video_data['bc_video_id'],
			$hash_id
		);

		// Test Brightcove Video ID Input Hidden Field.
		$this->assertContains(
			$this->trim_html_markup( $bc_video_id_input_field ),
			$field_render_html
		);

		$bc_video_duration_input_field = sprintf(
			'<input
				type="hidden"
				class="bc_video_duration"
				name="brightcove_featured_video[bc_video_duration]"
				id="brightcove_featured_video_bc_video_duration"
				value="%s"
				data-hash=\'%s\' />',
			$video_data['bc_video_duration'],
			$hash_id
		);

		// Test Brightcove Video Duration Input Hidden Field.
		$this->assertContains(
			$this->trim_html_markup( $bc_video_duration_input_field ),
			$field_render_html
		);

		$bc_player_id_input_field = sprintf(
			'<input
				type="hidden"
				class="bc_player_id"
				name="brightcove_featured_video[bc_player_id]"
				id="brightcove_featured_video_bc_player_id"
				value="%s"
				data-hash=\'%s\' />',
			$video_data['bc_player_id'],
			$hash_id
		);

		// Test Brightcove Video Player ID Input Hidden Field.
		$this->assertContains(
			$this->trim_html_markup( $bc_player_id_input_field ),
			$field_render_html
		);

		$bc_account_id_input_field = sprintf(
			'<input
				type="hidden"
				class="bc_account_id"
				name="brightcove_featured_video[bc_account_id]"
				id="brightcove_featured_video_bc_account_id"
				value="%s"
				data-hash=\'%s\' />',
			$video_data['bc_account_id'],
			$hash_id
		);

		// Test Brightcove Account ID Input Hidden Field.
		$this->assertContains(
			$this->trim_html_markup( $bc_account_id_input_field ),
			$field_render_html
		);
	}

	/**
	 * Remove extra spaces from html output of ob_get_content.
	 *
	 * @param string $content ob_get_content data.
	 *
	 * @return string|null clean ob data.
	 */
	public function trim_html_markup( $content ) {

		// Remove any extra space from ob_start.
		$content = preg_replace( '/\s+/', ' ', $content );

		return trim( $content );
	}

	/**
	 * Test case for js_wp_templates function.
	 */
	public function test_js_wp_templates() {
		// Actual data.
		ob_start();
		BrightcoveVideoField\js_wp_templates();
		$cmb2_bc_video_preview = ob_get_contents();
		ob_end_clean();

		$this->assertContains(
			'<script type="text/html" id="tmpl-cmb2-brightcove-video-preview">',
			$cmb2_bc_video_preview
		);

		$this->assertContains(
			'https://players.brightcove.net/{{data.account_id}}/{{data.player_id}}_default/index.html?videoId={{data.id}}',
			$cmb2_bc_video_preview
		);
	}

}
