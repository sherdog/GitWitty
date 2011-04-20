<?php
/**
 * Popup include page
 * Displays a list with modules
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

$vars = JRequest::getVar( 'vars' );
$option = $vars['option'];
$comp_file = JRequest::getVar( 'comp' );

$file = dirname(__FILE__).DS.'components'.DS.$option.DS.$comp_file.'.xml';

$template = '';
$xml =& JFactory::getXMLParser('Simple');
$xml->loadFile($file);
if ( isset( $xml->document ) && isset( $xml->document->_children ) ) {
	require_once JPATH_PLUGINS.DS.'system'.DS.'nonumberelements'.DS.'helpers'.DS.'parameters.php';
	$parameters =& NNParameters::getParameters();
	$xml_template = $parameters->getObjectFromXML( $xml->document->_children );
	if ( isset( $xml_template->params ) && isset( $xml_template->params->required ) ) {
		require_once dirname(__FILE__).DS.'helper.php';
		if ( !is_object( $xml_template->params->required ) || modAddToMenu::checkRequiredFields( $xml_template->params->required, $vars ) ) {
			$template = $xml_template->params;
		}
	}
}

if ( !$template ) {
	return;
}

$lang =& JFactory::getLanguage();
$lang->load( 'mod_addtomenu', JPATH_ADMINISTRATOR );
$lang->load( 'com_menus', JPATH_ADMINISTRATOR );
$lang->load( $comp_file, JPATH_ADMINISTRATOR );

$insert = JRequest::getVar( 'insert' );
if ( $insert ) {
	insertMenuItem( $template );
} else {
	$lang =& JFactory::getLanguage();
	$lang->load( $option, JPATH_ADMINISTRATOR );
	if( isset( $template->urlparams->option ) && $template->urlparams->option != $option ) {
		$lang->load( $template->urlparams->option, JPATH_ADMINISTRATOR );
		$lang->load( $template->urlparams->option.'.menu', JPATH_ADMINISTRATOR );
	}

	renderHTML( $template );
}

function insertMenuItem( &$template )
{
	$db =& JFactory::getDBO();

	$item =& JTable::getInstance( 'menu' );

	$item->name = JRequest::getVar( 'name', '' );
	$item->alias = JRequest::getVar( 'alias', '' );
	if( !strlen( $item->alias ) ) { $item->alias = $item->name; }
	$item->alias = filterAlias( $item->alias );

	$item->published = JRequest::getVar( 'published', 0 );
	$menuitem = JRequest::getVar( 'menuitem', 'mainmenu::0' );
	$menuitem = explode( '::', $menuitem );
	$item->menutype = $menuitem['0'];
	$item->parent = (int) $menuitem['1'];

	$item->sublevel = 0;
	if ( $item->parent ) {
		$query = 'SELECT `sublevel`' .
				' FROM #__menu' .
				' WHERE id = '.$item->parent.
				' LIMIT 1';
		$db->setQuery( $query );
		$item->sublevel = (int) $db->loadResult();
		$item->sublevel++;
	}

	$query = 'SELECT `ordering`' .
			' FROM #__menu' .
			' WHERE menutype = "'.$item->menutype.'"'.
			' AND parent = '.$item->parent.
			' ORDER BY `ordering` DESC'.
			' LIMIT 1';
	$db->setQuery( $query );
	$item->ordering = (int) $db->loadResult();
	$item->ordering++;

	$item->type = 'component';

	$query = 'SELECT `id`' .
			' FROM `#__components`' .
			' WHERE `link` <> \'\'' .
			' AND `parent` = 0' .
			' AND `option` = \''.$template->urlparams->option.'\''.
			' LIMIT 1';
	$db->setQuery( $query );
	$item->componentid = $db->loadResult();

	$item->link = 'index.php?';
	$urlparams = array();
	foreach( $template->urlparams as $key => $val ) {
		$val = getVar( $val );
		if ( strlen( $val ) ) {
			$urlparams[] = $key.'='.$val;
		}
	}
	$item->link .= implode( '&', $urlparams );

	$menuparams = array();
	foreach( $template->menuparams as $key => $val ) {
		$val = getVar( $val );
		if ( strlen( $val ) ) {
			$menuparams[] = $key.'='.$val;
		}
	}
	$item->params .= implode( "\n", $menuparams );

	if ( !$item->check() ) {
		echo "<script> window.parent.addtomenu_setMessage( '".$item->getError( true )."', 0 ); </script>\n";
		return false;
	}

	if (!$item->store())
	{
		echo "<script> window.parent.addtomenu_setMessage( '".$item->getError(true)."', 0 ); </script>\n";
		return false;
	}

	echo "<script> window.parent.addtomenu_setMessage( '".JText::_( 'Menu item saved' )."', 1 ); </script>\n";
}

function renderHTML( &$template )
{
	if ( isset( $template->dbselect->table ) ) {
		if ( !isset( $template->dbselect->alias ) ) {
			$template->dbselect->alias = $template->dbselect->name;
		}

		$where = array();
		foreach( $template->dbselect->where as $key => $val ) {
			$val = getVar( $val );
			$where[] = '`'.$key.'` = \''.$val.'\'';
		}

		$db =& JFactory::getDBO();
		$query = 'SELECT '.
			'`'.$template->dbselect->name.'` as name, '.
			'`'.$template->dbselect->alias.'` as alias'.
			' FROM '.$template->dbselect->table.
			' WHERE '.implode( ' AND ', $where ).
			' LIMIT 1';
		$db->setQuery( $query );
		$item = $db->loadObject();
	} else {
		$item = new stdClass();
		$item->name = JText::_( $template->dbselect->name );
		if ( !isset( $template->dbselect->alias ) ) {
			$item->alias = $item->name;
		} else {
			$item->alias = $template->dbselect->alias;
		}
	}
	$item->alias = filterAlias( $item->alias );

	$width = '100%';
	$elements = array();
	$elements[] = el(
		'Title',
		'<input class="inputbox" type="text" name="name" style=width:'.$width.';" maxlength="255" value="'.$item->name.'" />'
	);
	$elements[] = el(
		'Alias',
		'<input class="inputbox" type="text" name="alias" style=width:'.$width.';" maxlength="255" value="'.$item->alias.'" />'
	);
	$elements[] = el(
		'Published',
		'<input type="radio" name="published" value="0"  />
		<label for="published0">'.JText::_( 'No' ).'</label>
		<input type="radio" name="published" value="1" checked="checked"  />
		<label for="published1">'.JText::_( 'Yes' ).'</label>'
	);
	$elements[] = el(
		'Parent Item',
		getMenuItems( 'menuitem', $width )
	);
	if ( isset( $template->extras ) && is_object( $template->extras ) && isset( $template->extras->extra ) ) {
		if ( !is_array( $template->extras->extra ) ) {
			$template->extras->extra = array( $template->extras->extra );
		}
		$extra_elements = array();
		foreach( $template->extras->extra as $element ) {
			if ( $element->type == 'toggler' ) {
				if ( isset( $element->param ) ) {
					if ( !isset( $element->value ) ) {
						$element->value = '';
					}
					$set_groups = explode( '|', $element->param );
					$set_values = explode( '|', $element->value );
					$ids = array();
					foreach ( $set_groups as $i => $group ) {
						$count = $i;
						if ( $count >= count( $set_values ) ) {
							$count = 0;
						}
						$values = explode( ',', $set_values[$count] );
						foreach ( $values as $val ) {
							$ids[] = $group.'.'.$val;
						}
					}
					$el = '</table><div id="'.rand( 1000000, 9999999 ).'___'.implode( '___', $ids ).'" class="nntoggler nntoggler_horizontal" style="visibility: hidden;"><table width="100%" class="paramlist admintable" cellspacing="1">';
				} else {
					$el = '</table></div><table width="100%" class="paramlist admintable" cellspacing="1">';
				}
				$extra_elements[] = el(
					'',
					$el
				);
				continue;
			}
			if ( !isset( $element->name ) || !isset( $element->type ) ) {
				continue;
			}
			if ( $element->type == 'title' ) {
				$extra_elements[] = el(
					'@spacer',
					JText::_( $element->name )
				);
				continue;
			}

			if ( !isset( $element->param ) ) {
				continue;
			}

			if ( $element->name == '' ) {
				$element->name = $element->param;
			}
			if ( $element->param == '' ) {
				$element->param = strtolower( $element->name );
			}

			if ( !isset( $element->value ) ) {
				$element->value = '';
			}
			if ( !isset( $element->values ) ) {
				$element->values = new stdClass();
				$element->values->value = $element->value;
			}
			if ( !isset( $element->default ) ) {
				$element->default = '';
			}

			$style = '';
			if ( isset( $element->style ) ) {
				$style = $element->style;
			}
			if ( $element->type == 'radio' || $element->type == 'select' ) {
				$options = array();
				if ( !is_array( $element->values->value ) ) {
					$element->values->value = array( $element->values->value );
				}
				foreach( $element->values->value as $val ) {
					$options[]	= JHTML::_( 'select.option', $val->value, JText::_( $val->name ), 'value', 'text' );
				}
			}
			switch( $element->type ) {
				case 'select':
					$el = JHTML::_( 'select.genericlist', $options, 'params['.$element->param.']', 'class="inputbox" style="'.$style.'"', 'value', 'text', $element->default, $element->param );
					break;
				case 'radio':
					$el = JHTML::_( 'select.radiolist', $options, 'params['.$element->param.']', 'class="inputbox" style="'.$style.'"', 'value', 'text', $element->default );
					// add breaks between each radio element
					$el = preg_replace( '#(</label>)(\s*<input )#i', '\1<br />\2', $el );
					break;
				case 'textarea':
					$el = '<textarea style="width:'.$width.';height:100px;'.$style.'" name="params['.$element->param.']">'.$element->values->value.'</textarea>';
					break;
				case 'hidden':
					$el = '<input type="hidden" style="'.$style.'" name="params['.$element->param.']" value="'.$element->values->value.'" />';
					break;
				case 'text':
				default:
					$el = '<input type="text" name="params['.$element->param.']" style="width:'.$width.';'.$style.'" value="'.$element->values->value.'" />';
					break;
			}
			$extra_elements[] = el(
				$element->name,
				$el
			);
		}
		if ( !empty( $extra_elements ) ) {
			$elements[] = el(
				'@spacer',
				'<strong>'.JText::_( 'ATM_EXTRA_OPTIONS' ).'</strong>'
			);
			$elements = array_merge( $elements, $extra_elements );
		}
	}

	outputHTML( $template, $elements );
}

function el( $name, $element )
{
	$el = new stdClass();
	$el->name = $name;
	$el->element = $element;
	return $el;
}

function getMenuItems( $name, $width = '100%' )
{
	$db =& JFactory::getDBO();

	// load the list of menu types
	$query = 'SELECT menutype, title'
			.' FROM #__menu_types'
			.' ORDER BY id'
			;
	$db->setQuery( $query );
	$menuTypes = $db->loadObjectList();

	// load the list of menu items
	$query = 'SELECT id, parent, name, menutype, type, published'
			.' FROM #__menu'
			.' WHERE published != "-2"'
			.' ORDER BY menutype, parent, ordering'
			;
	$db->setQuery($query);
	$menuItems = $db->loadObjectList();

	// establish the hierarchy of the menu
	// TODO: use node model
	$children = array();

	if ($menuItems)
	{
		// first pass - collect children
		foreach ($menuItems as $v)
		{
			$pt 	= $v->parent;
			$list 	= @$children[$pt] ? $children[$pt] : array();
			array_push( $list, $v );
			$children[$pt] = $list;
		}
	}

	// second pass - get an indent list of the items
	$list = JHTML::_('menu.treerecurse', 0, '', array(), $children, 9999, 0, 0 );

	// assemble into menutype groups
	$groupedList = array();
	foreach ( $list as $k => $v ) {
		$groupedList[$v->menutype][] =& $list[$k];
	}

	// assemble menu items to the array
	$options = array();

	foreach ($menuTypes as $count => $type)
	{
		if ( $count ) {
			$options[]	= JHTML::_('select.option',  '-', '&nbsp;', 'value', 'text', true);
		} else {
			$selected = $type->menutype.'::0';
		}

		$options[]	= JHTML::_( 'select.option', $type->menutype.'::0', '[ '.$type->title.' ]' );

		if (isset( $groupedList[$type->menutype] ))
		{
			$n = count( $groupedList[$type->menutype] );
			for ($i = 0; $i < $n; $i++)
			{
				$item =& $groupedList[$type->menutype][$i];

				//If menutype is changed but item is not saved yet, use the new type in the list
				if ( JRequest::getString('option', '', 'get') == 'com_menus' ) {
					$currentItemArray = JRequest::getVar('cid', array(0), '', 'array');
					$currentItemId = (int) $currentItemArray['0'];
					$currentItemType = JRequest::getString('type', $item->type, 'get');
					if ( $currentItemId == $item->id && $currentItemType != $item->type) {
						$item->type = $currentItemType;
					}
				}
				if ( $item->published == 0 ) {
					$item->treename .= ' ('.JText::_( 'Unpublished' ).')';
				}
				$options[] = JHTML::_( 'select.option', $type->menutype.'::'.$item->id, '&nbsp;&nbsp;&nbsp;'.$item->treename );
			}
		}
	}

	$attribs = 'class="inputbox" style=width:'.$width.';"';
	$attribs .= ' size="'.( ( count( $options) > 10 ) ? 10 : count( $options) ).'"';

	return JHTML::_( 'select.genericlist',  $options, $name, $attribs, 'value', 'text', $selected );
}

function getVar( $var ) {
	if ( $var['0'] == '$' ) {
		$var = getVal( substr( $var, 1 ) );
	}
	return $var;
}

function getVal( $val ) {
	$vars = JRequest::getVar( 'vars' );
	$extra = JRequest::getVar( 'params' );

	if( isset( $extra[$val] ) ) {
		$value = $extra[$val];
	} else if( isset( $vars[$val] ) ) {
		$value = $vars[$val];
	} else {
		$value = JRequest::getVar( $val );
	}

	if ( is_array( $value ) ) {
		$value = $value['0'];
	}

	return $value;
}

function filterAlias( $alias ) {
	$alias = JFilterOutput::stringURLSafe( $alias );
	if( trim( str_replace( '-', '', $alias ) ) == '' ) {
		$datenow =& JFactory::getDate();
		$alias = $datenow->toFormat( "%Y-%m-%d-%H-%M-%S" );
	}
	return $alias;
}

function outputHTML( &$template, &$elements )
{
	JHTML::_( 'behavior.tooltip' );

	require_once JPATH_PLUGINS.DS.'system'.DS.'nonumberelements'.DS.'helpers'.DS.'functions.php';
	$functions =& NNFunctions::getFunctions();
	$mt_version = $functions->getJSVersion();

	$document =& JFactory::getDocument();
	$document->addStyleSheet( JURI::root( true ).'/administrator/modules/mod_addtomenu/addtomenu/css/popup.css' );
	$document->addScript( JURI::root(true).'/plugins/system/nonumberelements/js/script'.$mt_version.'.js' );
	$document->addScript( JURI::root(true).'/plugins/system/nonumberelements/elements/toggler'.$mt_version.'.js' );

	$uri =& JURI::getInstance();
?>
	<form action="<?php echo $uri->toString(); ?>" method="post" name="adminForm" id="adminForm">
		<input type="hidden" name="insert" value="1" />
		<fieldset>
			<div style="float: left">
				<h1><?php echo JText::_( 'ADD_TO_MENU' ); ?></h1>
			</div>
			<div style="float: right">
				<div class="button2-left"><div class="blank hasicon apply">
					<a rel="" onclick="document.getElementById('adminForm').submit();" href="javascript://" title="<?php echo JText::_('Add') ?>"><?php echo JText::_('Add') ?></a>
				</div></div>
				<div class="button2-left"><div class="blank hasicon cancel">
					<a rel="" onclick="window.parent.document.getElementById('sbox-window').close();" href="javascript://" title="<?php echo JText::_('Cancel') ?>"><?php echo JText::_('Cancel') ?></a>
				</div></div>
			</div>
			<div style="clear: both;"></div>
			<?php echo JText::_( $template->name ); ?>
		</fieldset>

		<table width="100%" class="paramlist admintable" cellspacing="1">
			<tbody>
				<?php
					foreach ( $elements as $element ) {
						if ( !$element->name ) {
					?>
						<?php echo $element->element; ?>
					<?php } else if ( $element->name == '@spacer' ) {
					?>
						<tr>
							<td colspan="2"><?php echo $element->element; ?></td>
						</tr>
					<?php } else { ?>
						<tr>
							<td class="paramlist_key"><?php echo JText::_( $element->name ); ?></td>
							<td><?php echo $element->element; ?></td>
						</tr>
					<?php }
					}
				?>
			</tbody>
		</table>

		<fieldset>
			<div style="float: right">
				<div class="button2-left"><div class="blank hasicon apply">
					<a rel="" onclick="document.getElementById('adminForm').submit();" href="javascript://" title="<?php echo JText::_('Add') ?>"><?php echo JText::_('Add') ?></a>
				</div></div>
				<div class="button2-left"><div class="blank hasicon cancel">
					<a rel="" onclick="window.parent.document.getElementById('sbox-window').close();" href="javascript://" title="<?php echo JText::_('Cancel') ?>"><?php echo JText::_('Cancel') ?></a>
				</div></div>
			</div>
		</fieldset>
	</form>
<?php
}