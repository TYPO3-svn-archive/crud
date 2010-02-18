
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
		if ( $('.__historyFrame').length == 0 ) {

			// set the history cursor to (-1) - this will be populated with current unix timestamp or 0 for the first screen
			$.history.cursor = $.history.intervalId = 0;
			// initialize the stack of history stored entries
			$.history.stack = {};
			// initialize the stack of loading hold flags
			$.history._loading = {};
			// initialize the queue for loading history fragments in sequence
			$.history._queue = [];

			// append to the root window.document.body without the src - uses class for toggleClass debugging - display:none doesn't work
			$('body').after('<iframe class="__historyFrame" src="'+store+'" style="border:0px; width:0px; height:0px; visibility:hidden;" />');
			$('body').after('<div class="__historyDebug" style="border:0px; width:0px; height:0px; visibility:hidden;" />');

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
                     	// window.console.info($.history._queue[0]);
						// move the history cursor in the hidden iframe to the newest fragment identifier
						$('.__historyFrame').contents()[0].location.href =
						$('.__historyFrame').contents().attr( $.browser.msie ? 'URL' : 'location' ).toString().replace(/[\?|#]{1}(.*)$/gi, '') + '?' + $.history._queue[0] + '#' + $.history._queue[0];
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
      //	window.console.info($.history.cursor);
            // add this cursor fragment id into the queue to be loaded by the checking function interval
			$.history._queue.push( $.history.cursor );
			// insert copy into the stack with current cursor
			$.history.stack[ $.history.cursor ] = $.extend( true, {}, store );
		}
	};
})(jQuery);

// -==========
// = globals 
// -==========
var ajaxIdle;
var tinyMCEpresent;
var savedContainer;
var baseUrl;
var thickboxActive;
var firstHistoryEntry;
var ac_minChars = 4;
var googleMapCoords;

ajaxIdle = false;
savedContainer = new Array();
thickboxActive = false;

$(document).ready(function() {
	prepareSide();
//	$.history( baseUrl+'typo3conf/ext/crud/ajaxHistoryHeader.php' );
	firstHistoryEntry = $('body').html();
	$('body').append('<div class="thickbox" style="display:none;"><div class="thickbox-toolbar"><a id="hideThickbox" onclick="hideThickbox();return false;" href="">close</a></div><div id="thickbox" class="thickbox-content"></div></div>');
	$('body').append('<div class="overlay" style="display:none;background-color:#000;position:fixed;z-index:9;top:0;left:0;height:100%;width:100%;filter:alpha(opacity=75);-moz-opacity:0.75;opacity:0.75;"></div>');
});

function prepareSide() {
	observeLinks(); 
//	prepareHelp();
	prepareAutoComplete(); 
	prepareHistoryChecks();
	prepareMultiSelects();
	prepareThickbox();
	prepareGoogleMap();
	if (typeof enableTabs == 'function') {
		enableTabs();
	}
	if (typeof getBaseurl == 'function') {
		baseUrl = getBaseurl();
	}
	prepareHideables();
	loadPicPos();
	prepareDatePicker();
}

