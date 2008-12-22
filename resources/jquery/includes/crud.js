var ajaxIdle = false;
$(document).ready(function(){
	prepareSide();
});
function prepareSide(){ 
	observeLinks(); 
	prepareHelp(); 
	enableTabs();
//	enableTinyMCE();
}
function enableTabs(){};
function enableTinyMCE(){};
function ajax4onClick(elem){
	if(ajaxedForm($(elem).parents('form'))==true) $(elem).parents('form').submit();
}
function ajaxedForm($form){
	if(!ajaxIdle){
		var ajaxLink = $form.attr("action");
		var ajaxParams = $form.formSerialize();	
		if(ajaxLoadForm(ajaxLink, ajaxParams , $form)==false) return true; 
	}
	return false;
}
function getAjaxContainer(ajaxLink,ajaxParams,$elem){
	var string = ajaxLink +"&"+ ajaxParams;
	if(string.indexOf("ajaxTarget") == -1)
		return false;
	var ajaxTarget = string.substr(string.indexOf("ajaxTarget")+11);
	if(ajaxTarget.indexOf("&")>=0)
		ajaxTarget = ajaxTarget.substr(0, ajaxTarget.indexOf("&"));
	if(ajaxTarget.length < 1)
		return false;
	ajaxIdle = true;
	var $container=$elem.parents("."+ajaxTarget);
	if(!$container)
		$container=$("."+ajaxTarget);
	if(!$container)
		return false;
	return $container;
}
function ajaxLoadForm(ajaxLink, ajaxParams, $elem){
	var $container=getAjaxContainer(ajaxLink, ajaxParams, $elem);
	if($container==false)return false;
	$elem.prepend('<input type="hidden" name="ajax" value="1"/>');
	$container.prepend('<img id="loadAjaxGif" src="typo3conf/ext/crud/resources/jquery/images/ajaxload.gif" alt="Loading content" />');
	$elem.ajaxSubmit({
		url: ajaxLink,
		target: $container,
		success: function (r){
			ajaxIdle=false;
			prepareSide();
		}
	});
}
function ajaxLoad(ajaxLink, ajaxParams, $elem){
	var $container=getAjaxContainer(ajaxLink, "", $elem);
	if($container==false)return false;
	$container.prepend('<img id="loadAjaxGif" src="typo3conf/ext/crud/resources/jquery/images/ajaxload.gif" alt="Loading content" />');
	$.ajax({
		type: "GET",
		data: ajaxParams,
		url: ajaxLink,
		timeout: 200000,
		success: function(r) {
			$container.html(r);
			ajaxIdle = false;
			prepareSide();
		}
	});
}
function observeLinks(){
	var ajaxLink;
	$(':submit, :image').click(function() {
		$(this).after('<input type="hidden" name="'+this.name+'" value="'+this.value+'"/>');
	});
	$("form").submit(function(){
		return ajaxedForm($(this));
	});
	$("a").click(function() {
		if(!ajaxIdle){
			// der verweis
			ajaxLink = $(this).attr("href");
			if(ajaxLoad(ajaxLink, "ajax=1",$(this))==false) return true; 	
		}
		return false;
	});
}
function prepareHelp() {
	$('.csh').css('display', 'none');
	$('.hasHelp').mouseover(function() {
		if(!ajaxIdle){
			var i = $(this).attr('refId');
			$('#'+i+'').css('display','block');
		}
	});
	$('.hasHelp').mouseout(function() {
		if(!ajaxIdle)
			$('.csh').css('display', 'none');
	});
}