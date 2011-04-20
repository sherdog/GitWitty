<!--//--><![CDATA[//><!--
startList = function() {

	if (document.all&&document.getElementById) {   
			if (document.getElementById("menulist_root")) {
			navRoot = document.getElementById("menulist_root");
					for (i=0; i<navRoot.childNodes.length; i++) {
						effect(navRoot);
					}
			}
			if (document.getElementById("menulist_1")) {
			navRoot = document.getElementById("menulist_1");
					for (i=0; i<navRoot.childNodes.length; i++) {
						effect(navRoot);
					}
			}
			if (document.getElementById("menulist_2")) {
			navRoot = document.getElementById("menulist_2");
					for (i=0; i<navRoot.childNodes.length; i++) {
						effect(navRoot);
					}
			}
			if (document.getElementById("menulist_3")) {
			navRoot = document.getElementById("menulist_3");
					for (i=0; i<navRoot.childNodes.length; i++) {
						effect(navRoot);
					}
			}
			if (document.getElementById("menulist_4")) {
			navRoot = document.getElementById("menulist_4");
					for (i=0; i<navRoot.childNodes.length; i++) {
						effect(navRoot);
					}
			}
			if (document.getElementById("menulist_5")) {
			navRoot = document.getElementById("menulist_5");
					for (i=0; i<navRoot.childNodes.length; i++) {
						effect(navRoot);
					}
			}
			if (document.getElementById("menulist_6")) {
			navRoot = document.getElementById("menulist_6");
					for (i=0; i<navRoot.childNodes.length; i++) {
						effect(navRoot);
					}
			}
			if (document.getElementById("menulist_7")) {
			navRoot = document.getElementById("menulist_7");
					for (i=0; i<navRoot.childNodes.length; i++) {
						effect(navRoot);
					}
			}
			if (document.getElementById("menulist_8")) {
			navRoot = document.getElementById("menulist_8");
					for (i=0; i<navRoot.childNodes.length; i++) {
						effect(navRoot);
					}
			}

	}   
}

function effect(elementId) {
node = elementId.childNodes[i];
		if (node.nodeName=="LI") {
				node.onmouseover=function() {
					this.className="over";
				}
				node.onmouseout=function() {
					this.className=this.className.replace("over", "");
				}
		}
}

window.onload=startList;
//--><!]]>