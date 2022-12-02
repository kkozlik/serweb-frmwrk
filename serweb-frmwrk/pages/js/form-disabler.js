/**
 *  This file contain FormDisabler class used to enable/disable 'OK' and 'Apply'
 *  form element based on values of other elements 
 *  
 *	This file require 'functions.js' file to be loaded
 *
 *	@author		Karel Kozlik
 */

function FormDisabler(varname){
	this.form = null;
	this.disabledOnLoad = new Array();
	this.inactiveElements = new Array();

	/* flag - form has been changed */
	this.changed = false;
	
	/* name of variable pointing to reference of this object */
	this.varname = varname;
}

FormDisabler.prototype.init = function(form_name, disable_forever){
	var forms = document.forms;
	var i;
	var attr;
	
	for (i=0; i<forms.length; i++){
		if (forms[i].name == form_name) {
			this.form = forms[i];
			break;
		}
	}

	if (this.form == null) return;
	
	/* if form should be disabled forever, do not set onchange events */
	if (disable_forever) return;

	for (i=0; i<this.form.elements.length; i++){
        if (this.in_array(this.form.elements[i].name, this.inactiveElements)) continue;

		if (
		    this.form.elements[i].type == "select-one" ||
		    this.form.elements[i].type == "select-multiple"){
			
            add_event(this.form.elements[i], 'change', this.onChange.bindObj(this));
		}
		else if (
			this.form.elements[i].type == "checkbox" ||
			this.form.elements[i].type == "radio"){

            add_event(this.form.elements[i], 'mouseup', this.onChange.bindObj(this));
		}
        else if(this.form.elements[i].type == "text" || 
                this.form.elements[i].type == "password" ||
                this.form.elements[i].type == "textarea" ||
                this.form.elements[i].type == "file"){

                add_event(this.form.elements[i], 'cut', this.onChange.bindObj(this));
                add_event(this.form.elements[i], 'paste', this.onChange.bindObj(this));
                add_event(this.form.elements[i], 'keyup', this.onChange.bindObj(this));
                add_event(this.form.elements[i], 'change', this.onChange.bindObj(this));
        }
    }
}

FormDisabler.prototype.defineDisabled = function(disabled_el){
    var i,j;
    
    if (this.form == null) return;
    
    var initial_state = !this.initUsrFunct();

    j=0;
    for (i=0; i<this.form.elements.length; i++){
        if (this.in_array(this.form.elements[i].name, disabled_el)){
            this.disabledOnLoad[j++] = this.form.elements[i];
            this.form.elements[i].disabled = initial_state;
        }
    }
}

FormDisabler.prototype.in_array = function(str, ar){
	var i;

	for (i=0; i<ar.length; i++){
		if (ar[i] == str) return true;
	}
	
	return false;
}


/*
    Function called always when form is changed. If this function returns
    true, submit buttons are enabled. If returns false, submits are disabled.
    
    This function could be overridden by customized one.
*/
FormDisabler.prototype.onChangeUsrFunct = function(){
    return true;
}

/*
    Function called when page is dispalyed. If this function returns
    true, submit buttons are enabled. If returns false, submits are disabled.
    
    This function could be overridden by customized one.
*/
FormDisabler.prototype.initUsrFunct = function(){
    return false;
}

FormDisabler.prototype.onChange = function(){
	this.changed = true;

	for (i=0; i<this.disabledOnLoad.length; i++)
		this.disabledOnLoad[i].disabled = !this.onChangeUsrFunct();
}

function FormSubmitedWith(){
	this.submited_with = null;
}

FormSubmitedWith.prototype.set = function(submit_name){
	this.submited_with = submit_name;
}

FormSubmitedWith.prototype.get = function(){
	return this.submited_with;
}


