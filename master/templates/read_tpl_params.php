<?php defined('_JEXEC') or die('Restricted access');

class JAddons{
   
   static public function getTplParams(){
      $app =& JFactory::getApplication();
      $cont = null;
      $ini   = JPATH_THEMES.DS.$app->getTemplate().DS.'params.ini';
      $xml   = JPATH_THEMES.DS.$app->getTemplate().DS.'templateDetails.xml';
      jimport('joomla.filesystem.file');
      if (JFile::exists($ini)) {
         $cont = JFile::read($ini);
      } else {
         $cont = null;
      }
      return new JParameter($cont, $xml, $app->getTemplate());      
   }
}

?>