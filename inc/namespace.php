<?php
/**
 * Brightcove Video CMB2 Field Register.
 *
 * @package cmb2-brightcove-video-field
 */

namespace CMB2\BrightcoveVideoField;

use BC_Video_Shortcode;

/**
 * Hook up all the filters and actions.
 */
function bootstrap() {

	add_action( 'plugins_loaded', __NAMESPACE__ . '\\load_textdomain' );
	add_action( 'plugins_loaded', __NAMESPACE__ . '\\load_plugin' );
}

/**
 * Load plugin text domain for text translation.
 */
function load_textdomain() {

	load_plugin_textdomain(
		'cmb2-brightcove-video-field',
		false,
		basename( plugin_dir_url( __DIR__ ) ) . '/languages'
	);
}

/**
 * Dependency check before loading the plugin.
 */
function is_dependency_loaded() {

	return (
		defined( 'CMB2_LOADED' )
		&& ! empty( constant( 'CMB2_LOADED' ) )
		&& defined( 'BRIGHTCOVE_URL' )
		&& ! empty( constant( 'BRIGHTCOVE_URL' ) )
	);
}

/**
 * Load plugin functionality if dependency are loaded correctly.
 */
function load_plugin() {

	if ( ! is_dependency_loaded() ) {
		add_action( 'admin_notices', __NAMESPACE__ . '\\dependency_admin_notice' );
		add_action( 'network_admin_notices', __NAMESPACE__ . '\\dependency_admin_notice' );
		return;
	}

	add_brightcove_video_field();
}

/**
 * Plugin dependency error message for admin notice.
 */
function dependency_admin_notice() {

	echo '<div class="error"><p>';
	esc_html_e( '"CMB2 BrightCove Video Field" plugin can\'t be loaded, It requires following plugins to be installed and activated.', 'cmb2-brightcove-video-field' );
		echo '<ol>';
			printf(
				'<li><a href="https://wordpress.org/plugins/cmb2/" target="_blank">%s</a></li>',
				esc_html__( 'CMB2', 'cmb2-brightcove-video-field' )
			);
			printf(
				' <li><a href="https://wordpress.org/plugins/brightcove-video-connect" target="_blank">%s</a></li>',
				esc_html__( 'Brightcove Video Connect', 'cmb2-brightcove-video-field' )
			);
		echo '</ol>';
	esc_html_e( 'Please verify the dependency to enable this field type.', 'cmb2-brightcove-video-field' );
	echo '</p></div>';
}

/**
 * Register brightcove video field.
 */
function add_brightcove_video_field() {

	add_action( 'admin_enqueue_scripts', __NAMESPACE__ . '\\enqueue_scripts', 11 );
	add_action( 'cmb2_render_brightcove_video', __NAMESPACE__ . '\\cmb2_render_callback_for_brightcove_video', 10, 5 );
	add_action( 'admin_footer', __NAMESPACE__ . '\\js_wp_templates' );
}

/**
 * Enqueue helper JS script in the admin.
 *
 * @param string $hook Hook for the current page in the admin.
 */
function enqueue_scripts( $hook ) {

	if ( ! in_array( $hook, [ 'post.php', 'post-new.php' ], true ) ) {
		return;
	}

	wp_enqueue_style(
		'cmb2-brightcove-video-field-css',
		plugin_dir_url( __FILE__ ) . 'assets/css/brightcove-video-field.css',
		[],
		VERSION
	);

	wp_enqueue_script(
		'cmb2-brightcove-video-field-js',
		plugin_dir_url( __FILE__ ) . 'assets/js/brightcove-video-field.js',
		[
			'wp-util',
			'brightcove-admin',
		],
		VERSION,
		true
	);
}


/**
 * Brightcove Video Field render callback function.
 *
 * @param \CMB2_Field $field `CMB2_Field` object.
 * @param array       $escaped_value Field escaped values.
 * @param int         $object_id Field object id, ex: post id, term id etc.
 * @param string      $object_type Field object type, ex: post type, user, option-page etc.
 * @param \CMB2_Types $field_type_object `CMB2_Types` object.
 */
