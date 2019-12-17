<?php
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}
class ConcreteListTable extends WP_List_Table {
	var $columns;
	var $filters;
	var $title;
	function __construct() {
		parent::__construct();
		$this->columns = array();
		$this->filters = array();
	}
	function setTitle($title) {
		$this->title=$title;
	}
	function addFilter( $filterSpec ) {
		if ( ! $filterSpec['key'] ) {
			$filterSpec['key'] = '_' . sizeof( $this->filters );
		}
		$this->filters[ $filterSpec['key'] ] = $filterSpec;
	}
	function addColumn( $columnSpec ) {
		if ( ! $columnSpec['key'] ) {
			if (isset($columnSpec['field'])) {
				$columnSpec['key'] = $columnSpec['field'];
			}

			else {
				$columnSpec['key'] = '_' . sizeof( $this->columns );
			}
		}

		$this->columns[ $columnSpec['key'] ] = $columnSpec;
	}

    public function __call( $name, $arguments ) {
        if ( in_array( $name, $this->compat_methods ) ) {
            return $this->$name( ...$arguments );
        }
        throw new Error("Undefined method: ".$name);
    }

	// Overridden in order to get rid of _wp_nounce and _wp_http_referer
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
	function extra_tablenav( $which ) {
		switch ( $which ) {
			case 'top':
				if ( $this->filters ) {
					echo "<div class='alignleft actions'>";
					foreach ( $this->filters as $filterSpec ) {
						echo "<select name='$filterSpec[key]'>";
						echo "<option value=''>".htmlspecialchars($filterSpec["allLabel"])."</option>";
						display_select_options($filterSpec["options"],$_REQUEST[$filterSpec["key"]]);
						echo "</select>";
					}
					echo "<input type='submit' class='button' value='Filter'>";
					echo '</div>';
				}
				break;
		}
	}
	function get_columns() {
		$cols = array();
		foreach ( $this->columns as $column ) {
			$cols[ $column['key'] ] = $column['title'];
		}
		return $cols;
	}
	function column_default( $item, $columnName ) {
		$colSpec = $this->columns[ $columnName ];
		if ( $colSpec['field'] ) {
			if ( is_object( $item ) ) {
				return $item->$columnName;
			} else {
				return $item[ $colSpec['field'] ];
			}
		} elseif ( $colSpec['func'] ) {
			return $colSpec['func']($item);
		}
	}
	function setData( $data ) {
		$this->items = $data;
		$this->prepare_items();
	}
	function get_sortable_columns() {
		$sortable_columns=array();

		foreach ($this->columns as $colSpec) {
			if ($colSpec["sortable"]) {
				$sortable_columns[$colSpec["key"]]=array($colSpec["key"],false);
			}
		}

		return $sortable_columns;
	}
	function prepare_items() {
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = $this->get_sortable_columns();
		$this->_column_headers = array( $columns, $hidden, $sortable );
		// $this->process_bulk_action();
		$this->set_pagination_args(
			array(
				'total_items' => sizeof( $this->items ),
				'per_page'    => sizeof( $this->items ),
				'total_pages' => 1,
			)
		);
	}
	function display() {
		$adiminUrl = get_admin_url( null, 'admin.php' );
		echo '<div class="wrap">';
		echo '<h1 class="wp-heading-inline">'.$this->title.'</h1>';
		echo "<form action='$adminUrl' method='get'>";
		echo "<input type='hidden' name='page' value='$_REQUEST[page]'/>";
		parent::display();
		echo '</form>';
		echo '</div>';
	}
}