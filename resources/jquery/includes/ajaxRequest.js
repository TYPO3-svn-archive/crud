var ajaxIdle = false;

$(document).ready(function(){
	observeLinks();
	
});

// ########################################################
function ajaxLoad(ajaxLink, ajaxParams) {
	// zielcontainer
	var ajaxTarget = "#" + ajaxParams.substr(ajaxParams.indexOf("ajaxTarget")+11);
	if (ajaxTarget.indexOf("&") > 0)
		ajaxTarget = ajaxTarget.substr(0, ajaxTarget.indexOf("&"));
	$(ajaxTarget).fadeTo("fast", 0.2);
	ajaxIdle = true;
//	$("body").unbind("click");

	$(ajaxTarget).before('<img id="loadAjaxGif" src="typo3conf/ext/crud/resources/jquery/images/ajaxload.gif" alt="Loading content…" /> ');
	jQuery.ajax({
		data: ajaxParams,
		url: ajaxLink,
		timeout: 20000,
		error: function() {
			 //fixme: fehlermeldung
		},
		success: function(r) {
			$(ajaxTarget).replaceWith(r);
			$(ajaxTarget).fadeIn("fast");
			$("#loadAjaxGif").remove();
		//	$("body").bind("click")
			ajaxIdle = false;
			observeLinks();
		}
	});
}
	
function observeLinks() {
	// verweise mit ajax verarbeiten###########################
	var ajaxLink;

	$("option[onclick]").attr("onclick", "AJAX"); // onclick inaktiv
			
	// submit-formulare----------------------------------------
	$("input[type='submit']").click(function() {
		if (!ajaxIdle) {
			var ajaxParams = "";
			// die action
			var form = $(this).parent("form");
			ajaxLink = form.attr("action");
			// parameter drin?
			if (ajaxLink.indexOf("?") > 0) {
				ajaxParams = ajaxLink.substr(ajaxLink.indexOf("?") + 1) + "&";
				ajaxLink = ajaxLink.substr(0, ajaxLink.indexOf("?"));
			}
			// parameter
			$("input[name]", form).each(function() {
				ajaxParams += this.name + '=' + escape(this.value) + "&";
			});
			if (ajaxParams.indexOf("ajaxTarget") == -1)
				return true;
			ajaxLoad(ajaxLink, ajaxParams + "ajax=1");
		}
		return false;
	});
	
	// onclick-formulare---------------------------------------
	$("option[onclick]").click(function() {
		if (!ajaxIdle) {
			var ajaxParams = "";
			// parameterwert
			var paramval = $(this).attr("value");
			// parametername
			var sel = $(this).parent("select");
			var paramname = sel.attr("name");
			// die action
			var form = sel.parent("form");
			ajaxLink = form.attr("action");
			// parameter drin?
			if (ajaxLink.indexOf("?") > 0) {
				ajaxParams = ajaxLink.substr(ajaxLink.indexOf("?") + 1) + "&";
				ajaxLink = ajaxLink.substr(0, ajaxLink.indexOf("?"));
			}
			// andere inputs
			$("input[name]", form).each(function() {
				ajaxParams += this.name + '=' + escape(this.value) + "&";
			});
			if (ajaxParams.indexOf("ajaxTarget") == -1)
				form.submit();
			ajaxLoad(ajaxLink, ajaxParams + "ajax=1&" + paramname + "=" + paramval);
		}
		return false;
	});
	
	// <a href>------------------------------------------------
	$("a").click(function() {
		if(!ajaxIdle) {
			var ajaxParams = "";
			// der verweis
			ajaxLink = $(this).attr("href");
			// parameter drin?
			if (ajaxLink.indexOf("?") > 0) {
				ajaxParams = ajaxLink.substr(ajaxLink.indexOf("?") + 1);
				ajaxLink = ajaxLink.substr(0, ajaxLink.indexOf("?"));
				if (ajaxParams.indexOf("ajaxTarget") == -1)
					return true;
				ajaxParams += "&ajax=1";
				ajaxLoad(ajaxLink, ajaxParams);
				return false;
			}
		}
		return false;
	});
}