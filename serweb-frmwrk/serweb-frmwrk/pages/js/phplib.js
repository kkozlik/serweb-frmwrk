/**
 *  Javascript functions supporting phplib object oriented html forms
 * 
 *  $Id: $
 *
 */


function PHPlib_ctl(varname){

    /* name of variable pointing to reference of this object */
    this.varname = varname;
}

/**
 *  Register handler (fn) of event (evt) on object (obj) - browser independent
 */
PHPlib_ctl.prototype.add_event = function(obj, evt, fn){
    if (!obj) return;

    // register 'unload' event listener to opener window which close find popup 
    // when opened
    if (obj.addEventListener) //w3c model
        obj.addEventListener(evt, fn, false);
    else if (obj.attachEvent) //MS model
        obj.attachEvent('on'+evt, fn);
    else //other
        obj['on'+evt] = fn;
}

/**
 *  Unregister handler (fn) of event (evt) on object (obj) - browser independent
 */
PHPlib_ctl.prototype.remove_event = function(obj, evt, fn){
    if (!obj) return;

	if (obj.removeEventListener) //w3c model
		obj.removeEventListener(evt, fn, false);
	else if (obj.detachEvent) //MS model
		obj.detachEvent('on'+evt, fn);
    else //other
        obj['on'+evt] = null;
}

/**
 *  Get element which invoked the event
 */
PHPlib_ctl.prototype.get_element_from_event = function(event){
    var el;
    if (event.target) el = event.target;
    else if (event.srcElement) el = event.srcElement;

    if (el.nodeType == 3)   // defeat Safari bug
        el = el.parentNode;
        
    return el;
}


/**
 *  Trim whitespaces from begining and end of value of element
 */  
PHPlib_ctl.prototype.trim = function(el){
    if (!el) return;

    el.value = el.value.replace(new RegExp("^[ \t\n\r]*"), "");
    el.value = el.value.replace(new RegExp("[ \t\n\r]*$"), "");
}

/**
 *  On_change handler triming whitespaces from begining and end of value
 */  
PHPlib_ctl.prototype.oh_trim = function(event){
    if (!event) var event = window.event;

    var el=PHPlib_ctl.prototype.get_element_from_event(event);
    PHPlib_ctl.prototype.trim(el);
}


/**
 *  On_change handler for limit max length of textarea elements
 */  
PHPlib_ctl.prototype.oh_textarea_max_length = function(event) {
    if (!event) var event = window.event;

    var el=PHPlib_ctl.prototype.get_element_from_event(event);
    
    /* Count the length of the string. If there is alone LF char 
       replace it with CRLF sequence because all LineFeeds are transfered
       to php as CRLF.
     */
    var length = el.value.replace(/([^\r])\n/g, "$1\r\n").length;

    switch (event.type){
    case 'keypress':
        // Detect whether a special key has been pressed
        
        // in FF and Opera is event.which==0 
        if (typeof(event.which) != "undefined" && event.which == 0) break;
        if (event.which == 8) break; // backspace (in Opera)
        // IE does not invoke the event at all when special key sis pressed, 
        // so no checking is necessary
        
        
        // normal kye has been pressed, validate the length
        if (length >= el.my_max_length) {
            //prevent default action
            if ('function' == typeof(event.preventDefault)) event.preventDefault(); //w3c model
            event.returnValue=false; //MS model
            return false;
        }
        break;
    default:
        if (length > el.my_max_length){

            // count alone LF characters (those that are not in CRLF sequence)
            var alone_lf = 0;

            var s = el.value.replace(/\r\n/g, ""); //first remove all CRLF sequences from value
            s = s.replace(/[^\n]/g, "");           //then remove all characters eccept LF 
            alone_lf = s.length;

            // Alone LF characters should be counted twice in the length (see 
            // comment above).
            // So decrementing max_length by alone_lf have the same effect.        
            el.value = el.value.substr(0, el.my_max_length-alone_lf);
        }
    }

    return true;
}



phplib_ctl = new PHPlib_ctl('phplib_ctl');


