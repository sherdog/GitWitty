/**
* Get the layout parameters
*/

   function updateLayoutParams( layout_name, control_name, field_name)
   {
      var ajax;
      var elt;
      var fieldID = control_name + field_name;
      elt = document.getElementById( fieldID);

		elt.style.display = '';
		var cid = '';
		try {
		   cid = elt.getAttribute("cid");
		}
		catch( c1) {}

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

      ajax.onreadystatechange  = function()
      {
         if(ajax.readyState  == 4)
         {
            var showFolders = true;
            if(ajax.status  == 200) {
               var pos;
               // alert( ajax.responseText);
               if ( ajax.responseText.length <= 2) {
            		elt.innerHTML = '';
               }
               else {
                  pos = ajax.responseText.indexOf( '<!-- multisites_reply_ajaxGetLayoutParams -->');
                  // To be Joomla 1.5 & 1.6 compatible, check that this is an answer contain the reply to the query.
                  if ( pos>=0 && pos< 10) {
               		elt.innerHTML = ajax.responseText;
                  }
                  else if (pos<0) {
                     var nok = ajax.responseText.substring( 0, 5);
                     if ( nok == '[NOK]') {
                  		elt.innerHTML = '';
                     }
                     else {
                  		elt.innerHTML = 'Unexpected answer';
                     }
                  }
               }
            }
            else {
         		elt.innerHTML = 'Connection with server failed';
            }
         }
      };

      ajax.open( "GET", "index.php?option=com_multisites&task=ajaxGetLayoutParams"
                      + "&layout="+layout_name
                      + "&control_name="+control_name
                      + "&name="+field_name
                      + "&cid[]="+cid
                      ,  true);
      ajax.send(null);
   }
