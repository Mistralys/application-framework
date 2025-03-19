// make sure the console object exists even if the browser does not define it
if(!window.console) { 
	window.console = {
		'log':function(){} 
	}; 
}

/**
 * Stores key code values for easy access.
 * 
 * Note: more or less obsolete now that mousetrap is bundled,
 * which makes it easy to attach events to keyboard strokes.
 * 
 * @package Application
 * @class
 * @static
 */
var KeyCodes = 
{
	/**
	 * @property ArrowUp
	 * @type {Number}
	 */
	'ArrowUp':38,
	
	/**
	 * @property Enter
	 * @type {Number}
	 */
	'Enter':13,
	
	/**
	 * @property ArrowDown
	 * @type {Number}
	 */
	'ArrowDown':40,
	
	/**
	 * @property ArrowLeft
	 * @type {Number}
	 */
	'ArrowLeft':37,
	
	/**
	 * @property ArrowRight
	 * @type {Number}
	 */
	'ArrowRight':39,

	/**
	 * @property Space
	 * @type {Number}
	 */
	'Space':32,
	
	/**
	 * @property Delete
	 * @type {Number}
	 */
	'Delete':46,
	
	/**
	 * @property Backspace
	 * @type {Number}
	 */
	'Backspace':8,
	
	/**
	 * @property Shift
	 * @type {Number}
	 */
	'Shift':16,
	
	/**
	 * @property Control
	 * @type {Number}
	 */
	'Control':17,
	
	/**
	 * @property Alt
	 * @type {Number}
	 */
	'Alt':18
};

/**
 * Converts special characters to HTML entities in the target string.
 * 
 * @global
 * @param {String} text
 * @returns {String}
 */
