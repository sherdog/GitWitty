<?php

/**
 * @author W-Shadow
 * @copyright 2008
 */
 
/**
 * This script shows a simple example of how you can use the cPanelAPI class.
 * 
 * First, it lists all existing MySQL databases. Then, it adds a new database using 
 * the mysql_easy_create() method that automatically creates a database and a user 
 * and assigns that user to the database with full access privileges. Finally the script
 * displays the list of existing databases again to show that the new database has been
 * created. 
 *   
 **/

error_reporting(E_ALL);

require_once 'includes/ecurl.class.php';
require_once 'includes/cpanel.class.php';

$cpanel_user = 'gitwitty';
$cpanel_password = 'p!p@cw!tty';
$cpanel_host = 'gitwitty.com';
$cpanel_skin = 'x3'; //not tested with other skins, might need some small adjustments.

$cpanel = new cPanelAPI($cpanel_host, $cpanel_user, $cpanel_password, $cpanel_skin);

echo '<pre>';
//Get the current MySQL databases
$rez = $cpanel->mysql_list_databases();
echo "<strong>Current MySQL databases</strong> : \n",print_r($rez, true),"\n";

//Create a new database "example" with a user of the same name and a random password.
//Note that your cPanel username will be appended to the db/username by cPanel, 
//but this script will not reflect that. 
$rez = $cpanel->mysql_easy_create('example');
echo "<strong>New database</strong> : \n",print_r($rez, true), "\n";

//List existing databases again to show the new database.
$rez = $cpanel->mysql_list_databases();
echo "<strong>MySQL database list after a new DB was added</strong> : \n",print_r($rez, true);

echo '</pre>';
?>