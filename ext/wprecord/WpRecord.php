<?php

if ( ! class_exists( 'WpRecord' ) ) {

	// WordPress
	if ( defined( 'ABSPATH' ) ) {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	}

	/**
	 * Simple active record implementation which adapts itself to the database
	 * environment used.
	 *
	 * Currently only WordPress is supported, but it is easy to add other environments
	 * eventually. An environment in this context is a method to connect to a database,
	 * such as PDO. PDO would be the next obvious method to add support for.
	 *
	 * The basic usage is to create a class that extends SmartRecord, and implement
	 * a static method called initialize, which sets up the fields used by the class.
	 *
	 * <code>
	 * class MyClass extends WpRecord {
	 *     static function initialize() {
	 *         self::field("id", "integer not null auto_increment");
	 *         self::field("sometext", "varchar(255) not null");
	 *     }
	 * }
	 * </code>
	 *
	 * Now, we can call the install function, and have the schema syncronized to the
	 * underlying database. This only needs to be done whenever our schema changes,
	 * so a good place to do it is in plugin activation or similar.
	 *
	 * <code>
	 * MyClass::install();
	 * </code>
	 *
	 * Then we can create an instance of the class and save it to the database.
	 *
	 * <code>
	 * $mc=new MyClass();
	 * $mc->sometext="hello";
	 * $mc->save();
	 * </code>
	 */
	class WpRecord {

		private static $classes = array();

		// todo: make it possible to use pdo
		// private static $pdo;

		/**
		 * Get full table name.
		 */
		final public static function getFullTableName() {
			self::init();

			return self::$classes[ get_called_class() ]['table'];
		}

		/**
		 * Add field.
		 */
		final protected static function field( $name, $definition ) {
			if ( ! isset( self::$classes[ get_called_class() ]['primaryKey'] ) ) {
				self::$classes[ get_called_class() ]['primaryKey'] = $name;
			}

			self::$classes[ get_called_class() ]['fields'][ $name ] = $definition;
		}

		/**
		 * Init.
		 */
		final private static function init() {
			global $wpdb;

			$class = get_called_class();

			if ( isset( self::$classes[ $class ] ) ) {
				return;
			}

			self::$classes[ $class ] = array( 'fields' => array() );

			$a                                = explode( '\\', get_called_class() );
			$s                                = strtolower( $a[ sizeof( $a ) - 1 ] );
			self::$classes[ $class ]['table'] = self::getTablePrefix() . $s;

			static::initialize();
		}

		/**
		 * Create underlying table.
		 */
		final public static function install() {
			global $wpdb;

			self::init();

			$table      = self::$classes[ get_called_class() ]['table'];
			$fields     = self::$classes[ get_called_class() ]['fields'];
			$primaryKey = self::$classes[ get_called_class() ]['primaryKey'];

			// Create table if it doesn't exist.
			$qs = 'CREATE TABLE IF NOT EXISTS ' . $table . ' (';

			foreach ( $fields as $name => $declaration ) {
				$qs .= $name . ' ' . $declaration . ', ';
			}

			$qs .= 'primary key(' . $primaryKey . '))';

			self::query( $qs );

			// Check current state of database.
			$describeResult = self::query( 'DESCRIBE ' . $table );

			$existing = array();
			foreach ( $describeResult as $describeRow ) {
				$existing[] = $describeRow['Field'];
			}

			// Create or modify existing fields.
			foreach ( $fields as $name => $declaration ) {
				if ( in_array( $name, $existing ) ) {
					$q = "ALTER TABLE `$table` MODIFY $name $declaration";
				} else {
					$q = "ALTER TABLE `$table` ADD `$name` $declaration";
				}

				self::query( $q );
			}

			// Drup unused fields.
			$currentFieldNames = array_keys( $fields );
			foreach ( $existing as $existingField ) {
				if ( ! in_array( $existingField, $currentFieldNames ) ) {
					self::query( "ALTER TABLE $table DROP $existingField" );
				}
			}
		}

		/**
		 * Drop table if it exists.
		 */
		final public static function uninstall() {
			global $wpdb;

			self::init();

			$table = self::$classes[ get_called_class() ]['table'];
			$wpdb->query( "DROP TABLE IF EXISTS $table" );
		}

		/**
		 * Get value for primary key.
		 */
		private function getPrimaryKeyValue() {
			$conf = self::$classes[ get_called_class() ];
			$pk   = $conf['primaryKey'];

			if ( ! isset( $this->$pk ) ) {
				return null;
			}

			return $this->$pk;
		}

		/**
		 * Get conf.
		 */
		private static function getConf() {
			self::init();
			return self::$classes[ get_called_class() ];
		}

		/**
		 * Save.
		 */
		public function save() {
			$conf = self::getConf();

			$pk = $this->getPrimaryKeyValue();
			$s  = '';

			if ( $pk ) {
				$s .= "UPDATE $conf[table] SET ";

			} else {
				$s .= "INSERT INTO $conf[table] SET ";
			}

			$params = array();

			$first = true;
			foreach ( $conf['fields'] as $field => $declaration ) {
				if ( $field != $conf['primaryKey'] ) {
					if ( ! $first ) {
						$s .= ', ';
					}

					$s    .= "$field=%s";
					$first = false;

					if ( isset( $this->$field ) ) {
						$params[] = $this->$field;

					} else {
						$params[] = null;
					}
				}
			}

			if ( $pk ) {
				$s       .= " WHERE $conf[primaryKey]=%s";
				$params[] = $this->getPrimaryKeyValue();
			}

			$statement = self::query( $s, $params );

			if ( ! $this->getPrimaryKeyValue() ) {
				$primaryKeyField        = $conf['primaryKey'];
				$this->$primaryKeyField = self::lastInsertId();
			}
		}

		/**
		 * Delete this item.
		 */
		final public function delete() {
			self::init();
			$conf = self::$classes[ get_called_class() ];

			if ( ! $this->getPrimaryKeyValue() ) {
				throw new Exception( "Can't delete, there is no id" );
			}

			self::query(
				"DELETE FROM :table WHERE $conf[primaryKey]=%s",
				array(
					$this->getPrimaryKeyValue(),
				)
			);

			$primaryKeyField = $conf['primaryKey'];
			unset( $this->$primaryKeyField );
		}

		/**
		 * Find all by query.
		 */
		final public static function findAllByQuery( $query /* ... */ ) {
			$conf   = self::getConf();
			$class  = get_called_class();
			$fields = self::$classes[ get_called_class() ]['fields'];

			$params    = self::flattenArray( array_slice( func_get_args(), 1 ) );
			$queryRows = self::query( $query, $params );

			$res = array();

			foreach ( $queryRows as $queryRow ) {
				$o = new $class();

				foreach ( $fields as $field => $declaration ) {
					$o->$field = $queryRow[ $field ];
				}

				$res[] = $o;
			}

			return $res;
		}

		/**
		 * Find one by query.
		 */
		final public static function findOneByQuery( $query /* ... */ ) {
			$params = self::flattenArray( array_slice( func_get_args(), 1 ) );
			$all    = self::findAllByQuery( $query, $params );

			if ( ! sizeof( $all ) ) {
				return null;
			}

			return $all[0];
		}

		/**
		 * Find all.
		 */
		final public static function findAll() {
			return self::findAllByQuery( 'SELECT * FROM :table' );
		}

		/**
		 * Find all by value.
		 */
		final public static function findAllBy( $field, $value = null ) {
			if ( is_array( $field ) ) {
				$args = $field;

			} else {
				$args = array( $field => $value );
			}

			$q      = 'SELECT * FROM :table WHERE ';
			$qa     = array();
			$params = array();

			foreach ( $args as $key => $value ) {
				$qa[]     = "$key=%s";
				$params[] = $value;
			}

			$q .= join( ' AND ', $qa );

			return self::findAllByQuery( $q, $params );
		}

		/**
		 * Find one by value.
		 */
		final public static function findOneBy( $field, $value = null ) {
			$res = self::findAllBy( $field, $value );

			if ( ! sizeof( $res ) ) {
				return null;
			}

			return $res[0];
		}

		/**
		 * Find one by id.
		 */
		final public static function findOne( $id ) {
			$conf = self::getConf();

			return self::findOneBy( $conf['primaryKey'], $id );
		}

		/**
		 * Get table prefix.
		 */
		final private static function getTablePrefix() {
			if ( defined( 'ABSPATH' ) ) {
				global $wpdb;

				return $wpdb->prefix;
			}

			return '';
		}

		/**
		 * Run query with parameters.
		 * The parameters are varadic!
		 */
		final private static function query( $q /* ... */ ) {
			$params = self::flattenArray( array_slice( func_get_args(), 1 ) );

			// echo "q: ".$q." p: ".print_r($params, TRUE);

			if ( defined( 'ABSPATH' ) ) {
				global $wpdb;

				$q = str_replace( ':table', self::getFullTableName(), $q );
				$q = str_replace( '%table', self::getFullTableName(), $q );
				$q = str_replace( '%t', self::getFullTableName(), $q );

				if ( sizeof( $params ) ) {
					$arg = array_merge( array( $q ), $params );
					// print_r($arg);

					$q = call_user_func_array( array( $wpdb, 'prepare' ), $arg );
				}

				$res = $wpdb->get_results( $q, ARRAY_A );
				if ( $wpdb->last_error ) {
					throw new Exception( $wpdb->last_error );
				}

				if ( $res === null ) {
					throw new Exception( 'Unknown error' );
				}

				return $res;
			} else {
				throw new Exception( 'Unknown environment' );
			}
		}

		/**
		 * Flatten an array, or make a non array into an array.
		 */
		public static function flattenArray( $a ) {
			if ( is_array( $a ) && ! $a ) {
				return array();
			}

			if ( ! is_array( $a ) ) {
				$a = array( $a );
			}

			$res = array();

			foreach ( $a as $item ) {
				if ( is_array( $item ) ) {
					$res = array_merge( $res, $item );

				} else {
					$res[] = $item;
				}
			}

			return $res;
		}

		/**
		 * Run query with parameters.
		 */
		final private static function lastInsertId() {
			if ( defined( 'ABSPATH' ) ) {
				global $wpdb;

				return $wpdb->insert_id;
			} else {
				throw new Exception( 'Unknown environment' );
			}
		}
	}
} // if (!class_exists("WpRecord"))
