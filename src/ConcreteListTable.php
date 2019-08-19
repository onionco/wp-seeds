<?php

if(!class_exists('WP_List_Table'))
	require_once(ABSPATH.'wp-admin/includes/class-wp-list-table.php');

class ConcreteListTable extends WP_List_Table {
	var $columns;

	function __construct() {
		parent::__construct();

		$this->columns=array();
	}

	function addFieldColumn($title, $field) {
		$index=sizeof($this->columns);
		$key="_$index";

		$this->columns[$key]=array(
			"key"=>$key,
			"title"=>$title,
			"field"=>$field
		);
	}

	function addFuncColumn($title, $func) {
		$index=sizeof($this->columns);
		$key="_$index";

		$this->columns[$key]=array(
			"key"=>$key,
			"title"=>$title,
			"func"=>$func
		);
	}

	function get_columns(){
		$cols=array();

		foreach ($this->columns as $column)
			$cols[$column["key"]]=$column["title"];

		return $cols;
    }

	function column_default($item, $columnName) {
		$colSpec=$this->columns[$columnName];

		if ($colSpec["field"]) {
			if (is_object($item))
				return $item->$columnName;

			else
				return $item[$colSpec["field"]];
		}

		else if ($colSpec["func"]) {
			return $colSpec["func"]($item);
		}
	}

    function setData($data) {
    	$this->items=$data;
    	$this->prepare_items();
    }

    function prepare_items() {
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        //$this->process_bulk_action();
    }
}
