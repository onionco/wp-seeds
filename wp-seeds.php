<?php

/**
 * Plugin Name:       Seeds
 * Plugin URI:        https://github.com/limikael/wp-seeds
 * GitHub Plugin URI: https://github.com/limikael/wp-seeds
 * Description:       Transferrable tokens.
 * Version:           0.0.1
 * Author:            Mikael Lindqvist
 * License:           GNU General Public License v2
 */

require_once __DIR__."/src/lib.php";
require_once __DIR__."/src/ConcreteListTable.php";

function seeds_accounts_page() {
	$vars=array();

	$listTable=new ConcreteListTable();
	$listTable->addFieldColumn("User","name");
	$listTable->addFieldColumn("Email","email");
	$listTable->addFieldColumn("Balance","balance");
	$listTable->addFieldColumn("Transactions","transactions");

	$users=array();
	foreach (get_users() as $user) {
		$balance=get_user_meta($user->ID,"seeds_balance",TRUE);
		if (!$balance)
			$balance=0;

		$users[]=array(
			"name"=>$user->data->user_nicename,
			"email"=>$user->data->user_email,
			"balance"=>$balance,
			"transactions"=>"<a href=''>1</a>",
		);
	}

	$listTable->setData($users);
	$vars["table"]=$listTable;

	render_template(__DIR__."/tpl/seeds_accounts.tpl.php",$vars);
}

function seeds_create_page() {
	$vars=array();

	$vars["errorMessage"]="";
	$vars["amount"]="";
	$vars["userId"]="";

	if (isset($_REQUEST["amount"])) {
		if (!$_REQUEST["amount"])
			$vars["errorMessage"]="You need to select an amount.";

		if (!$_REQUEST["userId"])
			$vars["errorMessage"]="You need to select an account.";

		if ($vars["errorMessage"]) {
			$vars["amount"]=$_REQUEST["amount"];
			$vars["userId"]=$_REQUEST["userId"];
		}

		else {
			$user=get_user_by("id",$_REQUEST["userId"]);
			$balance=intval(get_user_meta($user->ID,"seeds_balance",TRUE));
			$balance+=intval($_REQUEST["amount"]);
			update_user_meta($user->ID,"seeds_balance",$balance);

			render_template(__DIR__."/tpl/seeds_create_done.tpl.php",$vars);
			return;
		}
	}

	$vars["action"]=get_admin_url(NULL,"admin.php?page=seeds_create");
	$vars["users"]=array();
	foreach (get_users() as $user) {
		$vars["users"][]=array(
			"id"=>$user->ID,
			"label"=>$user->data->user_nicename."( ".$user->data->user_email.")"
		);
	}

	render_template(__DIR__."/tpl/seeds_create.tpl.php",$vars);
}

function seeds_transactions_page() {
	$vars=array();

	render_template(__DIR__."/tpl/seeds_transactions.tpl.php",$vars);
}

function seeds_admin_menu() {
	add_menu_page("Seeds","Seeds",
		"manage_options","seeds_accounts","seeds_accounts_page",
		"dashicons-money",71);

	add_submenu_page("seeds_accounts","Seed Accounts","Accounts",
		"manage_options","seeds_accounts","seeds_accounts_page");

	add_submenu_page("seeds_accounts","Seed Transactions","Transactions",
		"manage_options","seeds_transactions","seeds_transactions_page");

	add_submenu_page("seeds_accounts","Create Seeds","Create Seeds",
		"manage_options","seeds_create","seeds_create_page");
}

add_action("admin_menu","seeds_admin_menu");