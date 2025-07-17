<?php
// ファイルへの直接アクセスを禁止
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$data          = get_option( 'consultation_hours_table_data', [] );
$notes         = get_option( 'consultation_hours_notes', [] );
$default_hours = consultation_hours_get_default_hours();
$saved_hours   = get_option( 'consultation_hours_hours', $default_hours );
$hours         = array_replace_recursive( $default_hours, $saved_hours );

$days_of_week = [ 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun' ];
?>

<table class="consultation-hours">
	<thead>
		<tr>
			<th> </th>
			<?php foreach ( $days_of_week as $index => $day ) : ?>
				<th class="<?php echo ( 6 === $index ) ? 'sunday' : ''; ?>"><?php echo esc_html( $day ); ?></th>
			<?php endforeach; ?>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $hours as $slot => $info ) : if ( ! $info['enabled'] ) { continue; } ?>
		<tr>
			<td class="time-label"><?php echo esc_html( $info['label'] ); ?><br><?php echo esc_html( $info['time'] ); ?></td>
			<?php foreach ( range( 0, 6 ) as $d ) : ?>
				<?php
				$symbol = $data[ $slot ][ $d ] ?? '／';
				$class  = '';
				if ( '★' === $symbol ) {
					$class = 'star';
				} elseif ( '／' === $symbol ) {
					$class = 'closed';
				}
				?>
				<td class="<?php echo esc_attr( $class ); ?>"><?php echo esc_html( $symbol ); ?></td>
			<?php endforeach; ?>
		</tr>
		<?php endforeach; ?>
	</tbody>
	<?php if ( ! empty( array_filter( $notes ) ) ) : ?>
	<tfoot>
		<tr>
			<td colspan="8" class="consultation-notes">
				<p><?php esc_html_e( 'Notes', 'consultation-hours' ); ?></p>
				<ul>
					<?php foreach ( $notes as $note ) : if ( ! empty( $note ) ) : ?>
						<li><?php echo esc_html( $note ); ?></li>
					<?php endif; endforeach; ?>
				</ul>
			</td>
		</tr>
	</tfoot>
	<?php endif; ?>
</table>