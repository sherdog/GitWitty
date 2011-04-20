function lz_livebox(_name,_template,_width,_height,_mleft,_mtop,_mright,_mbottom,_position,_speed,_slide)
{
	this.lz_livebox_slide_finished = false;
	this.lz_livebox_template = _template;
	this.lz_livebox_name = _name;
	this.lz_livebox_move = lz_livebox_move_box;
	this.lz_livebox_show = lz_livebox_show_box;
	this.lz_livebox_close = lz_livebox_close_box;
	this.lz_livebox_chat = lz_livebox_direct_chat;
	this.lz_livebox_get_left = lz_livebox_center_get_left;
	this.lz_livebox_get_right = lz_livebox_center_get_right;
	this.lz_livebox_get_top = lz_livebox_center_get_top;
	this.lz_livebox_get_bottom = lz_livebox_center_get_bottom;
	
	this.lzibst_width = _width;
	this.lzibst_height = _height;
	this.lzibst_margin = new Array(_mleft,_mtop,_mright,_mbottom);
	this.lzibst_position = _position;
	this.lzibst_slide_speed = 13;
	this.lzibst_slide_disabled = !_slide;
	
	if(_speed == 2)
		this.lzibst_slide_step = 3;
	else if(_speed == 1)
		this.lzibst_slide_step = 8;
	else
		this.lzibst_slide_step = 13;
	
	function lz_livebox_direct_chat(_intid,_groupid)
	{
		var user_header = '<!--user_header-->';
		var user_email = '<!--user_email-->';
		var user_company = '<!--user_company-->';
		var area_code = '<!--area_code-->';
		var question = '<!--user_question-->';
		var custom = '<!--custom_params-->';
		var user_name ='';
		
		if(document.getElementById('lz_invitation_name') != null)
			user_name = document.getElementById('lz_invitation_name').value;

		var params = "";
		if(user_header.length > 0)
			params += '&eh='+user_header;
		if(user_email.length > 0)
			params += '&ee='+user_email;
		if(user_company.length > 0)
			params += '&ec='+user_company;
		if(area_code.length > 0)
			params += '&code='+area_code;
		if(question.length > 0)
			params += '&eq='+question;
		if(custom.length > 0)
			params += custom;

		if(user_name != '')
			void(window.open('<!--server--><!--file_chat-->?intid='+_intid+'&en='+lz_global_base64_url_encode(user_name)+'&intgroup='+_groupid+params,'LiveZilla','width=<!--width-->,height=<!--height-->,left=0,top=0,resizable=yes,menubar=no,location=no,status=yes,slidebars=no'));
		else
			void(window.open('<!--server--><!--file_chat-->?intid='+_intid+'&intgroup='+_groupid+params,'LiveZilla','width=<!--width-->,height=<!--height-->,left=0,top=0,resizable=yes,menubar=no,location=no,status=yes,slidebars=no'));
	}
	
	function lz_livebox_close_box(uid)
	{
		if(!this.lz_livebox_slide_finished)
			return;
		
		document.body.removeChild(this.MainDiv);
		lz_request_window = null;
	}
	
	function lz_livebox_show_box()
	{
		this.MainDiv = document.createElement('DIV');
		this.MainDiv.id = this.lz_livebox_name;
		this.MainDiv.style.position= (lz_global_is_xhtml() || !<!--is_ie-->) ? 'fixed' : 'absolute';
		this.MainDiv.style.height = this.lzibst_height+'px';
		this.MainDiv.style.width = this.lzibst_width+'px';
		this.MainDiv.style.zIndex = 1001;
		
		if(this.lzibst_position == 20 || this.lzibst_position == 21 || this.lzibst_position == 22)
			this.MainDiv.style.left = -(this.lzibst_width+100)+'px';
		else
			this.MainDiv.style.top = -(this.lzibst_height+100)+'px';
		
		this.MainDiv.innerHTML = this.lz_livebox_template;
		document.body.appendChild(this.MainDiv);
		if(document.getElementById('lz_invitation_name') != null)
			document.getElementById('lz_invitation_name').value = lz_global_base64_url_decode("<!--user_name-->");
		window.setTimeout("window['"+ this.lz_livebox_name +"'].lz_livebox_move()",1);
	}

	function lz_livebox_move_box()
	{
		this.MainDiv.style.bottom = this.lz_livebox_get_bottom();
		this.MainDiv.style.right = this.lz_livebox_get_right();

		if(this.lzibst_slide_disabled)
		{
			this.MainDiv.style.left = this.lz_livebox_get_left();
			this.MainDiv.style.top = this.lz_livebox_get_top();
			this.MainDiv.style.right = this.lz_livebox_get_right();
			this.MainDiv.style.bottom = this.lz_livebox_get_bottom();
			this.lz_livebox_slide_finished = true;
		}
		else
		{
			if(this.lzibst_position == 20 || this.lzibst_position == 21 || this.lzibst_position == 22)
			{
				var current = parseInt(this.MainDiv.style.left.replace("px","").replace("pt",""));
				current+=this.lzibst_slide_step;

				this.MainDiv.style.left = current+'px';
				this.MainDiv.style.top = this.lz_livebox_get_top();

				var leftdist = parseInt(this.lz_livebox_get_left().replace("px",""));
				if(current < (leftdist-this.lzibst_slide_step))
					window.setTimeout("window['"+ this.lz_livebox_name +"'].lz_livebox_move()",this.lzibst_slide_speed);
				else
				{
					this.MainDiv.style.left = leftdist+'px';
					this.lz_livebox_slide_finished = true;
				}
			}
			else
			{
				var current = parseInt(this.MainDiv.style.top.replace("px","").replace("pt",""));
				current+=this.lzibst_slide_step;
					
				this.MainDiv.style.top = current+'px';
				this.MainDiv.style.left = this.lz_livebox_get_left();
				
				var topdist = parseInt(this.lz_livebox_get_top().replace("px",""));
				if(current < (topdist-this.lzibst_slide_step))
					window.setTimeout("window['"+ this.lz_livebox_name +"'].lz_livebox_move()",this.lzibst_slide_speed);
				else
				{
					this.MainDiv.style.top = topdist+'px';
					this.lz_livebox_slide_finished = true;
				}
			}
		}

		if(this.lz_livebox_slide_finished && document.body.onresize == null)
		{
			if(this.MainDiv.style.position == 'absolute')
				window.onslide = lz_livebox_center_box;
			window.onresize = lz_livebox_center_box;
		}
	}
	
	function lz_livebox_center_get_left()
	{
		var left = 0;
		if(this.lzibst_position == 01 || this.lzibst_position == 11 || this.lzibst_position == 21)
		{
			left  = parseInt((<!--is_ie-->) ? (document.documentElement.offsetWidth * 50 / 100) : (window.innerWidth * 50 / 100));
			if(this.MainDiv.style.position == 'absolute')
				left += lz_global_get_page_offset_x()
			left -= parseInt(this.lzibst_width / 2);
			
			if(this.lzibst_margin[0] != 0)
				left += this.lzibst_margin[0];
			if(this.lzibst_margin[2] != 0)
				left -= this.lzibst_margin[2];
				
			return left+'px';
		}
		else if(this.lzibst_position == 00 || this.lzibst_position == 10 || this.lzibst_position == 20)
		{
			if(this.lzibst_margin[0] != 0)
				left += this.lzibst_margin[0];
			if(this.lzibst_margin[2] != 0)
				left -= this.lzibst_margin[2];
			
			left+=lz_global_get_page_offset_x();
				
			return left+'px';
		}
		else if(this.lzibst_position == 22)
		{
			left = window.innerWidth-this.lzibst_width;
			
			if(this.lzibst_margin[0] != 0)
				left += this.lzibst_margin[0];
			if(this.lzibst_margin[2] != 0)
				left -= this.lzibst_margin[2];
				
			left+=lz_global_get_page_offset_x();
				
			return left+'px';
		}
		else
			return '';
	}
	
	function lz_livebox_center_get_right()
	{
		var right = 0;
		if(this.lzibst_position == 02 || this.lzibst_position == 12 || this.lzibst_position == 22)
		{
			if(this.lzibst_margin[0] != 0)
				right -= this.lzibst_margin[0];
			if(this.lzibst_margin[2] != 0)
				right += this.lzibst_margin[2];
				
			right-=lz_global_get_page_offset_x();
				
			return right+'px';
		}
		else
			return '';
	}
	
	function lz_livebox_center_get_top()
	{
		var top = 0;
		if(this.lzibst_position == 10 || this.lzibst_position == 11 || this.lzibst_position == 12)
		{
			top  = parseInt((<!--is_ie-->) ? (document.documentElement.offsetHeight * 50 / 100) : (window.innerHeight * 50 / 100));
			if(this.MainDiv.style.position == 'absolute')
				top += lz_global_get_page_offset_y()
			top -= parseInt(this.lzibst_height / 2);
			
			if(this.lzibst_margin[1] != 0)
				top += this.lzibst_margin[1];
			if(this.lzibst_margin[3] != 0)
				top -= this.lzibst_margin[3];
				
			return parseInt(top)+'px';
		}
		else if(this.lzibst_position == 00 || this.lzibst_position == 01 || this.lzibst_position == 02)
		{
			if(this.lzibst_margin[1] != 0)
				top += this.lzibst_margin[1];
			if(this.lzibst_margin[3] != 0)
				top -= this.lzibst_margin[3];
				
			top+=lz_global_get_page_offset_y();
				
			return parseInt(top)+'px';
		}
		else
			return '';
	}
	
	function lz_livebox_center_get_bottom()
	{
		var bottom = 0;
		if(this.lzibst_position == 20 || this.lzibst_position == 21 || this.lzibst_position == 22)
		{
			if(this.lzibst_margin[1] != 0)
				bottom -= this.lzibst_margin[1];
			if(this.lzibst_margin[3] != 0)
				bottom += this.lzibst_margin[3];
			bottom-=lz_global_get_page_offset_y();
				
			return bottom+'px';
		}
		else
			return '';
	}
}

function lz_livebox_center_box()
{
	if(document.getElementById("lz_request_window") != null)
	{
		document.getElementById("lz_request_window").style.top = window['lz_request_window'].lz_livebox_get_top();
		document.getElementById("lz_request_window").style.left = window['lz_request_window'].lz_livebox_get_left();
		document.getElementById("lz_request_window").style.right = window['lz_request_window'].lz_livebox_get_right();
		document.getElementById("lz_request_window").style.bottom = window['lz_request_window'].lz_livebox_get_bottom();
	}

	if(document.getElementById("lz_alert_window") != null)
	{
		document.getElementById("lz_alert_window").style.top = window['lz_alert_window'].lz_livebox_get_top();
		document.getElementById("lz_alert_window").style.left = window['lz_alert_window'].lz_livebox_get_left();
		document.getElementById("lz_alert_window").style.right = window['lz_alert_window'].lz_livebox_get_right();
		document.getElementById("lz_alert_window").style.bottom = window['lz_alert_window'].lz_livebox_get_bottom();
	}
}