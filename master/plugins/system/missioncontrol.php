<?php
/**
 * @version   0.1.4 December 6, 2010
 * @author    RocketTheme http://www.rockettheme.com
 * @copyright Copyright (C) 2007 - 2010 RocketTheme, LLC
 * @license   http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */
// no direct access
defined( '_JEXEC' ) or die( 'Restricted index access' );

jimport('joomla.plugin.plugin');

class plgSystemMissionControl extends JPlugin
{

	function plgSystemMissionControl(& $subject, $config)
	{
		parent::__construct($subject, $config);
	}


	function onAfterRoute()
	{
		global $mainframe;

        $option = JRequest::getString('option');
		
		$output = "<?php \n";
	

		$tid = JRequest::getString('id');
		
		if($mainframe->isAdmin() && $mainframe->getTemplate() == "rt_missioncontrol_j15") {


    
	        if ($this->params->get('blacklist')) {

	        	$extensions = explode(',',$this->params->get('blacklist'));
                $template = 'rt_missioncontrol_j15';

                if ($this->params->get('patching')==1 && is_array($extensions) && sizeof($extensions)>0) plgSystemMissionControl::checkPatched();

	        	foreach ($extensions as $ext) {
	        		if (trim($ext) == $option) {
	        		    $mainframe->getTemplate('khepri');
	        		}
	        	}
            }
		}


		// is user in admin area?
		if($mainframe->isAdmin() && $tid=='rt_missioncontrol_j15') {
			// in admin area
		
		    if ($template == "rt_missioncontrol_j15"
			   && JRequest::getString('option','','post')=='com_templates'
			   && (JRequest::getString('task','','post')=='apply' || JRequest::getString('task','','post')=='save')) {
				
				
				$params = JRequest::getVar('params','','post');
		
				
				foreach($params as $key=>$value) {
				
					if (strpos($key,'_color')>0) {
						$output .= '$'.$key.'="'.$value.'";';
					}
				
				}
				
				$path = JPATH_ADMINISTRATOR.DS.'templates'.DS.$template.DS.'css'.DS.'color-vars.php';
				
				jimport( 'joomla.filesystem.file' );
				JFile::write($path,$output);
	
				return;
			}
		} 
		
		
	}

    function checkPatched() {
        jimport('joomla.filesystem.file');

        $admin_app = JFile::read('includes/application.php');

        if (strpos($admin_app,'getTemplate()')) {

            $admin_app = str_replace('getTemplate()','getTemplate($temptemplate=null)', $admin_app);
            $admin_app = str_replace('static $template;','static $template;
        if ($temptemplate) $template=$temptemplate;', $admin_app);

            JFile::write('includes/application.php',$admin_app);

        }

    }



	
}