function cmb2_render_callback_for_brightcove_video( $field, $escaped_value, $object_id, $object_type, $field_type_object ) {

	$field_id = $field->id();

	$bc_video_args = [
		'video_id'    => $escaped_value['bc_video_id'] ?? '',
		'player_id'   => $escaped_value['bc_player_id'] ?? '',
		'account_id'  => $escaped_value['bc_account_id'] ?? '',
		'embed'       => 'iframe',
		'width'       => '100%',
		'height'      => 'auto',
		'padding_top' => '10', // Adding min 10 padding since by-default it's adding 56.25% padding even with 0 padding.
	];

	$allowed_html           = wp_kses_allowed_html( 'post' );
	$allowed_html['iframe'] = [
		'src'                   => true,
		'webkitallowfullscreen' => true,
		'allowfullscreen'       => true,
		'mozallowfullscreen'    => true,
		'style'                 => true,
	];
	$allowed_html['input']  = [
		'type'      => true,
		'class'     => true,
		'name'      => true,
		'id'        => true,
		'value'     => true,
		'data-hash' => true,
	];

	// Remove jetpack shortcode module filters which is converting this shortcode to anchor tag when jetpack is enabled.
	// ref: https://github.com/Automattic/jetpack/blob/cb04cfc4479515f12945256555bbab1192711c57/modules/shortcodes/class.filter-embedded-html-objects.php#L11-L12.
	if ( class_exists( 'Filter_Embedded_HTML_Objects' ) ) {
		remove_filter( 'pre_kses', [ 'Filter_Embedded_HTML_Objects', 'filter' ], 11 );
		remove_filter( 'pre_kses', [ 'Filter_Embedded_HTML_Objects', 'maybe_create_links' ], 100 );
	}

	?>
	<div class="cmb2-brightcove-video-metabox">
		<button type="button" class="button brightcove-add-media-btn">
			<?php
			$button_title = __( 'Select Brightcove Video', 'cmb2-brightcove-video-field' );
			printf(
				'<img class="bc-button-icon" src="%s" alt="%s" />%s',
				esc_url( BRIGHTCOVE_URL . 'images/menu-icon.svg' ),
				esc_attr( $button_title ),
				esc_html( $button_title )
			);
			?>
		</button>
		<div class="brightcove-video-preview">
			<?php
				if( ! empty( $bc_video_args['video_id'] ) ) {
					echo wp_kses( BC_Video_Shortcode::bc_video( $bc_video_args ), $allowed_html );
					echo '<a href="#" class="bc-remove-video">Remove brightcove video</a>';
				}
			?>
		</div>
		<?php

		echo wp_kses(
			$field_type_object->input(
				[
					'type'  => 'hidden',
					'id'    => esc_attr( $field_id . '_bc_video_id' ),
					'class' => 'bc_video_id',
					'name'  => esc_attr( $field_id . '[bc_video_id]' ),
					'value' => esc_attr( $escaped_value['bc_video_id'] ) ?? '',
				]
			),
			$allowed_html
		);
		echo wp_kses(
			$field_type_object->input(
				[
					'type'  => 'hidden',
					'id'    => esc_attr( $field_id . '_bc_video_duration' ),
					'class' => 'bc_video_duration',
					'name'  => esc_attr( $field_id . '[bc_video_duration]' ),
					'value' => esc_attr( $escaped_value['bc_video_duration'] ) ?? '',
				]
			),
			$allowed_html
		);
		echo wp_kses(
			$field_type_object->input(
				[
					'type'  => 'hidden',
					'id'    => esc_attr( $field_id . '_bc_player_id' ),
					'class' => 'bc_player_id',
					'name'  => esc_attr( $field_id . '[bc_player_id]' ),
					'value' => esc_attr( $escaped_value['bc_player_id'] ) ?? '',
				]
			),
			$allowed_html
		);
		echo wp_kses(
			$field_type_object->input(
				[
					'type'  => 'hidden',
					'id'    => esc_attr( $field_id . '_bc_account_id' ),
					'class' => 'bc_account_id',
					'name'  => esc_attr( $field_id . '[bc_account_id]' ),
					'value' => esc_attr( $escaped_value['bc_account_id'] ) ?? '',
				]
			),
			$allowed_html
		);
		?>
	</div>
	<?php

	// Enable jetpack embed filter.
	if ( class_exists( 'Filter_Embedded_HTML_Objects' ) ) {
		add_filter( 'pre_kses', [ 'Filter_Embedded_HTML_Objects', 'filter' ], 11 );
		add_filter( 'pre_kses', [ 'Filter_Embedded_HTML_Objects', 'maybe_create_links' ], 100 );
	}
}

/**
 * JS WP Template to add video preview from Brightcove modal selection.
 */
function js_wp_templates() {
	?>
	<script type="text/html" id="tmpl-cmb2-brightcove-video-preview">
		<iframe
			width="100%"
			src="https://players.brightcove.net/{{data.account_id}}/{{data.player_id}}_default/index.html?videoId={{data.id}}"
			allowfullscreen
			webkitallowfullscreen
			mozallowfullscreen
		></iframe>
		<a href="#" class="bc-remove-video">Remove brightcove video</a>
	</script>
	<?php
}
