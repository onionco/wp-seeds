<?php
/**
 * WP Seeds 🌱
 *
 * Manage list tables.
 *
 * @package   wp-seeds/inc
 * @link      https://github.com/limikael/wp-seeds
 * @author    Mikael Lindqvist & Niels Lange
 * @copyright 2019 Mikael Lindqvist & Niels Lange
 * @license   GPL v2 or later
 */

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

require_once __DIR__ . '/lib.php';

/**
 * Class for simplifying the usage of WP_List_Table.
 */
class Custom_List_Table extends WP_List_Table {

	/**
	 * The columns for the list table.
	 *
	 * @var array $columns
	 */
	private $columns;

	/**
	 * The filters for the list table.
	 *
	 * @var array $filters
	 */
	private $filters;

	/**
	 * The title for the list table.
	 *
	 * @var string $title
	 */
	private $title;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->columns = array();
		$this->filters = array();
	}

	/**
	 * Set the title for the list table.
	 *
	 * @param string $title The title for the list table.
	 * @return void
	 */
	public function set_title( $title ) {
		$this->title = $title;
	}

	/**
	 * Add a filter for the list table.
	 *
	 * @param array $filter_spec The specification of the filter.
	 * @return void
	 */
	public function add_filter( $filter_spec ) {
		if ( ! $filter_spec['key'] ) {
			$filter_spec['key'] = '_' . count( $this->filters );
		}
		$this->filters[ $filter_spec['key'] ] = $filter_spec;
	}

	/**
	 * Add a column to the list table.
	 *
	 * @param array $column_spec The specification of the column.
	 * @return void
	 */
	public function add_column( $column_spec ) {
		if ( ! $column_spec['key'] ) {
			if ( isset( $column_spec['field'] ) ) {
				$column_spec['key'] = $column_spec['field'];
			} else {
				$column_spec['key'] = '_' . count( $this->columns );
			}
		}

		$this->columns[ $column_spec['key'] ] = $column_spec;
	}

	/**
	 * This gets called when an unimplemented function is called.
	 * It is already in WP_List_Table, but we re-implement it here
	 * to be able to throw an exception if an undefined function is called.
	 *
	 * @param array $name The sname of the function being called.
	 * @param array $arguments The arguments to the function.
	 * @return mixed
	 * @throws Exception If the function is not defined.
	 */
	public function __call( $name, $arguments ) {
		if ( in_array( $name, $this->compat_methods, true ) ) {
			return $this->$name( ...$arguments );
		}
		throw new Exception( 'Undefined method: ' . $name );
	}

	/**
	 * Overridden in order to get rid of wp_nonce stuff.
	 * Maybe we should put it back.
	 *
	 * @param array $which Don't know what it is for, copy pasted code.
	 * @return mixed
	 */
	protected function display_tablenav( $which ) {
		?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">
			<?php if ( $this->has_items() ) : ?>
			<div class="alignleft actions bulkactions">
				<?php $this->bulk_actions( $which ); ?>
			</div>
				<?php
				endif;
				$this->extra_tablenav( $which );
				$this->pagination( $which );
			?>
			<br class="clear" />
		</div>
		<?php
	}

	/**
	 * Add filter UI.
	 *
	 * @param string $which Which part.
	 * @return void
	 */
	protected function extra_tablenav( $which ) {
		switch ( $which ) {
			case 'top':
				if ( $this->filters ) {
					echo "<div class='alignleft actions'>";
					foreach ( $this->filters as $filter_spec ) {
						echo "<select name='" . esc_attr( $filter_spec['key'] ) . "'>";
						echo "<option value=''>" . esc_html( $filter_spec['allLabel'] ) . '</option>';
						$current = get_req_var( $filter_spec['key'], '' );
						display_select_options( $filter_spec['options'], $current );
						echo '</select>';
					}
					echo "<input type='submit' class='button' value='Filter'>";
					echo '</div>';
				}
				break;
		}
	}

	/**
	 * Override the single_row function so we have a chance to add
	 * stuff to it.
	 *
	 * @param array $item The item to render.
	 */
	public function single_row( $item ) {
		if ( array_key_exists( '__class', $item ) ) {
			echo '<tr class="' . esc_attr( $item['__class'] ) . '">';
		} else {
			echo '<tr>';
		}
		$this->single_row_columns( $item );
		echo '</tr>';
	}

	/**
	 * Implementation of get_columns.
	 *
	 * @return array The columns.
	 */
	public function get_columns() {
		$cols = array();
		foreach ( $this->columns as $column ) {
			$cols[ $column['key'] ] = $column['title'];
		}
		return $cols;
	}

	/**
	 * Implementation of column_default.
	 *
	 * @param string $item Item.
	 * @param string $column_name Column name.
	 *
	 * @return array The columns.
	 */
	protected function column_default( $item, $column_name ) {
		$col_spec = $this->columns[ $column_name ];
		if ( $col_spec['field'] ) {
			if ( is_object( $item ) ) {
				return $item->$column_name;
			} else {
				return $item[ $col_spec['field'] ];
			}
		} elseif ( $col_spec['func'] ) {
			return $col_spec['func']($item);
		}
	}

	/**
	 * Set the data to be displayed.
	 *
	 * @param array $data The data.
	 *
	 * @return void
	 */
	public function set_data( $data ) {
		$this->items = $data;
		$this->prepare_items();
	}

	/**
	 * Implementation of get_sortable_columns.
	 *
	 * @return array The sortable columns.
	 */
	protected function get_sortable_columns() {
		$sortable_columns = array();

		foreach ( $this->columns as $col_spec ) {
			if ( $col_spec['sortable'] ) {
				$sortable_columns[ $col_spec['key'] ] = array( $col_spec['key'], false );
			}
		}

		return $sortable_columns;
	}

	/**
	 * Implementation of prepare_items.
	 *
	 * @return void
	 */
	public function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->set_pagination_args(
			array(
				'total_items' => count( $this->items ),
				'per_page'    => count( $this->items ),
				'total_pages' => 1,
			)
		);
	}

	/**
	 * Display the table.
	 *
	 * @return void
	 */
	public function display() {
		$adimin_url = get_admin_url( null, 'admin.php' );
		echo '<div class="wrap">';
		echo '<h1 class="wp-heading-inline">' . esc_attr( $this->title ) . '</h1>';
		echo "<form action='" . esc_attr( $admin_url ) . "' method='get'>";

		$page = get_req_var( 'page', '' );
		echo "<input type='hidden' name='page' value='" . esc_attr( $page ) . "'/>";
		parent::display();
		echo '</form>';
		echo '</div>';
	}
}
