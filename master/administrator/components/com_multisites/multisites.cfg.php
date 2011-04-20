<?php



/**

 * This allow to change the default directory rights.

 * Some customers want to use 777 because the Joomla user and Extension user is not the same owner

 * We think this is linked to "fantastiso"

 */

define( 'MULTISITES_DIR_RIGHTS', 0755);



// The following DB parameters may be used to create new users into MySQL

define( 'MULTISITES_DB_GRANT_HOST', '');     // IP or Server name. (GRANT ... TO <username>@<MULTISITES_DB_GRANT_HOST> ....)

                                             // When empty or not defined, it uses the $_SERVER environment LOCAL_ADDR or SERVER_ADDR.

                                             // If found localhost and "to DB" is located on another server, the GRANT will use the wildcard '%' as host

                                             // Remark: 

                                             // localhost or 127.0.0.1 is NOT recommended 

                                             // when the DB server is NOT present on the same machine (localhost).

define( 'MULTISITES_DB_ROOT_USER', 'gitwitty_dbadmin');      // MySQL root login user to allow create user (GRANT).

                                             // By default, this is the User name provided in the "from DB" to copy.

define( 'MULTISITES_DB_ROOT_PSW', 'db@dm1n');       // MySQL root password. Only used when the previous ROOT_USER define is present

?>