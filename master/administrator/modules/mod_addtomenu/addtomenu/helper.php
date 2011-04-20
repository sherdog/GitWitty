<?php
/**
 * Module Helper File
 *
 * @package     Add to Menu
 * @version     1.6.0
 *
 * @author      Peter van Westen <peter@nonumber.nl>
 * @link        http://www.nonumber.nl
 * @copyright   Copyright © 2011 NoNumber! All Rights Reserved
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 */

// No direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

class modAddToMenu
{
	function render( &$params )
	{
		$option = JRequest::getCmd( 'option' );

		$this->vars = array();

		jimport( 'joomla.filesystem.folder' );
		jimport( 'joomla.filesystem.file' );

		require_once JPATH_PLUGINS.DS.'system'.DS.'nonumberelements'.DS.'helpers'.DS.'parameters.php';
		$parameters =& NNParameters::getParameters();

		$folder = dirname(__FILE__).DS.'components'.DS.$option;
		$comp_file = '';
		foreach ( JFolder::files( $folder, '.xml' ) as $filename ) {
			$file = $folder.DS.$filename;
			$xml =& JFactory::getXMLParser('Simple');
			$xml->loadFile($file);
			if ( isset( $xml->document ) && isset( $xml->document->_children ) ) {
				$template = $parameters->getObjectFromXML( $xml->document->_children );
				if ( isset( $template->params ) && isset( $template->params->required ) ) {
					if ( !is_object( $template->params->required ) || modAddToMenu::checkRequiredFields( $template->params->required ) ) {
						$template = $template->params;
						$comp_file = JFile::stripExt( $filename );
						break;
					}
				}
			}
		}

		if ( !$comp_file ) {
			return;
		}

		JHTML::_( 'behavior.modal' );

		require_once JPATH_PLUGINS.DS.'system'.DS.'nonumberelements'.DS.'helpers'.DS.'functions.php';
		$this->functions =& NNFunctions::getFunctions();
		$mt_version = $this->functions->getJSVersion();

		$document =& JFactory::getDocument();
		$document->addScript( JURI::root( true ).'/administrator/modules/mod_addtomenu/addtomenu/js/script'.$mt_version.'.js' );
		$document->addStyleSheet( JURI::root( true ).'/administrator/modules/mod_addtomenu/addtomenu/css/style.css' );

		// set height for popup
		$popup_height = 320;
		if ( isset( $template->adjust_height ) ) {
			$popup_height += $template->adjust_height;
		}
		if ( isset( $template->extras ) && is_object( $template->extras ) && isset( $template->extras->extra ) ) {
			if ( !is_array( $template->extras->extra ) ) {
				$template->extras->extra = array( $template->extras->extra );
			}
			$haselements = 0;
			// + heights of elements
			foreach( $template->extras->extra as $element ) {
				if ( isset( $element->type ) ) {
					$haselements = 1;
					switch( $element->type ) {
						case 'radio':
							// add height for every line
							$popup_height += 8 + ( 16 * count( $element['value'] ) );
							break;
						case 'textarea':
							$popup_height += 111;
							break;
						case 'hidden':
						case 'toggler':
							// no height
							break;
						default:
							$popup_height += 24;
							break;
					}
				}
			}
			if ( $haselements ) {
				// + height of title
				$popup_height += 23 ;
			}
		}

		$link = 'index.php?nn_qp=1';
		$link .= '&folder=administrator.modules.mod_addtomenu.addtomenu';
		$link .= '&file=addtomenu.inc.php';
		$link .= '&comp='.$comp_file;

		$uri =& JFactory::getURI();
		$url_query = $uri->getQuery( 1 );
		foreach ( $url_query as $key => $val ) {
			$this->vars[$key] = $val;
		}
		if ( !isset( $this->vars['option'] ) ) {
			$this->vars['option'] = $option;
		}
		foreach ( $this->vars as $key => $val ) {
			if ( is_array( $val ) ) {
				$val = $val['0'];
			}
			$link .= '&vars['.$key.']='.$val;
		}

		//$text = JText::_( $params->get( 'icon_text', 'ADD_TO_MENU' ) );
		$text = "Menu Manager";
		$title = $text;
		$class = '';
		if ( $params->get( 'display_link', 'both' ) == 'text' ) {
			$class = 'no_icon';
		} else if ( $params->get( 'display_link', 'both' ) == 'icon' ) {
			$text = '';
			$class = 'no_text';
		}

		if ( $params->get( 'display_tooltip', 1 ) ) {
			JHTML::_( 'behavior.tooltip' );
			$class .= ' hasTip';
			$title = JText::_( 'ADD_TO_MENU' ).'::'.JText::_( 'Add' ).': '.JText::_( $template->name );
		}

		echo '<a href="'.$link.'" onfocus="this.blur();" id="addtomenu" class="modal" rel="{handler: \'iframe\', size: {x: 400, y: 500}}"><span class="'.$class.'"  title="'.$title.'">'.$text.'</span></a>';
	}

	function getVar( $var ) {
		if ( $var['0'] == '$' ) {
			$var = substr( $var, 1 );
			$var = modAddToMenu::getVal( $var );
		}
		return $var;
	}

	function getVal( $value, $vars = '' ) {
		$url = JRequest::getVar( 'url' );
		$extra = JRequest::getVar( 'extra' );

		if( isset( $vars[$value] ) ) {
			$val = $vars[$value];
		} else if( isset( $url[$value] ) ) {
			$val = $url[$value];
		} else if( isset( $extra[$value] ) ) {
			$val = $extra[$value];
		} else {
			$val = JRequest::getVar( $value );
			if ( $val == '' ) {
				global $context;
				$mainframe =& JFactory::getApplication();
				$val = $mainframe->getUserStateFromRequest( $context.$value, $value );
			}
		}

		if ( is_array( $val ) ) {
			$val = $val['0'];
		}

		return $val;
	}

	function checkRequiredFields( &$required, $vars = '' ) {
		$pass = 1;
		foreach( $required as $key => $values ) {
			$keyval = modAddToMenu::getVal( $key, $vars );
			$values = explode( ',', $values );
			foreach ( $values as $val ) {
				$pass = 0;
				switch ( $val ) {
					case '*':
						if ( strlen( $keyval ) ) {
							$pass = 1;
						}
						break;
					case '+':
						if ( $keyval ) {
							$pass = 1;
						}
						break;
					default:
						if ( $keyval == $val ) {
							$pass = 1;
						}
						break;
				}
				if ( $pass ) {
					break;
				}
			}
			if ( !$pass ) {
				break;
			}
			$this->vars[$key] = $keyval;
		}
		return $pass;
	}
}