<?php

class modGwFeatureHelper

{

	/**
	* Retrieves current feature image user has selected
	*/
	function getSelectedFeature($params) 
	{
		return $image = $params->get('callout_images');
	}
	
	function getCalloutButton($params)
	{
		$app =& JFactory::getApplication();
		JLoader::register('JAddons', JPATH_THEMES.DS.$app->getTemplate().DS.'read_tpl_params.php');
		$paramval  = JURI::root().'templates'.DS.$app->getTemplate().DS.'images'.DS.JAddons::getTplParams()->get('call_to_action_button');
		return $paramval;
	}

}

?>