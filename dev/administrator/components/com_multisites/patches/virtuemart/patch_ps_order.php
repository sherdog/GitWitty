//_jms2win_begin v1.1.0
JPluginHelper::importPlugin('multisites');
$dispatcher	=& JDispatcher::getInstance();
$results = $dispatcher->trigger('onOrderStatusUpdate', array ( & $d));
//_jms2win_end
