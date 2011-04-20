//_jms2win_begin v1.1.0
		   // If in fact the folder is a link to a folder
		   if ( is_link( $folder)) {
		      // Delete the link (not the folder content).
   			jimport('joomla.filesystem.file');
   			if (JFile::delete( $folder) !== true) {
   				// JFile::delete throws an error
   				return false;
   			}
		   }
		   else if (JFolder::delete($folder) !== true) {
//_jms2win_end
