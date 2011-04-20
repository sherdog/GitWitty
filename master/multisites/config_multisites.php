<?php
if( !defined( '_EDWIN2WIN_' ) && !defined( '_JEXEC' )) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 

if( !defined( 'MULTISITES_MASTER_ROOT_PATH' )) {
   define( 'MULTISITES_MASTER_ROOT_PATH', '/home/gitwitty/public_html/master');
}

$md_hostalias = array( 'tagmyvideo.com' => array( array( 'url' => 'http://tagmyvideo.com', 'site_id' => 'tagmyvid')),
                       'www.tagmyvideo.com' => array( array( 'url' => 'http://www.tagmyvideo.com', 'site_id' => 'tagmyvid')));
?>