<?php
require_once dirname(__FILE__).DS.'helper.php';
$featuredImage = JURI::root().'images'.DS.'features'.DS.modGwFeatureHelper::getSelectedFeature($params);
$callToActionButton = modGwFeatureHelper::getCalloutButton($params);
require( JModuleHelper::getLayoutPath( 'mod_gw_feature') );
?>