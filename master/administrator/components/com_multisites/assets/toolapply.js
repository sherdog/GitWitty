/**
 * @file       toolapply.js
 * @version    1.2.0
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2009 Edwin2Win sprlu - all right reserved.
 * @license    This program is free software; you can redistribute it and/or
 *             modify it under the terms of the GNU General Public License
 *             as published by the Free Software Foundation; either version 2
 *             of the License, or (at your option) any later version.
 *             This program is distributed in the hope that it will be useful,
 *             but WITHOUT ANY WARRANTY; without even the implied warranty of
 *             MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *             GNU General Public License for more details.
 *             You should have received a copy of the GNU General Public License
 *             along with this program; if not, write to the Free Software
 *             Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *             A full text version of the GNU GPL version 2 can be found in the LICENSE.php file.
 */


/**
 * JMS / Tools / Apply
 */
var ToolApply = {
   execute: function()
   {
      var elt;
      var result;
      var i;
      var j;
      // For each sites
      for ( i=0; ; i++)
      {
         try {
            elt = $('siteid_'+i);
            if ( elt == null) {
               break;
            }
            
            // Retreive the comment present in siteid
				var siteParams = new Array();

            var comment='';
            for (z=0; z<elt.childNodes.length; z++) {
               se = elt.childNodes[z];
               switch (se.nodeName) {
                  case '#comment': comment += se.nodeValue; break;
               }
            }

				// Parse the comment
				if (comment != '') {
					var bits = comment.split(';');
					for (z=0; z<bits.length; z++) {
						var pcs = bits[z].trim().split('=');
						if (pcs.length == 2) siteParams['_'+pcs[0].trim()] = pcs[1].trim();
					}
				}
				
				// Now searching the actions associated to the siteid number (i)
				var actions = new Array();
            for ( j=0; ; j++)
            {
               try {
                  elt = $('action_'+i+'_'+j);
                  if ( elt == null) {
                     break;
                  }
                  result = $('result_'+i+'_'+j);
                  if ( elt == null) {
                     break;
                  }
            		result.addClass('toolApply_processing');
                  
                  // Retreive the comment with the action to perform
      				actions[j] = new siteAction( result);
                  comment='';
                  for (z=0; z<elt.childNodes.length; z++) {
                     se = elt.childNodes[z];
                     switch (se.nodeName) {
                        case '#comment': comment += se.nodeValue; break;
                     }
                  }
      
      				// Parse the comment
      				if (comment != '') {
      					var bits = comment.split(';');
      					for (z=0; z<bits.length; z++) {
      						var pcs = bits[z].trim().split('=');
      						if (pcs.length == 2) actions[j].setAction( pcs[0].trim(), pcs[1].trim());
      					}
      				}
               }
               catch( e2) {
                  break;
               }
            } // Next action


				// Execute the action
				var rc;
				rc = this.doActions( i, siteParams, actions);
         }
         catch( e) {
            break;
         }
      } // Next site
   },

   checkReplies: function( ajax, siteNbr, actions)
   {
      if(ajax.readyState  == 4)
      {
         if(ajax.status  == 200) {
      		if ( ajax.responseText == '[OK]') {
               for ( var i=0; i<actions.length; i++) {
                  actions[i].addClass('toolApply_success');
               }
      		}
         	else {
               for ( var i=0; i<actions.length; i++) {
                  actions[i].addClass('toolApply_fail');
               }

               try {
                  var err    = $('err_'+siteNbr);
                  var errmsg = $('errmsg_'+siteNbr);
                  if ( err != null && errmsg != null) {
                     var reg=new RegExp("[|]+", "g");
                     var errors=ajax.responseText.split(reg);
                     if ( errors.length > 0) {
                        var msg = '';
                        var begin = 0;
                        if ( errors[0] == '[NOK]') begin = 1;
                        for (var i=begin; i<errors.length; i++) {
                           msg += '<li>' + errors[i] + '</li>' +"\n";
                        }
                        errmsg.innerHTML = '<ul>' + msg + '</ul>';
                        err.style.display = '';
                     }
                  }
               }
               catch( ee) {}
         	}
         }
         else {
            for ( var i=0; i<actions.length; i++) {
               actions[i].addClass('toolApply_fail');
            }
         }
      }
   },
   
   doActions: function( siteNbr, siteParams, actions)
   {
      var ajax;

      try {  ajax = new ActiveXObject('Msxml2.XMLHTTP');   }
      catch (e)
      {
        try {   ajax = new ActiveXObject('Microsoft.XMLHTTP');    }
        catch (e2)
        {
          try {  ajax = new XMLHttpRequest();     }
          catch (e3) {  ajax = false;   }
        }
      }
      
   
      // Asynchronous processing
      var me = this
      ajax.onreadystatechange  = function() {
         me.checkReplies( ajax, siteNbr, actions);
      };
      
      var param = "";
      var c;
      var value;
      for ( var key in siteParams) {
         c = key.charAt(0);
         if ( c == "\_") {
            value = siteParams[key];
            param += "&" + key.substring(1) + "="+value;
         }
      }
      
      param += "&nbActions="+actions.length;
      for ( var i=0; i<actions.length; i++) {
         param += actions[i].toParams( i);
      }
   
      var async = false;
      ajax.open( "POST", "index.php?option=com_multisites"
                                +"&task=ajaxToolsApply"
                      , async);
      ajax.setRequestHeader( 'Content-Type', 'application/x-www-form-urlencoded; charset=UTF-8' ); 
      ajax.send( g_curtoken+param);
      if ( !async) {
         // Synchronous processing
         this.checkReplies( ajax, siteNbr, actions);
      }

      return true;
   } // end doActions
}; // End class ToolApply


var siteAction = new Class({
	initialize: function(result) {
		this.result = result;
		this.action = new Array();
	},
	
	setAction: function(key, value) {
		this.action['_'+key]=  value;
	},
	
	toParams: function( indice) {
      var param = "";
      var c;
      var value;
	   for ( var key in this.action) {
         c = key.charAt(0);
         if ( c == "\_") {
   	      if ( this.action[key].length > 0) {
               value = this.action[key];
      	      param += "&"+key.substring(1)+"["+indice+"]=" + value;
   	      }
   	   }
	   }
		return param;;
	},
	
	addClass: function( aClassName) {
	   this.result.addClass( aClassName);
	}
});


window.addEvent('domready', function(){
     ToolApply.execute();
});
