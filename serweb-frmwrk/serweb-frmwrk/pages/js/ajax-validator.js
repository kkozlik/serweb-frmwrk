/**
 *  Various validator functions based on AJAX request.
 *  
 *  So far only validation for mass delete form is implemented.
 *  
 *  @param  string  server_url  URL where the AJAX request is sent
 */ 
function AJAX_validator(server_url){
    this.server_url = server_url;
    this.el_class = null;
}


/**
 *  Bind validation function to form for mass delete.
 *  
 *  @param  string  form_id     ID of the form element to bind the validator to
 *  @param  string  el_class    if is set, it could specify the checkbox 
 *                              elements by their css class
 */ 
AJAX_validator.prototype.validate_mass_form = function(form_id, el_class){

    this.form = document.getElementById(form_id);
    this.el_class = el_class;
    add_event(this.form, 'submit', this.validate_mass_form_handler.bindObj(this));

}

/**
 *  This is the validation handler executed once the mass form is submited.
 *  It collects data from the form, send them to the server and in dependency 
 *  on server response it could display confirmation message.
 */ 
AJAX_validator.prototype.validate_mass_form_handler = function(e){

    if(!e) var e = window.event;

    var post_data="";

    // collect the data from the form and generate POST request
    for(var i in this.form.elements){
        var el = this.form.elements[i];
        if (this.el_class && !hasClassName(el, this.el_class)) continue;
    
        if (el.type == "checkbox" && el.checked){
            if (post_data != "") post_data += "&";
            post_data += encodeURIComponent(el.name) + "=" + encodeURIComponent(el.value);
        }
    }

    if (post_data == "") return;

    // send the ajax request
    var http_request = ajax_sync_request(this.server_url, post_data);

    // if request has not been succesful 
    if (!http_request || http_request.status != 200) return;

    var response = eval('(' + http_request.responseText + ')');

    // if the response contain 'confirm_text' property, display the text in 
    // confirm dialog. If user do not confirm it, cancel the form submission.
    if (response.confirm_text){
        if (!confirm(response.confirm_text)){
            e.returnValue = false;
            if (e.preventDefault) {
                e.preventDefault();
            }
        }
    }
}


/**
 *  This is the validation handler for a single action.
 *  Before the action is executed this function make AJAX request to specified URL. 
 *  In dependency on server response it could display confirmation message.
 */ 
AJAX_validator.prototype.validate_single_action = function(url){

    // send the ajax request
    var http_request = ajax_sync_request(url);

    // if request has not been succesful 
    if (!http_request || http_request.status != 200) return false;

    var response = eval('(' + http_request.responseText + ')');

    // if the response contain 'confirm_text' property, display the text in 
    // confirm dialog. If user do not confirm it, cancel the form submission.
    if (response.confirm_text){
        if (!confirm(response.confirm_text)){
            return false;
        }
    }

    return true;
}
