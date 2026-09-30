<?php
/**
 * The Customizer control for a list: a row of boxes per entry, with buttons to
 * add, remove and reorder rows. What it saves is the same text rad_list_rows()
 * reads — one row per line, parts separated by "|" — kept in a hidden box that
 * assets/js/customize-controls.js rewrites whenever a row changes.
 *
 * @package Redcliffe_Advisory
 */

defined( 'ABSPATH' ) || exit;

/**
 * A list the owner edits row by row.
 */
class RAD_List_Control extends WP_Customize_Control {

	/**
	 * Control type, used by the Customizer's JavaScript.
	 *
	 * @var string
	 */
	public $type = 'rad_list';

	/**
	 * Names of the parts of a row, e.g. Role, Organisation.
	 *
	 * @var string[]
	 */
	public $columns = array();

	/**
	 * The list's content key, e.g. who.currently.
	 *
	 * @var string
	 */
	public $list_key = '';

	/**
	 * The rows as boxes, with the saved text in a hidden field behind them.
	 */
	public function render_content() {
		$list     = rad_list_definition( $this->list_key );
		$defaults = $list ? $list['rows'] : array();
		$rows     = rad_list_parse( $this->value(), count( $this->columns ) );

		if ( ! $rows ) {
			$rows = $defaults;
		}
		?>
		<div class="rad-list" data-columns="<?php echo esc_attr( wp_json_encode( array_values( $this->columns ) ) ); ?>" data-defaults="<?php echo esc_attr( wp_json_encode( $defaults ) ); ?>">
			<?php if ( $this->label ) : ?>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php endif; ?>
			<?php if ( $this->description ) : ?>
				<span class="description customize-control-description"><?php echo esc_html( $this->description ); ?></span>
			<?php endif; ?>
			<ol class="rad-list-rows">
				<?php foreach ( $rows as $cells ) : ?>
					<?php $this->render_row( $cells ); ?>
				<?php endforeach; ?>
			</ol>
			<p class="rad-list-buttons">
				<button type="button" class="button rad-list-add"><?php esc_html_e( '+ Add a row', 'redcliffe-advisory' ); ?></button>
				<button type="button" class="button-link rad-list-reset"><?php esc_html_e( 'Put the original list back', 'redcliffe-advisory' ); ?></button>
			</p>
			<textarea class="rad-list-value" hidden <?php $this->link(); ?>><?php echo esc_textarea( $this->value() ); ?></textarea>
		</div>
		<?php
	}

	/**
	 * One row: a box per part and its buttons.
	 *
	 * @param string[] $cells The parts of the row.
	 */
	protected function render_row( $cells ) {
		?>
		<li class="rad-list-row">
			<span class="rad-list-cells">
				<?php foreach ( array_values( $this->columns ) as $n => $name ) : ?>
					<label class="rad-list-cell">
						<span class="screen-reader-text"><?php echo esc_html( $name ); ?></span>
						<input type="text" placeholder="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( isset( $cells[ $n ] ) ? wp_strip_all_tags( $cells[ $n ] ) : '' ); ?>" />
					</label>
				<?php endforeach; ?>
			</span>
			<span class="rad-list-actions">
				<button type="button" class="button rad-list-up" title="<?php esc_attr_e( 'Move up', 'redcliffe-advisory' ); ?>" aria-label="<?php esc_attr_e( 'Move up', 'redcliffe-advisory' ); ?>">&uarr;</button>
				<button type="button" class="button rad-list-down" title="<?php esc_attr_e( 'Move down', 'redcliffe-advisory' ); ?>" aria-label="<?php esc_attr_e( 'Move down', 'redcliffe-advisory' ); ?>">&darr;</button>
				<button type="button" class="button-link button-link-delete rad-list-remove"><?php esc_html_e( 'Remove', 'redcliffe-advisory' ); ?></button>
			</span>
		</li>
		<?php
	}
}
