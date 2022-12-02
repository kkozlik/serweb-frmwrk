/**
 *  Javascript functions used by apu_filter
 * 
 *  $Id: filter.js,v 1.1 2007/12/17 12:06:54 kozlik Exp $
 */


/**
 *  Class used for manipulating filter form
 **/ 
function Filter_Form(form_name, filter_elements){
    this.filter_elements = filter_elements;

	var forms = document.forms;
	
	for (var i=0; i<forms.length; i++){
		if (forms[i].name == form_name) {
			this.form = forms[i];
			break;
		}
	}
}

/**
 *	Clear filter form
 */ 
Filter_Form.prototype.filter_clear = function(){

    var el;
    
    for (var i=0; i < this.filter_elements.length; i++){
        el = this.filter_elements[i];
    
        switch (el.type){
        case "text":
            if (typeof(this.form[el.name]) != "undefined"){
                this.form[el.name].value = "";
            }
            break;
        case "select":
            if (typeof(this.form[el.name]) != "undefined"){

                if (this.form[el.name].type == "select-multiple"){
                    var opts = this.form[el.name].options;
                    for (var j=0; j < opts.length; j++){
                        opts[j].selected = false;
                    }
                }
                else{
                    this.form[el.name].selectedIndex = 0;
                }
            }
            break;
        case "checkbox":
            if (el.three_state){
                if (typeof(this.form[el.name+'_en']) != "undefined"){
                    this.form[el.name+'_en'].checked = false;
                }
                if (typeof(this.form[el.name]) != "undefined"){
                    this.form[el.name].disabled = true;
                }
            }
            else{
                if (typeof(this.form[el.name]) != "undefined"){
                    this.form[el.name].checked = false;
                }
                if (typeof(this.form[el.name+'_hidden']) != "undefined"){
                    this.form[el.name+'_hidden'].value=0;
                }
            }
            break;
        
        }
    }
}
