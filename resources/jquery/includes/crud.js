/*
**  history for ajax/javascript history
**      0.4 easier to configure cache control iframe POST handler
**      0.3 history events now setup in queue to ensure all entries reside in the history stack
**      0.2 no more FORM GET submission, straight location.href instead + hold time for iframe load
**      0.1 hidden frame + not bookmarkable + stores data for state change + allows reinstating data on forw/back hit
**  authored by Jim Palmer - released under MIT license
**  collage of ideas from Taku Sano, Mikage Sawatari, david bloom and Klaus Hartl
*/
(function($) {

    /*
    ** pre-initialize the history functionality - once you include this plugin this will be instantiated as a singleton onLoad
    ** IMPORTANT - replace the 'cache.php' with the appropriate cache handler URL string
    **             this is what the iframe submits its POSTS to and is required for this plugin to work
    */
  //  $(document).ready( function () {  } );

    // core history plugin functionality - handles singleton instantiation and individual calls
    $.history = function ( store ) {


        // (initialize) create the hidden iframe if not on the root window.document.body
        if ( $(".__historyFrame").length == 0 ) {

            // set the history cursor to (-1) - this will be populated with current unix timestamp or 0 for the first screen
            $.history.cursor = $.history.intervalId = 0;
            // initialize the stack of history stored entries
            $.history.stack = {};
            // initialize the stack of loading hold flags
            $.history._loading = {};
            // initialize the queue for loading history fragments in sequence
            $.history._queue = [];

            // append to the root window.document.body without the src - uses class for toggleClass debugging - display:none doesn't work
            $("body").after('<iframe class="__historyFrame" src="'+store+'" style="border:0px; width:0px; height:0px; visibility:hidden;" />');
            $("body").after('<div class="__historyDebug" style="border:0px; width:0px; height:0px; visibility:hidden;" />');

            // set the src (safari doesnt load the src if set in the append above)  + set the onLoad event for the iframe
            $('.__historyFrame').load(function () {

                    // parse out the current cursor from the location/URL
                    var cursor = $(this).contents().attr( $.browser.msie ? 'URL' : 'location' ).toString().split('#')[1];
                    if ( cursor ) {
                        // remove the cursor from the load queue
                        var qPos = $.inArray( cursor, $.history._queue );
                        if ( qPos > -1 )
                            $.history._queue.splice( qPos, 1 );
                        // flag that the iframe is done loading the new fragment id
                        $.history._loading[ cursor ] = false;
                    }

                    // setup interval function to check for changes in "history" via iframe hash and call appropriate callback function to handle it
                    $.history.intervalId = $.history.intervalId || window.setInterval(function () {
                            // if any cursors in queue - load first cursor (FIFO)
                            if ( $.history._queue.length > 0 && !$.history._loading[ $.history._queue[0] ] ) {
                                // flag this queued cursor as loading so this interval will not load more than once
                                $.history._loading[ $.history._queue[0] ] = true;
                     //         window.console.info($.history._queue[0]);
                                // move the history cursor in the hidden iframe to the newest fragment identifier
                                $('.__historyFrame').contents()[0].location.href =
                                    $('.__historyFrame').contents().attr( $.browser.msie ? 'URL' : 'location' ).toString().replace(/[\?|#]{1}(.*)$/gi, '') +
                                    '?' + $.history._queue[0] + '#' + $.history._queue[0];
                            } else if ( $.history._queue.length == 0 ) {
                                // fetch current cursor from the iframe document.URL or document.location depending on browser support
                                var cursor = $(".__historyFrame").contents().attr( $.browser.msie ? 'URL' : 'location' ).toString().split('#')[1];
                           //     window.console.info(cursor);
                                // display debugging information if block id exists
                                $('#__historyDebug').html('"' + $.history.cursor + '" vs "' + cursor + '" - ' + (new Date()).toString());
                                // if cursors are different (forw/back hit) then reinstate data only when iframe is done loading
                                if ( parseFloat($.history.cursor) >= 0 && parseFloat($.history.cursor) != ( parseFloat(cursor) || 0 ) ) {
                                    // set the history cursor to the current cursor
                                    $.history.cursor = parseFloat(cursor) || 0;
                                    // reinstate the current cursor data through the callback
                                    if ( typeof($.history.callback) == 'function' )
                                        $.history.callback( $.history.stack[ cursor ], cursor );
                                }
                            }
                        }, 150);

                });

        /* handle calls to store entries in the history after the history plugin has been initialized */
        } else {

            // set the current unix timestamp for our history
            $.history.cursor = (new Date()).getTime().toString();
      //		window.console.info($.history.cursor);
            // add this cursor fragment id into the queue to be loaded by the checking function interval
            $.history._queue.push( $.history.cursor );
            // insert copy into the stack with current cursor
            $.history.stack[ $.history.cursor ] = $.extend( true, {}, store );

        }
           
    };

})(jQuery);




// globals --------------------------------------------------------
var ajaxIdle = false;
var tinyMCEpresent;
savedContainer=new Array();
var baseUrl;
var thickboxActive=false;
var firstHistoryEntry;

$(document).ready(function() {
	prepareSide();
	$.history( baseUrl+'typo3conf/ext/crud/ajaxHistoryHeader.php' );
	firstHistoryEntry=$('body').html();
	$("body").append('<div class="thickbox" style="display:none;"><div class="thickbox-toolbar"><a id="hideThickbox" onclick="hideThickbox();return false;" href="">close</a></div><div id="thickbox" class="thickbox-content"></div></div>');
	$("body").append('<div class="overlay" style="display:none;background-color:#000;position:fixed;z-index:9;top:0px;left:0px;height:100%;width:100%;filter:alpha(opacity=75);-moz-opacity:0.75;opacity:0.75;"></div>');
});

function prepareSide(){ 
	observeLinks(); 
	prepareHelp();
	prepareAutoComplete(); 
	prepareHistoryChecks();
	prepareThickbox();
	if(typeof enableTabs == 'function')
		enableTabs();
	if(typeof getBaseurl == 'function')
		baseUrl=getBaseurl();
}
// helpcontext divs------------------------------------------------------
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
// history-callback------------------------------------------------------
$.history.callback = function(reinstate,cursor){
	if (typeof(reinstate) != 'undefined'){	
		if(typeof(reinstate.containerId)!='undefined'){
			var $container=$('#'+reinstate.containerId);	
			if($container.attr('class')=="thickbox-content"){
				if(!thickboxActive)
					showThickbox();
			}
			else if(thickboxActive)
				hideThickbox();
		}
		if(typeof(reinstate.$elem)!='undefined'){
			ajaxedForm(reinstate.$elem, $container,false);
		}else if(typeof(reinstate.link)!='undefined'){
			ajaxLoad(reinstate.link, reinstate.pars, "", false, $container);
		}
	}else {
		$('body').html(firstHistoryEntry);
		prepareSide();
	}
};
// thickbox --------------------------------------------------------------
function prepareThickbox(){
	document.onkeydown=function(e){
		if(thickboxActive){
			if (e == null) keycode = event.keyCode;
			else keycode = e.which;
        	if(keycode == 27) hideThickbox();
		}
	};
	$(".overlay").click(function(){
		if(thickboxActive) hideThickbox();
	});	
}
function showThickbox(){
	$(".overlay").css("display","block");
	$(".thickbox").css("display","block");
	thickboxActive=true;
}
function hideThickbox(){
	$(".thickbox").css("display","none");
	thickboxActive=false;	
	$(".thickbox-content").empty();
	$(".overlay").css("display","none");
}
// checkbox 4 history compare -----------------------------------------------
function prepareHistoryChecks(){
	val1=$(".showhistory:checked").attr('value');
	val2=$(".comparewith:checked").attr('value');
	$(".showhistory[value='"+val2+"']").attr("disabled","disabled");
	$(".comparewith[value='"+val1+"']").attr("disabled","disabled");
	$(".showhistory").change(function(){
		$(".comparewith").each(function(){
			$(this).attr("disabled","");
		});
		$(".comparewith[value='"+$(this).attr('value')+"']").attr("disabled","disabled");
	});
	$(".comparewith").change(function(){
		$(".showhistory").each(function(){
			$(this).attr("disabled","");
		});
		$(".showhistory[value='"+$(this).attr('value')+"']").attr("disabled","disabled");
	});
}
// autocomplete ------------------------------------------------------
function serialize2Array($form) {
	serializedArray = $form.serializeArray();
	result = new Array();
	$.each(serializedArray, function(i, field){
		//alert(field.name+"="+field.value);
        result[field.name]=field.value;
      });
     return result;
    
}
function selectItem(li,$elem) {
	$elem.attr('name', $('.ac_info1',li).html());
	$elem.attr('value', $('.ac_info2',li).html());
	$elem.parents('form').submit();
}
function formatItem(row) {
	if(row[2])
		return '<div class="hidden ac_info1">'+row[1]+'</div><div class="hidden ac_info2">'+row[3]+'</div><div>'+row[0]+' ('+row[2]+')</div>';
	else return '<div class="headline">'+row[1]+'</div>';
}
function prepareAutoComplete(){
	if(typeof $.autocomplete == 'function') {
		$form=$(".autocomplete").parents("form");
	//	$(":submit",$form).attr("disabled","disabled");
	//	alert(serialize2Array($form));
		$(".autocomplete").each( function(){
			$(this).autocomplete(document.URL,{
				delay:1,
				//direction:"over",
				extraParams:serialize2Array($form),
				minChars:4,
				matchSubset:4,
				matchContains:1,
				cacheLength:4,
				autoFill:false,
				onItemSelect:selectItem,
				formatItem:formatItem
			});
		});
	}
}
// ajax -----------------------------------------------------------------
function ajax4onClick(elem){
	if(ajaxedForm($(elem).parents('form'),"",true)==true) $(elem).parents('form').submit();
}
function ajaxedForm($form,$container,saveHist){
	if(!ajaxIdle){
		var ajaxLink = $form.attr("action");
		if(tinyMCEpresent)
			tinyMCE.triggerSave(true,true);
		var ajaxParams = $form.formSerialize();	
		if(ajaxLoadForm(ajaxLink, ajaxParams , $form,saveHist,$container)==false) return true; 
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
	if(ajaxTarget=="thickbox"){
		ajaxTarget="thickbox-content";
		if(!thickboxActive)
			showThickbox();
	}
	else if(thickboxActive)
		hideThickbox();
	var $container=$elem.parents("div."+ajaxTarget);
	if($container.length < 1)
		$container=$("div."+ajaxTarget);
	if($container.length < 1)
		return false;
	if($container.length > 1)
		$container=$container[0]; 
	if(string.indexOf("saveContainer")!=-1)
		savedContainer[$container.attr('id')]=$container.html();
	if(typeof(savedContainer[$container.attr('id')])=='string' && string.indexOf("restoreContainer")!=-1){
		$.history( {'containerId':$container.attr('id'),'link':ajaxLink,'pars':ajaxParams} );
		$container.html(savedContainer[$container.attr('id')]);
		ajaxIdle=false;
		prepareSide();
		return true;
	}
	if(string.indexOf("http:")==0)
		baseUrl="";
	return $container;
}
function ajaxLoadForm(ajaxLink, ajaxParams, $elem, saveHist, $container){
	if($container.length < 1)
		$container=getAjaxContainer(ajaxLink, ajaxParams, $elem);
	if($container==false||$container==true)return $container;
	$elem.prepend('<input type="hidden" name="ajax" value="1"/>');
	$container.prepend('<img id="loadAjaxGif" src="'+baseUrl+'typo3conf/ext/crud/resources/jquery/images/ajaxload.gif" alt="Loading content" />');
	$elem.ajaxSubmit({
		url: baseUrl+ajaxLink,
		target: $container,
		success: function (r){
			if(saveHist)
			$.history( {'containerId':$container.attr('id'),'$elem':$elem} );	
			ajaxIdle=false;
			prepareSide();
		}
	});
}
function ajaxLoad(ajaxLink, ajaxParams, $elem, saveHist, $container){
	if($container.length < 1)
		$container=getAjaxContainer(ajaxLink, "", $elem);
	if($container==false||$container==true)return $container;
	
	$container.prepend('<img id="loadAjaxGif" src="'+baseUrl+'typo3conf/ext/crud/resources/jquery/images/ajaxload.gif" alt="Loading content" />');
	$.ajax({
		type: "GET",
		data: ajaxParams,
		url: baseUrl+ajaxLink,
		timeout: 200000,
		success: function(r) {
			$container.html(r);
			if(saveHist)
				$.history( {'containerId':$container.attr('id'),'link':ajaxLink,'pars':ajaxParams} );
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
		return ajaxedForm($(this),"",true);
	});
	$("a").click(function() {
		if(!ajaxIdle){
			// der verweis
			ajaxLink = $(this).attr("href");
			if(ajaxLoad(ajaxLink, "ajax=1",$(this),true,"")==false) return true; 	
		}
		return false;
	});
}
