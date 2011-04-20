<?php
/**
 * @version		$Id: tinymce.php 10709 2008-08-21 09:58:52Z eddieajau $
 * @package		Joomla
 * @copyright	Copyright (C) 2005 - 2008 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */

// Do not allow direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

jimport( 'joomla.plugin.plugin' );

/**
 * TinyMCE WYSIWYG Editor Plugin
 *
 * @package Editors
 * @since 1.5
 */
class plgEditorIdoeditor extends JPlugin{
	
	/**
	 * Constructor
	 *
	 * For php4 compatability we must not use the __constructor as a constructor for plugins
	 * because func_get_args ( void ) returns a copy of all passed arguments NOT references.
	 * This causes problems with cross-referencing necessary for the observer design pattern.
	 *
	 * @param 	object $subject The object to observe
	 * @param 	array  $config  An array that holds the plugin configuration
	 * @since 1.5
	 */
	function plgEditorIdoEditor(& $subject, $config)
	{
		parent::__construct($subject, $config);
	}

	/**
	 * Method to handle the onInit event.
	 *  - Initializes the TinyMCE WYSIWYG Editor
	 *
	 * @access public
	 * @return string JavaScript Initialization string
	 * @since 1.5
	 */
	function onInit()
	{
		global $mainframe;
		$db			=& JFactory::getDBO();
		$language	=& JFactory::getLanguage();

		$theme = "advanced";


		$format_date = '%Y-%m-%d';
		$format_time = '%H:%M:%S' ;

		$langPrefix = $this->_getLangPrefix();
		
		$load = "\t<script type=\"text/javascript\" src=\"".JURI::root()."plugins/editors/idoeditor/editor.js\"></script>\n";
		$load .= "\t<script type=\"text/javascript\" src=\"".JURI::root()."plugins/editors/idoeditor/tiny_mce.js\"></script>\n";
		$appl = &JFactory::getApplication();
		$application_name = $appl->getName();
		$image_ext = trim($this->params->get("image_ext"));
		$video_ext = trim($this->params->get("video_ext"));
		$audio_ext = trim($this->params->get("audio_ext"));
		$file_ext  = trim($this->params->get("file_ext"));
		$use_rokbox = $this->params->get("use_rokbox");
		$use_noindex = $this->params->get("use_noindex");
	$return = $load.'
<script type="text/javascript">
tinyMCE.init ({
	mode:"textareas", 
	dialog_type:"modal", 
	editor_selector : "mce_editable",
	theme:"advanced", 
	skin:"default", 
	theme_advanced_buttons1:"bold,italic,underline,strikethrough,|,justifyleft,justifyright,justifycenter,justifyfull,floatimage,|,bullist,numlist,|,link,unlink,image,video,audio,file,emotions,readmore", 
	language:"'.$langPrefix.'", 
	theme_advanced_toolbar_location:"top", 
	theme_advanced_toolbar_align:"left", 
	theme_advanced_resizing:"1", 
	theme_advanced_resize_horizontal:"", 
	relative_urls:"", 
	remove_script_host:"", 
	convert_urls:"", 
	apply_source_formatting:"", 
	remove_linebreaks: true, 
	paste_convert_middot_lists: true, 
	paste_remove_spans: true, 
	paste_remove_styles: true, 
	paste_auto_cleanup_on_paste : true,
	paste_use_dialog:false,
	upload_image_ext : "'.$image_ext.'",
	upload_audio_ext : "'.$audio_ext.'",
	upload_video_ext : "'.$video_ext.'",
	upload_file_ext : "'.$file_ext.'",
	application_name : "'.$application_name.'",
	use_rokbox : '.($use_rokbox==0?"false":"true").',
	use_noindex : '.($use_noindex==0?"false":"true").',
	pattern4checkname : /^[\w\-]+$/,
	entities:"38,amp,60,lt,62,gt", 
	accessibility_focus:"1", 
	tab_focus:":prev,:next", 
	content_css:"", 
	plugins:"safari,inlinepopups,emotions,media,floatimage,paste,readmore",
    extended_valid_elements : "",

	cleanup : true,
	cleanup_on_startup : false,
	verify_html: false
});
</script>
';
	return $return;
}