//-==============
//= DatePicker 
//-==============
function prepareDatePicker() {
	$('.inputDate').each(function() {
		activateDatePicker($(this));
	});
}
function activateDatePicker($elem) {
	var datepickerConfig = new Object({
		eventName: 'focus',
		format: $elem.attr('format'),
		date: $elem.val(),
		current: $elem.val(),
		starts: 1,
		position: 'right',
		onBeforeShow: function(){
			if ( $elem.val().length > 0 ) {
				$elem.DatePickerSetDate($elem.val(), true);
			}
		},
		onChange: function(formated, dates) {
			if(!isNaN(dates.valueOf())) {
				$elem.val(formated);
				$elem.DatePickerHide();
			}
		}
	});
	
	if($elem.attr('lan') == 'de') {
		datepickerConfig.locale = new Object({
			days: ['Sonntag', 'Montag', 'Dienstag', 'Mittwoch', 'Donnerstag', 'Freitag', 'Samstag', 'Sonntag'],
			daysShort: ['Son', 'Mon', 'Die', 'Mit', 'Don', 'Fre', 'Sam', 'Son'],
			daysMin: ['So', 'Mo', 'Di', 'Mi', 'Do', 'Fr', 'Sa', 'So'],
			months: ['Januar', 'Februar', 'März', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober', 'November', 'Dezember'],
			monthsShort: ['Jan', 'Feb', 'Mrz', 'Apr', 'Mai', 'Jun', 'Jul', 'Aug', 'Sep', 'Okt', 'Nov', 'Dez'],
			weekMin: 'KW'
		});
	}
/*	if($elem.attr('lower') != undefined) {
		datepickerConfig.onRender = function(date) {
			return {
				disabled: (date.valueOf() < $elem.attr('lower') || date.valueOf() > $elem.attr('upper'))
			}
		};
	}*/
	
	$elem.DatePicker(datepickerConfig);
}

//-===========
//= tinyMCE 
//-===========
function removeHTMLComments(element_id, html, body) {
	
	if(typeof html == 'string') {
		var clean = html.replace(/<!--.*?-->/g,'');
		clean = clean.replace(/&lt;!--.*?--&gt;/g,'');
		//alert(clean);
		return clean;
	} else return html; 
}

// -===========
// = hideable 
// -===========
function prepareHideables() {
	$('.hideable').each(function(){
		$(this).prev().html($(this).prev().html() + ' (<a href="#" onclick="return showHide(this, true);">show</a>)');
		$(this).hide();
	});
	$('.hideableSimple').each(function(){
		$(this).prev().html('<a href="#" onclick="return showHide(this, false);">' + $(this).prev().html() + '</a>');
		$(this).hide();
	});
}

function showHide(elem, withTextChange) {
	$(elem).parent().next().toggle();
	if(withTextChange) {
		if ($(elem).html() == 'show') {
			$(elem).html('hide');
		} else {
			$(elem).html('show');
		}
	}
	return false;
}

// -=============
// = GoogleMaps 
// -=============
function clearCoords() {
	googleMapCoords = new Array();
}

function addCoord(coord, address, link, id) {
	var index = googleMapCoords.length;
	googleMapCoords[index] = new Object();
	googleMapCoords[index]['coord'] = coord;
	googleMapCoords[index]['address'] = address;
	if(link) {
		googleMapCoords[index]['link'] = link;
	}
	if(id) {
		googleMapCoords[index]['elemId'] = id;
	}
}


function showGoogleMap(ajaxTarget, aId) {
	var m = $('#map')[0];
	if (window.GBrowserIsCompatible()) {
		$(m).empty();
		var map = new GMap2(m);
		var coord;
		var centerx = 0.0;
		var centery = 0.0;
		var divider = 0;
		var marker = new Array();
		var i;
		var item;
		for (i = 0; i < googleMapCoords.length; i++) {
			item = googleMapCoords[i];
			coord = item['coord'].split(',');
			var point = new GLatLng(coord[0],coord[1]);
			if (point) {	
				centerx += parseFloat(coord[0]);
				centery += parseFloat(coord[1]);
				marker[divider] = new GMarker(point);
				marker[divider].content = new Object();
				marker[divider].content['address'] = item['address'];
				
				GEvent.addListener(marker[divider], 'mouseover', function() {
					this.openInfoWindowHtml(this.content['address']);
				});
				GEvent.addListener(marker[divider], 'mouseout', function() {
					this.closeInfoWindow();
				});
				
				if (item['elemId']) {
					createDomListener(item['elemId'], marker[divider]);
				}
				if (item['link']) {
					marker[divider].content['link'] = item['link'];
					GEvent.addListener(marker[divider], 'click', function() {
						if (ajaxTarget != '' && aId != '') {
							ajaxLoad( baseUrl + this.content['link'], 'ajax=1&ajaxTarget=' + ajaxTarget + '&aID=' + aId, $(m), true, ''); //+'&saveContainer=1'
						} else {
							location = baseUrl + this.content['link'];
						}
					});
				}
				map.addOverlay(marker[divider] );
				divider ++;
			}
		}
		centerx = centerx / parseFloat(divider);
		centery = centery / parseFloat(divider);
		
		var center = new GLatLng(centerx, centery);
		map.setCenter(center, 5);
		map.addControl(new GSmallMapControl());
	}
}

function createDomListener(elem, marker){
	GEvent.addDomListener( document.getElementById(elem), 'mouseover', function() {
		GEvent.trigger(marker, 'mouseover');
	});
	GEvent.addDomListener( document.getElementById(elem), 'mouseout', function() {
		GEvent.trigger(marker, 'mouseout');
	});
}

function prepareGoogleMap() {
	// Das Element für die Anzeige suchen
	var m = $('#map_canvas')[0];
	
	// Die Landkarte auf der Webseite darstellen
	if (m) {
		var clArray = $(m).attr('class').split('###');
		var coordClass = clArray[0];
		var coord = new Array();
		coord = coordClass.split(',');
		var point = new GLatLng(coord[0],coord[1]);
		var addArray = new Array();
		addArray = clArray[1].split('#');
		
		var address = addArray[3] + '<br /> ' + addArray[1] + ' ' + addArray[2] + '<br /> ' + addArray[0];
		//alert(point);
		if (point && window.GBrowserIsCompatible()) {
			$(m).empty();
			var map = new GMap2(m);
			map.setCenter(point, 13);
			map.addControl(new GSmallMapControl());
			var marker = new GMarker(point);
			GEvent.addListener(marker, 'mouseover', function() {
				marker.openInfoWindowHtml(address);
			});
			GEvent.addListener(marker, 'mouseout', function() {
				marker.closeInfoWindow();
			});
			map.addOverlay(marker);
		} else {
			$(m).remove();
		}
	}
}


// -==========================
// = optgroups - multiselect 
// -==========================
function prepareMultiSelects() {
	var $sels = new Array();
	
	$('select[multiple]').each(function() {
		var selString = '';
		if (!$(this).hasClass('multiselectPrepared')){
			var className = $(this).attr('id');
			
			$(this).mouseover(function() {
				$sels = $(this.options + '[selected]');
			});
			
			$(this).mouseout($sels.length = 0);
			
			$(this).click(function() {
				var ind = this.selectedIndex;
				var tag = $(this.options[ind]).html();
				var indexOfTag = $('div.' + $(this).attr('id')).html().indexOf(tag);			
				$sels.each(function(){
					this.selected = true;
				});
				if (indexOfTag > -1) {
					$('div.' + $(this).attr('id')+' > ul > li').each(function(){
						if ($(this).html().indexOf(tag) > -1)
							$(this).remove();
					});
					this.options[ind].selected = false;
					if($('div.' + $(this).attr('id')+' > ul > li').length == 0) {
						$('#hl_' + className).remove();
					}
					
				} else { 					
					if($('div.' + $(this).attr('id') + ' > ul > li').length == 0) {
						$(this).after('<strong id="hl_' + className + '">Auswahl:</strong>');
					}
					$('div.' + $(this).attr('id')+' > ul').append('<li>' + tag + ' <a class="deselect" href="#" onclick="return deselect(\''+$(this.options[ind]).attr('value')+'\',\''+$(this).attr('id')+'\', this)">x</a></li>');
					this.options[ind].selected = true;	
				}
				$sels.length = 0;
				$sels = $(this.options + '[selected]');
				return false;
			});
			$(this).addClass('multiselectPrepared');
			var j = 0;
			for (var i = 0; i < this.options.length; i++) {
				if (this.options[i].selected) {
					j++;
					selString += '<li>' + $(this.options[i]).html() + ' <a class="deselect" href="#" onclick="return deselect(\''+$(this.options[i]).attr('value')+'\',\''+$(this).attr('id')+'\', this)">x</a></li>';
				}
			}
			 //TODO: Localization
			$(this).after('<div class="' + className + ' multiselection"><ul>' + selString + '</ul></div>');
			if (j > 0) {
				$(this).after('<strong id="hl_' + className + '">Auswahl:</strong>');
			}
		} 
	});
} 

function deselect( optVal, idName, elem){
	$('#'+idName+' option:selected').each(function(){
		if($(this).attr('value') == optVal)
			this.selected = false;
	});
	$(elem).parents('li').remove();
	if($('#'+idName+' option:selected').length == 0) {
		$('#hl_' + idName).remove();
	}
	return false;
}

// -===================
// = helpcontext divs 
// -===================
/*function prepareHelp() {
	$('.csh').css('display', 'none');
	$('.hasHelp').mouseover(function() {
		if (!ajaxIdle) {
			var i = $(this).attr('refId');
			$('#'+i+'').css('display','block');
		}
	});
	$('.hasHelp').mouseout(function() {
		if (!ajaxIdle)
			$('.csh').css('display', 'none');
	});
}*/

// -===================
// = history-callback 
// -===================
$.history.callback = function(reinstate,cursor) {
	if (typeof(reinstate) != 'undefined') {
		if (typeof(reinstate.containerId) != 'undefined') {
			var $container = $('#'+reinstate.containerId);
			if ($container.attr('class') == 'thickbox-content') {
				if (!thickboxActive) {
					showThickbox();
				}
			} else if (thickboxActive) {
				hideThickbox();
			}
		}
		if (typeof(reinstate.$elem) != 'undefined') {
			ajaxedForm(reinstate.$elem, $container,false);
		} else if (typeof(reinstate.link) != 'undefined') {
			ajaxLoad(reinstate.link, reinstate.pars, '', false, $container);
		}
	} else {
		$('body').html(firstHistoryEntry);
		prepareSide();
	}
};

// -===========
// = thickbox 
// -===========
function prepareThickbox() {
	document.onkeydown = function(e) {
		if (thickboxActive) {
			if (e == null) {
				keycode = event.keyCode;
			} else {
				keycode = e.which;
			}
        	if (keycode == 27) {
				hideThickbox();
			}
		}
	};
	$('.overlay').click(function() {
		if (thickboxActive) {
			hideThickbox();
		}
	});	
}

function showThickbox() {
	$('.overlay').css('display','block');
	$('.thickbox').css('display','block');
	thickboxActive = true;
}

function hideThickbox() {
	$('.thickbox').css('display','none');
	thickboxActive = false;
	$('.thickbox-content').empty();
	$('.overlay').css('display','none');
}

// -=============================
// = checkbox 4 history compare 
// -=============================
function prepareHistoryChecks() {
	val1 = $('.showhistory:checked').attr('value');
	val2 = $('.comparewith:checked').attr('value');
	$('.showhistory[value="' + val2 + '"]').attr('disabled','disabled');
	$('.comparewith[value="' + val1 + '"]').attr('disabled','disabled');
	$('.showhistory').change(function() {
		$('.comparewith').each(function() {
			$(this).attr('disabled','');
		});
		$('.comparewith[value="'+$(this).attr('value')+'"]').attr('disabled','disabled');
	});
	$('.comparewith').change(function() {
		$('.showhistory').each(function() {
			$(this).attr('disabled','');
		});
		$('.showhistory[value="'+$(this).attr('value')+'"]').attr('disabled','disabled');
	});
}

// -===============
// = autocomplete 
// -===============
function serialize2Array($form) {
	serializedArray = $form.serializeArray();
	result = new Array();
	$.each(serializedArray, function(i, field) {
		//alert(field.name+"="+field.value);
		result[field.name] = field.value;
	});
	result['ajax'] = 1;
	return result;
}

function selectItem(li, $elem) {
	$elem.attr('name', $('.ac_info1', li).html());
	$elem.attr('value', $('.ac_info2', li).html());
	$elem.parents('form').submit();
}

function formatItem(row) {
	if (row[2]) {
		if (ac_showHitCount) {
			var show = row[0]+' ('+row[2]+')';
		} else {
			var show = row[0];
		}
		return '<div class="hidden ac_info1">' + row[1] + '</div><div class="hidden ac_info2">' + row[3] + '</div><div>' + show + '</div>';
	} else {
		return '<div class="headline">' + row[1] + '</div>';
	}
}

function prepareAutoComplete() {
	if (typeof $.autocomplete == 'function') {
		$form = $('.autocomplete').parents('form');
	//	$(":submit",$form).attr("disabled","disabled");
	//	alert(serialize2Array($form));
		$('.autocomplete').each( function() {
			$(this).autocomplete($form.attr('action'),{
				delay: 1,
				//direction: 'over',
				extraParams: serialize2Array($form),
				minChars: ac_minChars,
				matchSubset: 4,
				matchContains: 1,
				cacheLength: 4,
				autoFill: false,
				onItemSelect: selectItem,
				formatItem: formatItem
			});
		});
		if (!ajaxIdle) {
			// der verweis
			ajaxLink = $(this).attr('href');
			if (ajaxLoad(ajaxLink, 'ajax=1', $(this), true, '') == false) {
				return true;
			}
		}
		return false;
	}
}

// -=======
// = ajax 
// -=======
function loadPicPos(){
	if ($('#loadAjaxGif').length==0) {
		  $('body').append('<img id="loadAjaxGif" style="position:absolute;z-index:999;" src="http://yellowmed.com/typo3conf/ext/crud/resources/jquery/images/ajax-loader.gif" alt="" />');	
		  $('#loadAjaxGif').hide();
	}

	$('body').mousemove(function(e) {
		if(ajaxIdle)
			$('#loadAjaxGif').css({ top: (e.pageY + 5) + 'px', left: (e.pageX + 5) + 'px' });
	});
	$('body').mousedown(function(e) {
		//alert(e.pageXOffset);
		$('#loadAjaxGif').css({ top: (e.pageY + 5) + 'px', left: (e.pageX + 5) + 'px' });
	});
}

function ajaxlink(elem, ajaxTarget, aId, saveContainer, restoreContainer) {
	if (!ajaxIdle) {
		var params;
		params = 'ajax=1&ajaxTarget=' + ajaxTarget + '&aID=' + aId;
		if (saveContainer) {
			params += '&saveContainer=' + saveContainer;
		}
		if (restoreContainer) {
			params += '&restoreContainer=' + restoreContainer;
		}
		// der verweis
		ajaxLink = $(elem).attr('href');
		if (ajaxLoad(ajaxLink, params, $(elem), true, '') == false) {
			return true;
		}
	}
	return false;
}

function ajax4onClick(elem) {
	if (ajaxedForm($(elem).parents('form'), '', true) == true) {
		$(elem).parents('form').submit();
	}
}

function ajaxedForm($form, $container, saveHist) {
	if (!ajaxIdle) {
		var ajaxLink = $form.attr('action');
		if (tinyMCEpresent) {
			tinyMCE.triggerSave(true, true);
		}
		var ajaxParams = $form.formSerialize();	
		if (ajaxLoadForm(ajaxLink, ajaxParams , $form, saveHist, $container) == false) {
			return true;
		}
	}
	return false;
}

function getAjaxContainer(ajaxLink, ajaxParams, $elem) {
	var string = ajaxLink + '&' + ajaxParams;
	if (string.indexOf('ajaxTarget') == -1) {
		return false;
	}
	
	var ajaxTarget = string.substr(string.indexOf('ajaxTarget') + 11);
	if (ajaxTarget.charAt(0) == '=') {
		ajaxTarget = ajaxTarget.substr(1);
	}
	if (ajaxTarget.indexOf('&') >= 0) {
		ajaxTarget = ajaxTarget.substr(0, ajaxTarget.indexOf('&'));
	}
	if (ajaxTarget.length < 1) {
		return false;
	}
	if (ajaxTarget == 'thickbox') {
		ajaxTarget = 'thickbox-content';
		if (!thickboxActive) {
			showThickbox();
		}
	} else if (thickboxActive) {
		hideThickbox();
	}
	
	var $container = $elem.parents('.' + ajaxTarget);
	if ($container.length < 1) {
		$container = $('.' + ajaxTarget);
	}
	if ($container.length < 1) {
		return false;
	}
	ajaxIdle = true;	
	
	if ($container.length > 1) {
		$container = $container[0];
	} 
	if (string.indexOf('saveContainer') != -1) {
		savedContainer[$container.attr('id')] = $container.html();
	}
	if (string.indexOf("restoreContainer") != -1) {
		ajaxIdle = false;
		if (typeof(savedContainer[$container.attr('id')]) == 'string') {
	//		$.history( {'containerId':$container.attr('id'),'link':ajaxLink,'pars':ajaxParams} );
			$container.html(savedContainer[$container.attr('id')]);
			prepareSide();
			return true;
		} else {
			return false;
		}
	}
	if (string.indexOf('http:') == 0) {
		baseUrl = '';
	}
	return $container;
}

// -===============
// = ajaxLoadForm 
// -===============
function ajaxLoadForm(ajaxLink, ajaxParams, $elem, saveHist, $container) {
	var callUrl;
	if ($container.length < 1) {
		$container = getAjaxContainer(ajaxLink, ajaxParams, $elem);
	}
	if ($container == false || $container == true) {
		return $container;
	}
	$elem.prepend('<input type="hidden" name="ajax" value="1" />');
	
	$('#loadAjaxGif').show();
	
	if (ajaxLink.indexOf('http') > -1) {
		callUrl = ajaxLink;
	} else {
		callUrl = baseUrl + ajaxLink;
	}
	
	$elem.ajaxSubmit({
		url: callUrl,
		target: $container,
		success: function (r){
			if (r.substr(0, 9) == '<!DOCTYPE') {
				window.location.href = document.URL;
			} else {
				$container.html(r);
			}
			if (saveHist) {
		//		$.history( {'containerId':$container.attr('id'),'$elem':$elem} );
			}
			ajaxIdle = false;
			$('#loadAjaxGif').hide();
			prepareSide();
		}
	});
}

function ajaxLoad(ajaxLink, ajaxParams, $elem, saveHist, $container) {
	var callUrl;
	if ($container.length < 1) {
		$container = getAjaxContainer(ajaxLink + ajaxParams, '', $elem);
	}
	if ($container == false || $container == true) {
		return $container;
	}
	if (ajaxLink.indexOf('http') > -1) {
		callUrl = ajaxLink;
	} else {
		callUrl = baseUrl + ajaxLink;
	}
	
	$('#loadAjaxGif').show();
	
	$.ajax({
		type: 'GET',
		data: ajaxParams,
		url: callUrl,
		timeout: 200000,
		success: function(r) {	
			if(r.substr(0, 9) == '<!DOCTYPE') {
				window.location.href = callUrl;
			} else {
				$container.html(r);
			}
			//$container.css('cursor', 'derfault');
			if (saveHist) {
		//		$.history( {'containerId':$container.attr('id'),'link':ajaxLink,'pars':ajaxParams} );
			}
			$('#loadAjaxGif').hide();
			ajaxIdle = false;
			prepareSide();
		},
		error:function (xhr, ajaxOptions, thrownError) {
            //alert(thrownError);
			ajaxIdle = false;
			return false;
		}
	});
}

function observeLinks(){
	var ajaxLink;
	$(':submit, :image').click(function() {
		$(this).after('<input type="hidden" name="' + this.name + '" value="' + this.value + '" />');
	});
	$('form').submit(function() {
		return ajaxedForm($(this), '', true);
	});
	
	$('a').click(function() {
		if (!ajaxIdle) {
			// der verweis
		//	ajaxLink = $(this).attr('href');
		//	if (ajaxLoad(ajaxLink, 'ajax=1', $(this), true, '') == false) {
				return true;
		//	}
		}
		return false;
	});
}