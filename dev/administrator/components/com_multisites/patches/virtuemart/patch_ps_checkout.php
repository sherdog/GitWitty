//_jms2win_begin v1.1.0
$d["order_number"] = $order_number;
JPluginHelper::importPlugin('multisites');
$dispatcher	=& JDispatcher::getInstance();
$results = $dispatcher->trigger('onAfterOrderCreate', array ( & $d));
//_jms2win_end