	/**
	 * TinyMCE WYSIWYG Editor - get the editor content
	 *
	 * @param string 	The name of the editor
	 */
	function onGetContent( $editor ) {
		return "tinyMCE.activeEditor.getContent();";
	}

	/**
	 * TinyMCE WYSIWYG Editor - set the editor content
	 *
	 * @param string 	The name of the editor
	 */
	function onSetContent( $editor, $html ) {
		return "tinyMCE.setContent(".$html.");";
	}

	/**
	 * TinyMCE WYSIWYG Editor - copy editor content to form field
	 *
	 * @param string 	The name of the editor
	 */
	function onSave( $editor ) {
		return "tinyMCE.triggerSave();";
	}

	/**
	 * TinyMCE WYSIWYG Editor - display the editor
	 *
	 * @param string The name of the editor area
	 * @param string The content of the field
	 * @param string The width of the editor area
	 * @param string The height of the editor area
	 * @param int The number of columns for the editor area
	 * @param int The number of rows for the editor area
	 * @param mixed Can be boolean or array.
	 */
	function onDisplay( $name, $content, $width, $height, $col, $row, $buttons = true)
	{
		// Only add "px" to width and height if they are not given as a percentage
		if (is_numeric( $width )) {
			$width .= 'px';
		}
		if (is_numeric( $height )) {
			$height .= 'px';
		}
		@include JPATH_PLUGINS.DS."editors".DS."idoeditor".DS."langs".DS.$this->_getLangPrefix().".php";
		$buttons = $this->_displayButtons($name, $buttons);
		$editor  = "
		<div align=right>
			<a onclick=\"switchEditors.go('$name', 'html'); return false;\" id=\"edButtonHTML\" href=\"#\">HTML</a>
			<a onclick=\"switchEditors.go('$name', 'tinymce'); return false;\" class=\"active\" id=\"edButtonPreview\" href=\"#\">{$LANG_VISUAL_MODE}</a>
		</div>
		<textarea id=\"$name\" name=\"$name\" cols=\"$col\" rows=\"$row\" style=\"width:{$width}; height:{$height};\" class=\"mce_editable\">$content</textarea>\n" . $buttons;

		return $editor;
	}
	function onGetInsertMethod($name)
	{
		$doc = & JFactory::getDocument();

		$js= "function jInsertEditorText( text, editor ) {
			tinyMCE.execInstanceCommand(editor, 'mceInsertContent',false,text);
		}";
		$doc->addScriptDeclaration($js);

		return true;
	}

	function _displayButtons($name, $buttons)
	{
		// Load modal popup behavior
		JHTML::_('behavior.modal', 'a.modal-button');

		$args['name'] = $name;
		$args['event'] = 'onGetInsertMethod';

		$return = '';
		$results[] = $this->update($args);
		foreach ($results as $result) {
			if (is_string($result) && trim($result)) {
				$return .= $result;
			}
		}

		if(!empty($buttons))
		{
			$results = $this->_subject->getButtons($name, $buttons);

			/*
			 * This will allow plugins to attach buttons or change the behavior on the fly using AJAX
			 */
			$return .= "\n<div id=\"editor-xtd-buttons\">\n";
			foreach ($results as $button)
			{
				/*
				 * Results should be an object
				 */
				if ( $button->get('name') )
				{
					$modal		= ($button->get('modal')) ? 'class="modal-button"' : null;
					$href		= ($button->get('link')) ? 'href="'.JURI::base().$button->get('link').'"' : null;
					$onclick	= ($button->get('onclick')) ? 'onclick="'.$button->get('onclick').'"' : null;
					$return .= "<div class=\"button2-left\"><div class=\"".$button->get('name')."\"><a ".$modal." title=\"".$button->get('text')."\" ".$href." ".$onclick." rel=\"".$button->get('options')."\">".$button->get('text')."</a></div></div>\n";
				}
			}
			$return .= "</div>\n";
		}

		return $return;
	}
	
	function _getLangPrefix(){
		$language	=& JFactory::getLanguage();
		$langMode = $this->params->def( 'lang_mode', 0 );
		$langPrefix	= $this->params->def( 'lang_code', 'ru' );
		if ($langMode) {
			$langPrefix = substr( $language->getTag(), 0, strpos( $language->getTag(), '-' ) );
		}
		return $langPrefix;		
	}
	
}