<?php

/**
 * This allow to change the default directory rights.
 * Some customers want to use 777 because the Joomla user and Extension user is not the same owner
 * We think this is linked to "fantastiso"
 */
define( 'MULTISITES_DIR_RIGHTS', 0755);

// define( 'MULTISITES_TLD_PARSING', false);       // Parse the URL using the Top Level Domain processing. When not defined, it is assume this is true
// define( 'MULTISITES_LETTER_TREE', true);        // Use a letter tree to have the Multisites "Slave site" configuration and therefore reduce the number of files/folders at each level. When not defined, it is assume this is false (flat directory structure).
// define( 'MULTISITES_REFRESH_DISABLED', true);   // When a large number of slave sites is expected, this allow disable the refresh icon that require to count the number of tables in each slave site.

// The following DB parameters may be used to create new users into MySQL
define( 'MULTISITES_DB_GRANT_HOST', '');     // IP or Server name. (GRANT ... TO <username>@<MULTISITES_DB_GRANT_HOST> ....)
                                             // When empty or not defined, it uses the $_SERVER environment LOCAL_ADDR or SERVER_ADDR.
                                             // If found localhost and "to DB" is located on another server, the GRANT will use the wildcard '%' as host
                                             // Remark: 
                                             // localhost or 127.0.0.1 is NOT recommended 
                                             // when the DB server is NOT present on the same machine (localhost).
define( 'MULTISITES_DB_ROOT_USER', '');      // MySQL root login user to allow create user (GRANT).
                                             // By default, this is the User name provided in the "from DB" to copy.
define( 'MULTISITES_DB_ROOT_PSW', '');       // MySQL root password. Only used when the previous ROOT_USER define is present
?>