<?php
/**
 * @copyright	Copyright (C) 2009-2011 ACYBA SARL - All rights reserved.
 * @license		http://www.acyba.com/commercial_license.php
 */
defined('_JEXEC') or die('Restricted access');
?>
<?php
$my =& JFactory::getUser();
if(empty($my->id)) die('You can not have access to this page');
include(ACYMAILING_BACK.'controllers'.DS.'template.php');
class FronttemplateController extends TemplateController{
}