function htmlspecialchars(text)
{
	return text
		.replace(/&/g, "&amp;")
		.replace(/</g, "&lt;")
		.replace(/>/g, "&gt;")
		.replace(/"/g, "&quot;")
		.replace(/'/g, "&#039;");
}

/**
 * Strips all HTML tags from the specified string.
 * 
 * @param {String} text
 * @returns {String}
 */
function strip_tags (input, allowed) 
{
	input = String(input);
	
	// @link http://phpjs.org/functions/strip_tags
	allowed = (((allowed || "") + "").toLowerCase().match(/<[a-z][a-z0-9]*>/g) || []).join('');
	
	var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi;
	var commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;

	return input.replace(commentsAndPhpTags, '').replace(tags, function ($0, $1) {
		return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
	});
}

function sortByWeight(a, b)
{
	if(a.weight > b.weight) {
		return 1;
	}

	if(a.weight < b.weight) {
		return -1;
	}

	return 0;
}

function checkSaveComments()
{
	var minor = $('#comments_minor_changes').is(':checked');
	if(minor === true) {
		return true;
	}

	var comments = $('#save_comments_field').val().trim();
	if(comments.length < 1) {
		application.dialogMessage(t('Please enter a reason for this change into the comments field.'));
		return false;
	}

	return true;
}

/**
 * Trims all whitespace from the specified string.
 * 
 * @param {String} str
 * @param {String} [charlist] List of characters to strip
 * @returns {String}
 */
function trim(str, charlist) 
{
	if(typeof(str)=='undefined' || str==null) {
		str = '';
	}
	
	var whitespace, l = 0,
    i = 0;
	str += '';

	if (!charlist) {
		whitespace = ' \n\r\t\f\x0b\xa0\u2000\u2001\u2002\u2003\u2004\u2005\u2006\u2007\u2008\u2009\u200a\u200b\u2028\u2029\u3000';
	} else {
		charlist += '';
		whitespace = charlist.replace(/([\[\]\(\)\.\?\/\*\{\}\+\$\^\:])/g, '$1');
	}

	l = str.length;
	for (i = 0; i < l; i++) {
		if (whitespace.indexOf(str.charAt(i)) === -1) {
			str = str.substring(i);
			break;
		}
	}

	l = str.length;
	for (i = l - 1; i >= 0; i--) {
		if (whitespace.indexOf(str.charAt(i)) === -1) {
			str = str.substring(0, i + 1);
			break;
		}
	}

	return whitespace.indexOf(str.charAt(0)) === -1 ? str : '';
}

function showExportDialog(ptypeID)
{
	var el = $('#'+ptypeID+'_xmloptions');
	if(el.length < 1 ) {
		return;
	}

	var buttons = {};
	buttons[t('Get link')] = function() {
		var id = $(this).data('ptypeID');
		var localeName = $('input:radio[name=xmlexport_'+id+'_locale]:checked').val();
		var revision = $('select[name=xmlexport_'+id+'_revision]').val();
		var format = $('input:radio[name=xmlexport_'+id+'_format]:checked').val();
		var url = application.url+'/xml.php?product-type_id='+id+'&amp;locale='+localeName+'&amp;revision='+revision+'&amp;format='+format;
		var linkEL = $('#'+id+'_xmloptions_link');
		linkEL.html('<br/><div class="message_important">'+t('Your link:')+'<br/><a href="'+url+'">'+t('XML Export')+' - '+t('Revision')+' '+revision+', '+localeName+'</a></div>');
		linkEL.effect('pulsate', {'times':2}, 300);
	};

	buttons[t('Close')] = function() {
		var id = $(this).data('ptypeID');
		$('#'+id+'_xmloptions_link').html('');
		$(this).dialog('close');
	};

	if(el.data('built') !== true) {
		el.dialog(
			{
				'width':400,
				'modal':true,
				'buttons':buttons,
				'autoOpen':false,
				'title':t('Configure XML export')
			}
		);

		el.data('ptypeID', ptypeID);
		el.data('built', true);
	}

	el.dialog('open');
}

var jsIDCounter = 0;

/**
 * Increases the global javascript ID counter and returns
 * a new ID. Can be used to generate element IDs within
 * a request. 
 * 
 * @returns {Number}
 */
function nextJSID()
{
	jsIDCounter++;
	return jsIDCounter;
}

/**
 * JS equivalent of the PHP sprintf function, used to format 
 * strings and insert dynamic content into placeholders.
 * 
 * @param {String} format
 * @param {String} [insertContent]*
 * @returns {String}
 */
function sprintf () {
		// http://kevin.vanzonneveld.net
		// +	 original by: Ash Searle (http://hexmen.com/blog/)
		// + namespaced by: Michael White (http://getsprink.com)
		// +		tweaked by: Jack
		// +	 improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
		// +			input by: Paulo Freitas
		// +	 improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
		// +			input by: Brett Zamir (http://brett-zamir.me)
		// +	 improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
		// +	 improved by: Dj
		// +	 improved by: Allidylls
		// *		 example 1: sprintf("%01.2f", 123.1);
		// *		 returns 1: 123.10
		// *		 example 2: sprintf("[%10s]", 'monkey');
		// *		 returns 2: '[		monkey]'
		// *		 example 3: sprintf("[%'#10s]", 'monkey');
		// *		 returns 3: '[####monkey]'
		// *		 example 4: sprintf("%d", 123456789012345);
		// *		 returns 4: '123456789012345'
		var regex = /%%|%(\d+\$)?([-+\'#0 ]*)(\*\d+\$|\*|\d+)?(\.(\*\d+\$|\*|\d+))?([scboxXuideEfFgG])/g;
		var a = arguments,
			i = 0,
			format = a[i++];
		
		// pad()
		var pad = function (str, len, chr, leftJustify) {
			if (!chr) {
				chr = ' ';
			}
			var padding = (str.length >= len) ? '' : Array(1 + len - str.length >>> 0).join(chr);
			return leftJustify ? str + padding : padding + str;
		};

		// justify()
		var justify = function (value, prefix, leftJustify, minWidth, zeroPad, customPadChar) {
			var diff = minWidth - value.length;
			if (diff > 0) {
				if (leftJustify || !zeroPad) {
					value = pad(value, minWidth, customPadChar, leftJustify);
				} else {
					value = value.slice(0, prefix.length) + pad('', diff, '0', true) + value.slice(prefix.length);
				}
			}
			return value;
		};

		// formatBaseX()
		var formatBaseX = function (value, base, prefix, leftJustify, minWidth, precision, zeroPad) {
			// Note: casts negative numbers to positive ones
			var number = value >>> 0;
			prefix = prefix && number && {
				'2': '0b',
				'8': '0',
				'16': '0x'
			}[base] || '';
			value = prefix + pad(number.toString(base), precision || 0, '0', false);
			return justify(value, prefix, leftJustify, minWidth, zeroPad);
		};

		// formatString()
		var formatString = function (value, leftJustify, minWidth, precision, zeroPad, customPadChar) {
			if (precision != null) {
				value = value.slice(0, precision);
			}
			return justify(value, '', leftJustify, minWidth, zeroPad, customPadChar);
		};

		// doFormat()
		var doFormat = function (substring, valueIndex, flags, minWidth, _, precision, type) {
			var number;
			var prefix;
			var method;
			var textTransform;
			var value;

			if (substring == '%%') {
				return '%';
			}

			// parse flags
			var leftJustify = false,
				positivePrefix = '',
				zeroPad = false,
				prefixBaseX = false,
				customPadChar = ' ';
			var flagsl = flags.length;
			for (var j = 0; flags && j < flagsl; j++) {
				switch (flags.charAt(j)) {
				case ' ':
					positivePrefix = ' ';
					break;
				case '+':
					positivePrefix = '+';
					break;
				case '-':
					leftJustify = true;
					break;
				case "'":
					customPadChar = flags.charAt(j + 1);
					break;
				case '0':
					zeroPad = true;
					break;
				case '#':
					prefixBaseX = true;
					break;
				}
			}

			// parameters may be null, undefined, empty-string or real valued
			// we want to ignore null, undefined and empty-string values
			if (!minWidth) {
				minWidth = 0;
			} else if (minWidth == '*') {
				minWidth = +a[i++];
			} else if (minWidth.charAt(0) == '*') {
				minWidth = +a[minWidth.slice(1, -1)];
			} else {
				minWidth = +minWidth;
			}

			// Note: undocumented perl feature:
			if (minWidth < 0) {
				minWidth = -minWidth;
				leftJustify = true;
			}

			if (!isFinite(minWidth)) {
				throw new Error('sprintf: (minimum-)width must be finite');
			}

			if (!precision) {
				precision = 'fFeE'.indexOf(type) > -1 ? 6 : (type == 'd') ? 0 : undefined;
			} else if (precision == '*') {
				precision = +a[i++];
			} else if (precision.charAt(0) == '*') {
				precision = +a[precision.slice(1, -1)];
			} else {
				precision = +precision;
			}

			// grab value using valueIndex if required?
			value = valueIndex ? a[valueIndex.slice(0, -1)] : a[i++];

			switch (type) {
			case 's':
				return formatString(String(value), leftJustify, minWidth, precision, zeroPad, customPadChar);
			case 'c':
				return formatString(String.fromCharCode(+value), leftJustify, minWidth, precision, zeroPad);
			case 'b':
				return formatBaseX(value, 2, prefixBaseX, leftJustify, minWidth, precision, zeroPad);
			case 'o':
				return formatBaseX(value, 8, prefixBaseX, leftJustify, minWidth, precision, zeroPad);
			case 'x':
				return formatBaseX(value, 16, prefixBaseX, leftJustify, minWidth, precision, zeroPad);
			case 'X':
				return formatBaseX(value, 16, prefixBaseX, leftJustify, minWidth, precision, zeroPad).toUpperCase();
			case 'u':
				return formatBaseX(value, 10, prefixBaseX, leftJustify, minWidth, precision, zeroPad);
			case 'i':
			case 'd':
				number = +value || 0;
				number = Math.round(number - number % 1); // Plain Math.round doesn't just truncate
				prefix = number < 0 ? '-' : positivePrefix;
				value = prefix + pad(String(Math.abs(number)), precision, '0', false);
				return justify(value, prefix, leftJustify, minWidth, zeroPad);
			case 'e':
			case 'E':
			case 'f': // Should handle locales (as per setlocale)
			case 'F':
			case 'g':
			case 'G':
				number = +value;
				prefix = number < 0 ? '-' : positivePrefix;
				method = ['toExponential', 'toFixed', 'toPrecision']['efg'.indexOf(type.toLowerCase())];
				textTransform = ['toString', 'toUpperCase']['eEfFgG'.indexOf(type) % 2];
				value = prefix + Math.abs(number)[method](precision);
				return justify(value, prefix, leftJustify, minWidth, zeroPad)[textTransform]();
			default:
				return substring;
			}
		};

		return format.replace(regex, doFormat);
}

/**
 * Checks whether the specified needle string exists
 * in the haystack string and returns the position of
 * the first occurrence.
 * 
 * @param {String} haystack
 * @param {String} needle
 * @param {Number} [offset] Starting offset
 * @returns {Number|false}
 */
function stripos (f_haystack, f_needle, f_offset) {
		// http://kevin.vanzonneveld.net
		// +		 original by: Martijn Wieringa
		// +			revised by: Onno Marsman
		// *				 example 1: stripos('ABC', 'a');
		// *				 returns 1: 0
		var haystack = (f_haystack + '').toLowerCase();
		var needle = (f_needle + '').toLowerCase();
		var index = 0;

		if ((index = haystack.indexOf(needle, f_offset)) !== -1) {
			return index;
		}
		return false;
}

/**
 * Replaces all occurrences of the search string with the replace string
 * in the subject string, and returns the modified string.
 * 
 * @param {String} search
 * @param {String} replace
 * @param {String} subject
 * @param {Number} [count] How many occurrences to replace. Default is no limit.
 * @returns {String}
 */
function str_replace (search, replace, subject, count) {
		// http://kevin.vanzonneveld.net
		// +	 original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
		// +	 improved by: Gabriel Paderni
		// +	 improved by: Philip Peterson
		// +	 improved by: Simon Willison (http://simonwillison.net)
		// +		revised by: Jonas Raoni Soares Silva (http://www.jsfromhell.com)
		// +	 bugfixed by: Anton Ongson
		// +			input by: Onno Marsman
		// +	 improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
		// +		tweaked by: Onno Marsman
		// +			input by: Brett Zamir (http://brett-zamir.me)
		// +	 bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
		// +	 input by: Oleg Eremeev
		// +	 improved by: Brett Zamir (http://brett-zamir.me)
		// +	 bugfixed by: Oleg Eremeev
		// %					note 1: The count parameter must be passed as a string in order
		// %					note 1:	to find a global variable in which the result will be given
		// *		 example 1: str_replace(' ', '.', 'Kevin van Zonneveld');
		// *		 returns 1: 'Kevin.van.Zonneveld'
		// *		 example 2: str_replace(['{name}', 'l'], ['hello', 'm'], '{name}, lars');
		// *		 returns 2: 'hemmo, mars'
		var i = 0,
			j = 0,
			temp = '',
			repl = '',
			sl = 0,
			fl = 0,
			f = [].concat(search),
			r = [].concat(replace),
			s = subject,
			ra = Object.prototype.toString.call(r) === '[object Array]',
			sa = Object.prototype.toString.call(s) === '[object Array]';
		s = [].concat(s);
		if (count) {
			this.window[count] = 0;
		}

		for (i = 0, sl = s.length; i < sl; i++) {
			if (s[i] === '') {
				continue;
			}
			for (j = 0, fl = f.length; j < fl; j++) {
				temp = s[i] + '';
				repl = ra ? (r[j] !== undefined ? r[j] : '') : r[0];
				s[i] = (temp).split(f[j]).join(repl);
				if (count && s[i] !== temp) {
					this.window[count] += (temp.length - s[i].length) / f[j].length;
				}
			}
		}
		return sa ? s : s[0];
}

/**
 * Returns an indexed array with all values from the specified object.
 * 
 * @param {Object} input
 * @returns {Array}
 * @see http://phpjs.org/functions/array_values/
 */
function array_values(input) 
{
	var tmp_arr = [],
	key = '';
	
	if(input && typeof input === 'object' && input.change_key_case) {
		return input.values();
	}
	
	for(key in input) {
		tmp_arr[tmp_arr.length] = input[key];
	}
	
	return tmp_arr;
}

/**
 * Returns an indexed array with all values from the specified object.
 * 
 * @param {Object} input
 * @param {String} [search]
 * @param {Boolean} [strict=false]
 * @returns {Array}
 * @see http://phpjs.org/functions/array_keys/
 */
function array_keys(input, search_value, argStrict) 
{
	//	discuss at: http://phpjs.org/functions/array_keys/
	// original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	//		input by: Brett Zamir (http://brett-zamir.me)
	//		input by: P
	// bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
	// bugfixed by: Brett Zamir (http://brett-zamir.me)
	// improved by: jd
	// improved by: Brett Zamir (http://brett-zamir.me)
	//	 example 1: array_keys( {firstname: 'Kevin', surname: 'van Zonneveld'} );
	//	 returns 1: {0: 'firstname', 1: 'surname'}

	var search = typeof search_value !== 'undefined',
		tmp_arr = [],
		strict = !! argStrict,
		include = true,
		key = '';

	if (input && typeof input === 'object' && input.change_key_case) {
		// Duck-type check for our own array()-created PHPJS_Array
		return input.keys(search_value, argStrict);
	}

	for (key in input) {
		if (input.hasOwnProperty(key)) {
			include = true;
			if (search) {
				if (strict && input[key] !== search_value) {
					include = false;
				} else if (input[key] != search_value) {
					include = false;
				}
			}

			if (include) {
				tmp_arr[tmp_arr.length] = key;
			}
		}
	}

	return tmp_arr;
}

/**
 * Natural sort algorithm: Can be used in custom sort functions 
 * to compare two values.
 * 
 * @param {String} a
 * @param {String} b
 * @see https://github.com/overset/javascript-natural-sort
 * @returns {Number}
 */
function naturalSort (a, b) 
{
	// Natural Sort algorithm for Javascript - Version 0.7 - Released under MIT license
	// Author: Jim Palmer (based on chunking idea from Dave Koelle)
		var re = /(^-?[0-9]+(\.?[0-9]*)[df]?e?[0-9]?$|^0x[0-9a-f]+$|[0-9]+)/gi,
				sre = /(^[ ]*|[ ]*$)/g,
				dre = /(^([\w ]+,?[\w ]+)?[\w ]+,?[\w ]+\d+:\d+(:\d+)?[\w ]?|^\d{1,4}[\/\-]\d{1,4}[\/\-]\d{1,4}|^\w+, \w+ \d+, \d{4})/,
				hre = /^0x[0-9a-f]+$/i,
				ore = /^0/,
				i = function(s) { return naturalSort.insensitive && (''+s).toLowerCase() || ''+s; },
				// convert all to strings strip whitespace
				x = i(a).replace(sre, '') || '',
				y = i(b).replace(sre, '') || '',
				// chunk/tokenize
				xN = x.replace(re, '\0$1\0').replace(/\0$/,'').replace(/^\0/,'').split('\0'),
				yN = y.replace(re, '\0$1\0').replace(/\0$/,'').replace(/^\0/,'').split('\0'),
				// numeric, hex or date detection
				xD = parseInt(x.match(hre)) || (xN.length != 1 && x.match(dre) && Date.parse(x)),
				yD = parseInt(y.match(hre)) || xD && y.match(dre) && Date.parse(y) || null,
				oFxNcL, oFyNcL;
		// first try and sort Hex codes or Dates
		if (yD)
				if ( xD < yD ) return -1;
				else if ( xD > yD ) return 1;
		// natural sorting through split numeric strings and default strings
		for(var cLoc=0, numS=Math.max(xN.length, yN.length); cLoc < numS; cLoc++) {
				// find floats not starting with '0', string or 0 if not defined (Clint Priest)
				oFxNcL = !(xN[cLoc] || '').match(ore) && parseFloat(xN[cLoc]) || xN[cLoc] || 0;
				oFyNcL = !(yN[cLoc] || '').match(ore) && parseFloat(yN[cLoc]) || yN[cLoc] || 0;
				// handle numeric vs string comparison - number < string - (Kyle Adams)
				if (isNaN(oFxNcL) !== isNaN(oFyNcL)) { return (isNaN(oFxNcL)) ? 1 : -1; }
				// rely on string comparison if different types - i.e. '02' < 2 != '02' < '2'
				else if (typeof oFxNcL !== typeof oFyNcL) {
						oFxNcL += '';
						oFyNcL += '';
				}
				if (oFxNcL < oFyNcL) return -1;
				if (oFxNcL > oFyNcL) return 1;
		}
		return 0;
}

/**
 * ScrollTo function: scrolls to the specified DOM element.
 * Can be an existing DOM element, or a jQuery selector.
 * 
 * @param {DOMElement|string} target
 * @param {Object} [options]
 * @param {Number} [options.delay] The time the scrolling should take, in microsenconds. Defaults to the application.scrollToDelay setting.
 * @deprecated Use the UI methods {@link UI.ScrollToElement()} or {@link UI.ScrollToOffset()} instead.
 */
function scrollTo(target, options)
{
	UI.ScrollToElement(target, options);
};
 

/**
 * Converts a string to a boolean value (can also be a boolean so this can
 * be a catch-all). Unrecognized strings are considered false.
 * 
 * Examples:
 * 
 * <pre>
 * string2bool('true') - true
 * string2bool('false') - false
 * string2bool('yes') - true
 * string2bool('no') - false
 * string2bool('Some text') - false
 * string2bool(true) - true
 * string2bool(false) - false
 * </pre>
 * 
 * @param {String|Boolean} string A boolean string, or a boolean value
 * @returns {Boolean}
 */
function string2bool(string)
{
	if(string==true || string=='true' || string == 'yes') {
		return true;
	}
	
	return false;
}

/**
 * Converts a boolean value to a string representation. String
 * booleans are recognized as well, so this can be used as a
 * catch all.
 * 
 * Examples:
 * 
 * <pre>
 * bool2string(true) - 'true'
 * bool2string(false) - 'false'
 * bool2string('true') - 'true'
 * bool2string('false') - 'false'
 * bool2string('yes') - 'true'
 * bool2string('no') - 'false'
 * bool2string('Some text') - 'false'
 * 
 * bool2string(true, true) - 'yes'
 * bool2string(false, true) - 'no'
 * </pre>
 * 
 * @param {Boolean|String} bool A boolean value, either as a regular boolean value or a string (yes/no/true/false). 
 * @param {Boolean} [yesno=false] Whether to return <code>yes</code> or <code>no</code> instead of <code>true</code> and <code>false</code>
 * @return {String} 
 */
function bool2string(bool, yesno)
{
	// make sure we're working with a boolean value
	bool = string2bool(bool);
	
	if(bool==true) {
		if(yesno==true) {
			return 'yes';
		}
		
		return 'true';
	}
	
	if(yesno==true) {
		return 'no';
	}
	
	return 'false';
}

/**
 * Checks whether the specified string/value is a boolean value.
 * Considered boolean are the following values:
 * 
 * <ul>
 * <li>boolean true</li>
 * <li>boolean false</li>
 * <li>string true</li>
 * <li>string false</li>
 * <li>string yes</li>
 * <li>string no</li>
 * </ul>
 * 
 * @param {String|Boolean} subject
 * @return boolean
 */
function isBoolean(subject)
{
	var booleanValues = ['true', 'yes', true, 'false', 'no', false];
	for(var i=0; i<booleanValues.length; i++) {
		if(booleanValues[i]===subject) {
			return true;
		}
	}
	
	return false;
}

function in_array(needle, haystack, argStrict) {
    //  discuss at: http://phpjs.org/functions/in_array/
    // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // improved by: vlado houba
    // improved by: Jonas Sciangula Street (Joni2Back)
    //    input by: Billy
    // bugfixed by: Brett Zamir (http://brett-zamir.me)
    //   example 1: in_array('van', ['Kevin', 'van', 'Zonneveld']);
    //   returns 1: true
    //   example 2: in_array('vlado', {0: 'Kevin', vlado: 'van', 1: 'Zonneveld'});
    //   returns 2: false
    //   example 3: in_array(1, ['1', '2', '3']);
    //   example 3: in_array(1, ['1', '2', '3'], false);
    //   returns 3: true
    //   returns 3: true
    //   example 4: in_array(1, ['1', '2', '3'], true);
    //   returns 4: false

    var key = '',
        strict = !! argStrict;

    //we prevent the double check (strict && arr[key] === ndl) || (!strict && arr[key] == ndl)
    //in just one for, in order to improve the performance
    //deciding wich type of comparation will do before walk array
    if (strict) {
        for (key in haystack) {
            if (haystack[key] === needle) {
                return true;
            }
        }
    } else {
        for (key in haystack) {
            if (haystack[key] == needle) {
                return true;
            }
        }
    }

    return false;
}

/**
 * Checks whether the specified variable can be considered empty, i.e.:
 * 
 * - Is undefined
 * - Is null
 * - Is an empty string
 * - Has 0 length
 * 
 * > Note: A numeric 0 is not considered empty.
 * 
 * @param subject
 * @returns {Boolean}
 */
function isEmpty(subject)
{
	if(typeof(subject)=='undefined' || subject==null || subject=='') {
		return true;
	}
	
	if(typeof(subject) == 'object' && typeof(subject.length) != 'undefined' && subject.length == 0) {
		return true;
	}

	return false;
}

function ucfirst(str) {
  //  discuss at: http://phpjs.org/functions/ucfirst/
  // original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  // bugfixed by: Onno Marsman
  // improved by: Brett Zamir (http://brett-zamir.me)
  //   example 1: ucfirst('kevin van zonneveld');
  //   returns 1: 'Kevin van zonneveld'

  str += '';
  var f = str.charAt(0)
    .toUpperCase();
  return f + str.substr(1);
}

function strnatcasecmp(str1, str2) {
  //       discuss at: http://phpjs.org/functions/strnatcasecmp/
  //      original by: Martin Pool
  // reimplemented by: Pierre-Luc Paour
  // reimplemented by: Kristof Coomans (SCK-CEN (Belgian Nucleair Research Centre))
  // reimplemented by: Brett Zamir (http://brett-zamir.me)
  //      bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //         input by: Devan Penner-Woelk
  //      improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
  //        example 1: strnatcasecmp(10, 1);
  //        example 1: strnatcasecmp('1', '10');
  //        returns 1: 1
  //        returns 1: -1

  var a = (str1 + '')
    .toLowerCase();
  var b = (str2 + '')
    .toLowerCase();

  var isWhitespaceChar = function(a) {
    return a.charCodeAt(0) <= 32;
  };

  var isDigitChar = function(a) {
    var charCode = a.charCodeAt(0);
    return (charCode >= 48 && charCode <= 57);
  };

  var compareRight = function(a, b) {
    var bias = 0;
    var ia = 0;
    var ib = 0;

    var ca;
    var cb;

    // The longest run of digits wins.  That aside, the greatest
    // value wins, but we can't know that it will until we've scanned
    // both numbers to know that they have the same magnitude, so we
    // remember it in BIAS.
    for (var cnt = 0; true; ia++, ib++) {
      ca = a.charAt(ia);
      cb = b.charAt(ib);

      if (!isDigitChar(ca) && !isDigitChar(cb)) {
        return bias;
      } else if (!isDigitChar(ca)) {
        return -1;
      } else if (!isDigitChar(cb)) {
        return 1;
      } else if (ca < cb) {
        if (bias === 0) {
          bias = -1;
        }
      } else if (ca > cb) {
        if (bias === 0) {
          bias = 1;
        }
      } else if (ca === '0' && cb === '0') {
        return bias;
      }
    }
  };

  var ia = 0,
    ib = 0;
  var nza = 0,
    nzb = 0;
  var ca, cb;
  var result;

  while (true) {
    // only count the number of zeroes leading the last number compared
    nza = nzb = 0;

    ca = a.charAt(ia);
    cb = b.charAt(ib);

    // skip over leading spaces or zeros
    while (isWhitespaceChar(ca) || ca === '0') {
      if (ca === '0') {
        nza++;
      } else {
        // only count consecutive zeroes
        nza = 0;
      }

      ca = a.charAt(++ia);
    }

    while (isWhitespaceChar(cb) || cb === '0') {
      if (cb === '0') {
        nzb++;
      } else {
        // only count consecutive zeroes
        nzb = 0;
      }

      cb = b.charAt(++ib);
    }

    // process run of digits
    if (isDigitChar(ca) && isDigitChar(cb)) {
      if ((result = compareRight(a.substring(ia), b.substring(ib))) !== 0) {
        return result;
      }
    }

    if (ca === '0' && cb === '0') {
      // The strings compare the same.  Perhaps the caller
      // will want to call strcmp to break the tie.
      return nza - nzb;
    }

    if (ca < cb) {
      return -1;
    } else if (ca > cb) {
      return +1;
    }

    // prevent possible infinite loop
    if (ia >= a.length && ib >= b.length) return 0;

    ++ia;
    ++ib;
  }
}

// See https://developer.mozilla.org/fr/docs/Web/JavaScript/Reference/Objets_globaux/String/repeat
if (!String.prototype.repeat) 
{
	String.prototype.repeat = function (count) 
	{
		"use strict";
		if (this == null) {
			throw new TypeError("Cannot convert " + this + " to object");
		}
		
		var str = "" + this;
		
		count = +count;
		if (count != count) {
			count = 0;
		}
		
		if (count < 0) {
			throw new RangeError("Repetition count cannot be a negative number");
		}
		
		if (count == Infinity) {
			throw new RangeError("Repetition count must be inferior to inifity");
		}
		
		count = Math.floor(count);
		if (str.length == 0 || count == 0) {
			return "";
		}

		if (str.length * count >= 1 << 28) {
			throw new RangeError("Repetition count must be inferior to the maximum string length");
		}
		
		var rpt = "";
		for (;;) {
			if ((count & 1) == 1) {
				rpt += str;
			}
			
			count >>>= 1;
			if (count == 0) {
				break;
			}
			
			str += str;
		}
			
		return rpt;
	}
}

function number_format (number, decimals, decPoint, thousandsSep) 
{ 
	  //  discuss at: http://locutus.io/php/number_format/
	  number = (number + '').replace(/[^0-9+\-Ee.]/g, '')
	  var n = !isFinite(+number) ? 0 : +number
	  var prec = !isFinite(+decimals) ? 0 : Math.abs(decimals)
	  var sep = (typeof thousandsSep === 'undefined') ? ',' : thousandsSep
	  var dec = (typeof decPoint === 'undefined') ? '.' : decPoint
	  var s = ''

	  var toFixedFix = function (n, prec) {
	    var k = Math.pow(10, prec);
	    return '' + (Math.round(n * k) / k).toFixed(prec);
	  }

	  s = (prec ? toFixedFix(n, prec) : '' + Math.round(n)).split('.')
	  if (s[0].length > 3) {
	    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep)
	  }
	  if ((s[1] || '').length < prec) {
	    s[1] = s[1] || ''
	    s[1] += new Array(prec - s[1].length + 1).join('0')
	  }

	  return s.join(dec)
}

/**
 * Highlights an URL using the URI.js library.
 * 
 * @param {String} url
 * @returns {String}
 */
function highlightURL(url)
{
	var uri = new URI(url);
	var tokens = [];
	
	var origin = uri.origin();
	if(!isEmpty(origin)) {
		tokens.push({
			'type':'origin',
			'content':origin
		});
	}
	
	var path = uri.path();
	if(!isEmpty(path)) {
		var parts = path.split('/');
		tokens.push({
			'type':'path',
			'content':parts.join('<span class="url-path-separator">/</span>')
		});
	}
	
	var queryString = uri.search();
	if(!isEmpty(queryString)) {
		var query = uri.search(true);
		var first = true;
		$.each(query, function(varname, value) {
			var queryToken = '&amp;';
			if(first) {
				queryToken = '?';
				first = false;
			}
			
			tokens.push({
				'type':'query-token',
				'content':queryToken
			});
			
			tokens.push({
				'type':'query-variable',
				'content':varname
			});
			
			tokens.push({
				'type':'query-token',
				'content':'='
			});
			
			tokens.push({
				'type':'query-value',
				'content':value
			});
		});
	}
	
	var fragment = uri.fragment();
	if(!isEmpty(fragment)) {
		tokens.push({
			'type':'fragment',
			'content':'#'+fragment
		});
	}
	
	var html = '';
	for(var i=0; i < tokens.length; i++) {
		var token = tokens[i];
		html += ''+
		'<span class="url-'+token.type+'">'+
			token.content+
		'</span>';
	}
	
	return html;
}

function str_repeat(pattern, count) {
    if (count < 1) return '';
    var result = '';
    while (count > 1) {
        if (count & 1) result += pattern;
        count >>= 1, pattern += pattern;
    }
    return result + pattern;
}

function zerofill(number, length)
{
	var str = str_repeat('0', length); 
	return (str+number).slice(-length);
}

/**
 * Utility function: checks if the subject is empty,
 * and if it is, returns the specified empty value.
 * Otherwise, leaves it unchanged.
 * 
 * 
 * @param {mixed} subject
 * @param {mixed} emptyValue
 */
function getEmpty(subject, emptyValue)
{
	if(isEmpty(subject)) {
		return emptyValue;
	}
	
	return subject;
}

function registerCrossbrowserEvent(eventTarget, eventName, callback, useCapture)
{
	if(eventTarget.addEventListener)
	{
		return eventTarget.addEventListener(eventName, callback, useCapture);
	} 
	
	if(eventTarget.attachEvent)
	{
		return eventTarget.attachEvent('on'+eventName, callback);
	}
	
	eventTarget['on'+eventName] = callback;
}

/**
 * Checks whether the specified subject is a valid date object.
 * 
 * @param {Date} subject
 * @returns {Boolean}
 * @see http://stackoverflow.com/questions/1353684/detecting-an-invalid-date-date-instance-in-javascript
 */
function isDate(subject)
{
	if(Object.prototype.toString.call(subject) == '[object Date]' && isFinite(subject)) {
		return true;
	}
	
	return false;
}

/**
 * Converts an ISO date string to a date object.
 * Use this to ensure browser-independent date parsing,
 * since the native Date.parse method can return 
 * different results.
 * 
 * Example:
 * 
 * <pre>
 * string2date('2017/04/14');
 * </pre>
 * 
 * Will return NULL if the date could not be parsed.
 * 
 * @param {String} isoString
 * @return {Date}
 */
function string2date(isoString)
{
	if(isEmpty(isoString)) {
		return null;
	}
	
	var myregexp = /(19|20)[0-9]{2}[- \/.](0[1-9]|1[012])[- \/.](0[1-9]|[12][0-9]|3[01])/g;
	var matches = [];
	var match = myregexp.exec(isoString);
	while (match != null) {
		for (var i = 1; i < match.length; i++) {
			matches.push(match[i]);
		}
		match = myregexp.exec(isoString);
	}
	
	if(matches.length == 3) {
		return new Date(matches[0], matches[1], matches[2]);
	}
	
	return null;
}

/**
 * Converts an ISO date string to a date object with time.
 * Use this to ensure browser-independent date parsing,
 * since the native Date.parse method can return 
 * different results.
 * 
 * Example:
 * 
 * <pre>
 * string2datetime('2017/04/14 14:45:12');
 * </pre>
 * 
 * Will return NULL if the date could not be parsed. 
 * 
 * @param {String} isoString
 * @return {Date}
 */
function string2datetime(isoString)
{
	if(isEmpty(isoString)) {
		return null;
	}
	
	var myregexp = /(19|20)[0-9]{2}[- \/.](0[1-9]|1[012])[- \/.](0[1-9]|[12][0-9]|3[01])[ ]*([0-9]{2}):([0-9]{2}):([0-9]{2})/g;
	var matches = [];
	var match = myregexp.exec(isoString);
	while (match != null) {
		for (var i = 1; i < match.length; i++) {
			matches.push(match[i]);
		}
		match = myregexp.exec(isoString);
	}
	
	if(matches.length == 6) {
		return new Date(matches[0], matches[1], matches[2], matches[3], matches[4], matches[5]);
	}
	
	return null;
}

function isFirefox()
{
	return typeof(InstallTrigger) !== 'undefined';
}

$.fn.selectRange = function(start, end) {
    if(end === undefined) {
        end = start;
    }
    return this.each(function() {
        if('selectionStart' in this) {
            this.selectionStart = start;
            this.selectionEnd = end;
        } else if(this.setSelectionRange) {
            this.setSelectionRange(start, end);
        } else if(this.createTextRange) {
            var range = this.createTextRange();
            range.collapse(true);
            range.moveEnd('character', end);
            range.moveStart('character', start);
            range.select();
        }
    });
};