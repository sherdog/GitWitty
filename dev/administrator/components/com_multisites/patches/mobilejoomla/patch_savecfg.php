//_jms2win_begin v1.2.45
		// If this is a Slave Site, let use the standard format
		if ( defined( 'MULTISITES_ID')) {
      	$configfname = JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_mobilejoomla'.DS.'config.' .MULTISITES_ID. '.php';
		}
		// If master, add the wrapper
		else {
      	$config = '<'.'?php'."\n"
      			. "defined( '_JEXEC' ) or die( 'Restricted access' );\n"
      			. "\n"
      			. "//_jms2win_begin v1.2.45\n"
      			. "if ( defined( 'MULTISITES_ID')\n"
      			. "  && file_exists( dirname(__FILE__) .DS. 'config.' .MULTISITES_ID. '.php')) {\n"
      			. "   require_once(  dirname(__FILE__) .DS. 'config.' .MULTISITES_ID. '.php');\n"
      			. "} else if ( empty( \$MobileJoomla_Settings)) {\n"
      			. "//_jms2win_end\n"
      			. "\$MobileJoomla_Settings=array(\n"
      			. "'version'=>'".HTML_mobilejoomla::getMJVersion()."',\n"
      			. implode(",\n",$params)."\n"
      			. ");\n"
               . "//_jms2win_begin\n"
               . "}\n"
               . "//_jms2win_end\n"
      			. "?>";
		}
//_jms2win_end
