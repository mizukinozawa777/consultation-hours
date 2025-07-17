<?php
/*
 * Plugin Name:       Consultation Hours
 * Plugin URI:        
 * Description:       A plugin to display a consultation hours table using a shortcode.
 * Version:           1.3.1
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * Author:            Mizuki Nozawa
 * Author URI:        https://aries67.com/
 * License:           GPLv2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       consultation-hours
 */

// ファイルへの直接アクセスを禁止
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ----------------------------------------------------------------
// デフォルト値の定義
// ----------------------------------------------------------------

/**
 * デフォルトの時間帯設定を取得
 */
function consultation_hours_get_default_hours() {
	return [
		'morning'   => [ 'label' => __( 'Morning', 'consultation-hours' ), 'time' => '8:15～11:30', 'enabled' => true ],
		'afternoon' => [ 'label' => __( 'Afternoon', 'consultation-hours' ), 'time' => '14:45～15:30', 'enabled' => true ],
		'night'     => [ 'label' => __( 'Evening', 'consultation-hours' ), 'time' => '15:30～18:00', 'enabled' => true ],
	];
}

/**
 * デフォルトのCSSを取得
 */
function consultation_hours_get_default_css() {
	return "
        .consultation-hours { width: 100%; border-collapse: collapse; font-family: sans-serif; }
        .consultation-hours th, .consultation-hours td { border: 1px solid #ccc; padding: 8px; text-align: center; vertical-align: middle; }
        .consultation-hours th { background-color: #224; color: #fff; }
        .consultation-hours th.sunday { background-color: #a00; }
        .consultation-hours tfoot td { text-align: left; vertical-align: top; padding: 1em; background-color: #f9f9f9; font-size: 0.95em; }
        .consultation-notes p { margin: 0 0 0.3em 0; font-weight: bold; }
        .consultation-notes ul { margin: 0; padding-left: 1.2em; list-style: disc; }
        .consultation-hours .time-label { background-color: #f4f4f4; font-weight: bold; text-align: left; padding-left: 1em; }
        .consultation-hours .star { color: gold; }
        .consultation-hours .closed { color: #aaa; }
        .consultation-notes { margin-top: 1em; font-size: 0.95em; }
        .consultation-notes ul { padding-left: 1.2em; }
    ";
}

// ----------------------------------------------------------------
// データの保存処理
// ----------------------------------------------------------------

/**
 * 設定ページからのデータ保存をハンドル
 */
function consultation_hours_handle_form_save() {
	if ( ! isset( $_POST['consultation_hours_data_submit'] ) || ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'consultation_hours_data_submit' ) ) {
		return;
	}
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$posted_data = wp_unslash( $_POST );

	if ( isset( $posted_data['consultation_hours_table_data'] ) && is_array( $posted_data['consultation_hours_table_data'] ) ) {
		$table_data_input = $posted_data['consultation_hours_table_data'];
		$table_data       = [];
		$allowed_symbols  = [ '●', '★', '／' ];
		foreach ( $table_data_input as $slot => $days ) {
			$sanitized_slot = sanitize_key( $slot );
			if ( ! is_array( $days ) ) {
				continue;
			}
			foreach ( $days as $day => $val ) {
				$sanitized_day = (int) $day;
				$table_data[ $sanitized_slot ][ $sanitized_day ] = in_array( $val, $allowed_symbols, true ) ? $val : '／';
			}
		}
		update_option( 'consultation_hours_table_data', $table_data );
	}

	if ( isset( $posted_data['consultation_hours_notes'] ) && is_array( $posted_data['consultation_hours_notes'] ) ) {
		$notes_input = $posted_data['consultation_hours_notes'];
		$notes       = array_filter( array_map( 'sanitize_text_field', $notes_input ) );
		update_option( 'consultation_hours_notes', $notes );
	}

	if ( isset( $posted_data['consultation_hours_hours'] ) && is_array( $posted_data['consultation_hours_hours'] ) ) {
		$hours_input = $posted_data['consultation_hours_hours'];
		$hours       = [];
		foreach ( $hours_input as $key => $val ) {
			if ( ! is_array( $val ) ) {
				continue;
			}
			$hours[ sanitize_key( $key ) ] = [
				'label'   => sanitize_text_field( $val['label'] ?? '' ),
				'time'    => sanitize_text_field( $val['time'] ?? '' ),
				'enabled' => ! empty( $val['enabled'] ),
			];
		}
		update_option( 'consultation_hours_hours', $hours );
	}

	add_settings_error( 'consultation_hours_messages', 'consultation_hours_message', __( 'Settings saved.', 'consultation-hours' ), 'updated' );
}
add_action( 'admin_init', 'consultation_hours_handle_form_save' );

// ----------------------------------------------------------------
// ショートコードとCSSの登録
// ----------------------------------------------------------------

function consultation_hours_register_shortcode() {
	ob_start();
	include plugin_dir_path( __FILE__ ) . 'shortcode-display.php';
	return ob_get_clean();
}
add_shortcode( 'consultation_hours', 'consultation_hours_register_shortcode' );

function consultation_hours_enqueue_plugin_styles() {
	if ( ! is_admin() && has_shortcode( get_the_content(), 'consultation_hours' ) ) {
		wp_register_style( 'consultation-hours-style', false );
		wp_enqueue_style( 'consultation-hours-style' );
		$default_css = consultation_hours_get_default_css();
		wp_add_inline_style( 'consultation-hours-style', $default_css );
	}
}
add_action( 'wp_enqueue_scripts', 'consultation_hours_enqueue_plugin_styles' );

// ----------------------------------------------------------------
// 管理画面の設定ページ
// ----------------------------------------------------------------

function consultation_hours_add_admin_menu() {
	add_options_page(
		__( 'Consultation Hours Settings', 'consultation-hours' ),
		__( 'Consultation Hours', 'consultation-hours' ),
		'manage_options',
		'consultation-hours',
		'consultation_hours_settings_page'
	);
}
add_action( 'admin_menu', 'consultation_hours_add_admin_menu' );

function consultation_hours_settings_page() {
	$data          = get_option( 'consultation_hours_table_data', [] );
	$notes         = get_option( 'consultation_hours_notes', [] );
	$default_hours = consultation_hours_get_default_hours();
	$saved_hours   = get_option( 'consultation_hours_hours', $default_hours );
	$hours         = array_replace_recursive( $default_hours, $saved_hours );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Consultation Hours Settings', 'consultation-hours' ); ?></h1>
		<?php settings_errors( 'consultation_hours_messages' ); ?>
		<form method="post">
			<?php wp_nonce_field( 'consultation_hours_data_submit' ); ?>
			<h2><?php esc_html_e( 'Time Slot Settings', 'consultation-hours' ); ?></h2>
			<?php foreach ( $hours as $key => $info ) : ?>
				<p>
					<label>
						<input type="checkbox" name="consultation_hours_hours[<?php echo esc_attr( $key ); ?>][enabled]" value="1" <?php checked( $info['enabled'] ); ?> />
						<?php echo esc_html( $info['label'] ); ?>
					</label>
					<label>
						<?php esc_html_e( 'Time:', 'consultation-hours' ); ?>
						<input type="text" name="consultation_hours_hours[<?php echo esc_attr( $key ); ?>][time]" value="<?php echo esc_attr( $info['time'] ); ?>" size="20" />
					</label>
					<label>
						<?php esc_html_e( 'Label:', 'consultation-hours' ); ?>
						<input type="text" name="consultation_hours_hours[<?php echo esc_attr( $key ); ?>][label]" value="<?php echo esc_attr( $info['label'] ); ?>" size="20" />
					</label>
				</p>
			<?php endforeach; ?>

			<h2><?php esc_html_e( 'Enter Consultation Hours', 'consultation-hours' ); ?></h2>
			<table class="wp-list-table widefat striped">
				<thead>
					<tr>
						<th></th>
						<?php foreach ( [ 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun' ] as $day ) : ?>
							<th><?php echo esc_html( $day ); ?></th>
						<?php endforeach; ?>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $hours as $slot => $info ) : if ( ! $info['enabled'] ) { continue; } ?>
					<tr>
						<td><?php echo esc_html( $info['label'] ) . ' ' . esc_html( $info['time'] ); ?></td>
						<?php foreach ( range( 0, 6 ) as $d ) : ?>
							<td>
								<select name="consultation_hours_table_data[<?php echo esc_attr( $slot ); ?>][<?php echo esc_attr( $d ); ?>]">
									<option value="●" <?php selected( $data[ $slot ][ $d ] ?? '', '●' ); ?>>●</option>
									<option value="★" <?php selected( $data[ $slot ][ $d ] ?? '', '★' ); ?>>★</option>
									<option value="／" <?php selected( $data[ $slot ][ $d ] ?? '', '／' ); ?>>／</option>
								</select>
							</td>
						<?php endforeach; ?>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>

			<h2><?php esc_html_e( 'Notes (Max 10)', 'consultation-hours' ); ?></h2>
			<?php for ( $i = 0; $i < 10; $i++ ) : ?>
				<p><input type="text" name="consultation_hours_notes[]" value="<?php echo esc_attr( $notes[ $i ] ?? '' ); ?>" class="large-text" /></p>
			<?php endfor; ?>

			<?php submit_button( __( 'Save Settings', 'consultation-hours' ), 'primary', 'consultation_hours_data_submit' ); ?>
		</form>
	</div>
	<?php
}