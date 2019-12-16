<?php

require_once plugin_dir_path( __FILE__ ) . '/../ext/wprecord/WpRecord.php';

class Transaction extends WpRecord {
	public static function initialize() {
		self::field("id","integer not null auto_increment");
		self::field("sender","integer not null");
		self::field("receiver","integer not null");
		self::field("amount","integer not null");
		self::field("timestamp","integer not null");
		self::field("notes","text");
	}
}
