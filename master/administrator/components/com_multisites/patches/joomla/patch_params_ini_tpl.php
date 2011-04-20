				<?php if ( defined( 'MULTISITES_ID')) {
				         $templatefile = DS.'templates'.DS.$template.DS.'params_' .MULTISITES_ID .'.ini';
               		jimport('joomla.filesystem.file');
				         if ( !JFile::exists( $templatefile)) {
				            $templatefile = DS.'templates'.DS.$template.DS.'params.ini';
				         }
				      }
				      else {
				         $templatefile = DS.'templates'.DS.$template.DS.'params.ini';
				      }
