/**
 *  This file contain javascript functions used by growable forms unit.
 *
 *  This file require 'functions.js' file to be loaded
 */

/**
 *  Object that should generalize access to bootstrap grid holding form items.
 *
 *  But ussualy not all rows of the grid belong to the item collection.
 *  One of the rows of the grid should be marked with ID given by
 *  "starter_id" parameter. Then rows just bellow this one, that contain
 *  "row_class" in the class param belongs to the collection. There could
 *  be optionaly one row with "row_class" that contain a header for all items
 *  in the collection.
 *
 *  @param  string  container_id   ID of div container containing the table of the items
 *  @param  string  html_row_id_prefix  prefix of row IDs
 *  @param  string  row_class
 *  @param  string  head_class
 *  @param  string  starter_id
 */
function GF_bootstrap_grid(container_id, html_row_id_prefix, row_class, head_class, starter_id){

    this.row_class = 'row';

    this.html_row_id_prefix = html_row_id_prefix;
    this.container_id = container_id;
    this.grid_el = document.getElementById(this.container_id);
    if (row_class) this.row_class = row_class;
    this.head_class = head_class;
    this.starter_id = starter_id;
}

GF_bootstrap_grid.prototype.get_tmp_row_parent = function(){
    return "div";
}

/**
 *  Get the ID of item from given html row element
 *
 *  @param  HTMLTableRowElement row     table row
 */
GF_bootstrap_grid.prototype.get_row_id = function(row){
    return row.id.substr(this.html_row_id_prefix.length);
}

/**
 *  Get the html row element by ID of item
 *
 *  @param  string      id          item ID
 *
 *  @return  HTMLTableRowElement    table row
 */
GF_bootstrap_grid.prototype.get_row_by_id = function(id){
    return document.getElementById(this.html_row_id_prefix+id);
}

GF_bootstrap_grid.prototype.next_element_sibling = function(tag){
    var next = tag;
    var tagname = tag.tagName.toLowerCase();
    while(next = next.nextSibling){
        if(next.tagName && next.tagName.toLowerCase() == tagname)
            return next;
    }
    return null;
}

/**
 *  Return array of heading rows from given table
 */
GF_bootstrap_grid.prototype.get_tbl_head_rows = function(table){
    return get_elements_by_className(table, 'DIV', this.head_class);
}

/**
 *  Return array of rows holding form items from given table
 */
GF_bootstrap_grid.prototype.get_tbl_rows = function(table){
    return get_elements_by_className(table, 'DIV', this.row_class);
}

/**
 *  Return array of rows holding form items
 */
GF_bootstrap_grid.prototype.get_rows = function(){
    if (null == this.grid_el) return new Array();
    else return this.get_tbl_rows(this.grid_el);
}

/**
 *  Append new row to grid
 */
GF_bootstrap_grid.prototype.add_row = function(row, insert_before){

    var ref_row = null;
    if (null != insert_before){
        ref_row = insert_before;
    }
    else{
        var item_rows = this.get_rows();
        if (item_rows.length == 0) item_rows = this.get_tbl_head_rows(this.grid_el);

        if (item_rows.length == 0) {
            if (this.starter_id) ref_row = document.getElementById(this.starter_id);
            else                 ref_row = null;
        }
        else ref_row = item_rows[item_rows.length-1];

        if (ref_row) ref_row = this.next_element_sibling(ref_row);
    }

    this.grid_el.insertBefore(row, ref_row);
}


/**
 *  Add heading rows to table
 */
GF_bootstrap_grid.prototype.add_head_rows = function(rows){

    var ref_row = null;
    var item_rows = this.get_tbl_head_rows(this.grid_el);

    if (item_rows.length == 0){
        if (this.starter_id) ref_row = document.getElementById(this.starter_id);
        else                 ref_row = null;
    }
    else ref_row = item_rows[item_rows.length-1];

    if (ref_row) ref_row = this.next_element_sibling(ref_row);

    // copy head rows to html container table
    for (var i=rows.length-1; i>=0; i--){
        ref_row=this.grid_el.insertBefore(rows[i].cloneNode(true), ref_row);
    }
}

/**
 *  Remove given row from table
 */
GF_bootstrap_grid.prototype.del_row = function(row){
    row.parentNode.removeChild(row);
}

/**
 *  Remove table heading
 */
GF_bootstrap_grid.prototype.del_head = function(){
    var rows = this.get_tbl_head_rows(this.grid_el);

    for (var i=0; i<rows.length; i++){
        this.del_row(rows[i]);
    }
}

/**
 *  Create the grid el if it do not exists
 */
GF_bootstrap_grid.prototype.create_table = function(){
    return; //grid el have to already exists, so nothing to do
}

/**
 *  Return number of rows holding form items
 *
 *  @return  int
 */
GF_bootstrap_grid.prototype.count_rows = function(){
    var rows = this.get_rows();
    return rows.length;
}








/**
 *  Abstract object that should generalize access to html table holding
 *  items of the form. This object should not be created directly, but
 *  throught its derivations.
 *
 *  @param  string  html_row_id_prefix  prefix of ID's of rows holding the items
 */
function GF_html_table(html_row_id_prefix){
    this.html_row_id_prefix = html_row_id_prefix;
}

GF_html_table.prototype.get_tmp_row_parent = function(){
    return "table";
}

/**
 *  Get the ID of item from given html row element
 *
 *  @param  HTMLTableRowElement row     table row
 */
GF_html_table.prototype.get_row_id = function(row){
    return row.id.substr(this.html_row_id_prefix.length);
}

/**
 *  Get the html row element by ID of item
 *
 *  @param  string      id          item ID
 *
 *  @return  HTMLTableRowElement    table row
 */
GF_html_table.prototype.get_row_by_id = function(id){
    return document.getElementById(this.html_row_id_prefix+id);
}


GF_html_table.prototype.get_rows = function(){ alert("Abstract function 'get_rows' called!"); }
GF_html_table.prototype.add_row = function(row, insert_before){ alert("Abstract function 'add_row' called!"); }
GF_html_table.prototype.add_head_rows = function(rows){ alert("Abstract function 'add_head_rows' called!"); }
GF_html_table.prototype.del_row = function(row){ alert("Abstract function 'del_row' called!"); }
GF_html_table.prototype.del_head = function(){ alert("Abstract function 'del_head' called!"); }
GF_html_table.prototype.get_tbl_head_rows = function(table){ alert("Abstract function 'get_tbl_head_rows' called!"); }
GF_html_table.prototype.get_tbl_rows = function(table){ alert("Abstract function 'get_tbl_rows' called!"); }
GF_html_table.prototype.create_table = function(){ alert("Abstract function 'create_table' called!"); }
GF_html_table.prototype.count_rows = function(){ alert("Abstract function 'count_rows' called!"); }


/**
 *  Object that should generalize access to html table holding form items.
 *  This object should be used for separate html tables that stores the form
 *  items as TBODY html elements
 *
 *  @param  string  html_container_id   ID of div container containing the table of the items
 *  @param  string  html_row_id_prefix  prefix of row IDs
 */
function GF_separate_table(html_container_id, html_row_id_prefix){
    this.html_row_id_prefix = html_row_id_prefix;
    this.html_container_id = html_container_id;
    this.html_table_id = html_container_id + "Table";
    this.html_table_el = document.getElementById(this.html_table_id);

}
GF_separate_table.inheritsFrom(GF_html_table);


/**
 *  Return array of heading rows from given table
 */
GF_separate_table.prototype.get_tbl_head_rows = function(table){
    if (table.tHead == null) return new Array();
    else return table.tHead.rows;
}

/**
 *  Return array of rows holding form items from given table
 */
GF_separate_table.prototype.get_tbl_rows = function(table){
    return table.tBodies;
}

/**
 *  Return array of rows holding form items
 */
GF_separate_table.prototype.get_rows = function(){
    if (null == this.html_table_el) return new Array();
    else return this.get_tbl_rows(this.html_table_el);
}

/**
 *  Append new row to table
 */
GF_separate_table.prototype.add_row = function(row, insert_before){
    this.html_table_el.insertBefore(row, insert_before);
}

/**
 *  Add heading rows to table
 */
GF_separate_table.prototype.add_head_rows = function(rows){

    if (this.html_table_el.tHead == null) this.html_table_el.createTHead();

    // copy head rows to html container table
    for (var i=0; i<rows.length; i++){
        this.html_table_el.tHead.appendChild(rows[i].cloneNode(true));
    }
}

/**
 *  Remove given row from table
 */
GF_separate_table.prototype.del_row = function(row){
    while (row.rows.length){ //row is TBODY, delete all rows of it
        row.deleteRow(0);
    }
    row.parentNode.removeChild(row);
}

/**
 *  Remove table heading
 */
GF_separate_table.prototype.del_head = function(){
    this.html_table_el.deleteTHead();
}

/**
 *  Create the HTML table if it do not exists
 */
GF_separate_table.prototype.create_table = function(){
    // if table containing items does not exists, create it
    if (null == this.html_table_el){
        container_el = document.getElementById(this.html_container_id);
        container_el.innerHTML = '<table id="'+this.html_table_id+'" border="0" cellspacing="0" cellpadding="0"></table>';
        this.html_table_el = document.getElementById(this.html_table_id);
    }
}

/**
 *  Return number of rows holding form items
 *
 *  @return  int
 */
GF_separate_table.prototype.count_rows = function(){
    var item_cnt = 0;

    if (null != this.html_table_el){
        for (var i=0; i < this.html_table_el.tBodies.length; i++){
            if (this.html_table_el.tBodies[i].rows.length > 0) item_cnt++;
        }
    }
    return item_cnt;
}








/**
 *  Object that should generalize access to html table holding form items.
 *  This object should be used for separate html tables that stores
 *  dynamic items in TBODY elements.
 *
 *  But ussualy not all TBODies of the table belong to the item collection.
 *  One of the TBODies of the table should be market with ID given by
 *  "starter_id" parameter. Then TBODies just bellow this one, that contain
 *  "row_class" in the class param belongs to the collection. There could
 *  be optionaly one TBODY with "row_class" that contain a header for all items
 *  in the collection.
 *
 *  @param  string  html_container_id   ID of div container containing the table of the items
 *  @param  string  html_row_id_prefix  prefix of row IDs
 *  @param  string  row_class
 *  @param  string  head_class
 *  @param  string  starter_id
 */
function GF_embeded_tbodies(html_table_id, html_row_id_prefix, row_class, head_class, starter_id){
    this.html_row_id_prefix = html_row_id_prefix;
    this.html_table_id = html_table_id;
    this.html_table_el = document.getElementById(this.html_table_id);
    this.row_class = row_class;
    this.head_class = head_class;
    this.starter_id = starter_id;
}
GF_embeded_tbodies.inheritsFrom(GF_html_table);


GF_embeded_tbodies.prototype.next_element_sibling = function(tag){
    var next = tag;
    var tagname = tag.tagName.toLowerCase();
	while(next = next.nextSibling){
		if(next.tagName && next.tagName.toLowerCase() == tagname)
			return next;
	}
	return null;
}

/**
 *  Return array of heading rows from given table
 */
GF_embeded_tbodies.prototype.get_tbl_head_rows = function(table){
    return get_elements_by_className(table, 'TBODY', this.head_class);
}

/**
 *  Return array of rows holding form items from given table
 */
GF_embeded_tbodies.prototype.get_tbl_rows = function(table){
    return get_elements_by_className(table, 'TBODY', this.row_class);
}

/**
 *  Return array of rows holding form items
 */
GF_embeded_tbodies.prototype.get_rows = function(){
    if (null == this.html_table_el) return new Array();
    else return this.get_tbl_rows(this.html_table_el);
}

/**
 *  Append new row to table
 */
GF_embeded_tbodies.prototype.add_row = function(row, insert_before){

    var ref_row = null;
    if (null != insert_before){
        ref_row = insert_before;
    }
    else{
        var item_rows = this.get_rows();
        if (item_rows.length == 0) item_rows = this.get_tbl_head_rows(this.html_table_el);

        if (item_rows.length == 0) ref_row = document.getElementById(this.starter_id);
        else ref_row = item_rows[item_rows.length-1];

        ref_row = this.next_element_sibling(ref_row);
    }

    this.html_table_el.insertBefore(row, ref_row);
}

/**
 *  Add heading rows to table
 */
GF_embeded_tbodies.prototype.add_head_rows = function(rows){

    var ref_row = null;
    var item_rows = this.get_tbl_head_rows(this.html_table_el);

    if (item_rows.length == 0) ref_row = document.getElementById(this.starter_id);
    else ref_row = item_rows[item_rows.length-1];

    ref_row = this.next_element_sibling(ref_row);

    // copy head rows to html container table
    for (var i=rows.length-1; i>=0; i--){
        ref_row=this.html_table_el.insertBefore(rows[i].cloneNode(true), ref_row);
    }
}

/**
 *  Remove given row from table
 */
GF_embeded_tbodies.prototype.del_row = function(row){
    while (row.rows.length){ //row is TBODY, delete all rows of it
        row.deleteRow(0);
    }
    row.parentNode.removeChild(row);
}

/**
 *  Remove table heading
 */
GF_embeded_tbodies.prototype.del_head = function(){
    var rows = this.get_tbl_head_rows(this.html_table_el);

    for (var i=0; i<rows.length; i++){
        this.del_row(rows[i]);
    }
}

/**
 *  Create the HTML table if it do not exists
 */
GF_embeded_tbodies.prototype.create_table = function(){
    return; //table have to already exists, so nothing to do
}

/**
 *  Return number of rows holding form items
 *
 *  @return  int
 */
GF_embeded_tbodies.prototype.count_rows = function(){
    var item_cnt = 0;
    var rows = this.get_rows();

    for (var i=0; i < rows.length; i++){
        if (rows[i].rows.length > 0) item_cnt++; // do not count TBODies that do not have any rows
    }

    return item_cnt;
}








/**
 *  @param  string  new_items_form_el   ID of hidden form element containing IDs of inserted items
 *  @param  string  del_items_form_el   ID of hidden form element containing IDs of deleted items
 */
function Growable_Forms_ctl(varname, new_items_form_el, del_items_form_el){
    this.add_item_url = "";
    this.add_item_url_id_param = "new_item_id";
    this.add_item_url_fr_param = "first_row";

    /* only viewing values - controls are disabled */
    this.view = false;

    /* name of variable pointing to reference of this object */
    this.varname = varname;

    /* new id to be assigned to item */
    this.new_item_id = 0;

    /* max and min constraints for number of items. Zero means no constraint. */
    this.max_items=0;
    this.min_items=0;

    /* if this value is not zero, inserting item is in proggress*/
    this.insert_in_proggress = 0;

    this.new_items_form_el = new_items_form_el;
    this.del_items_form_el = del_items_form_el;
    /* ID of form element containing priority value. Need to be set
       when UP and DOWN links are used */
    this.ordering_form_el = null;


    /* ID of span wrapper of ADD link */
    this.html_add_link_id = null;
    /* class of span wrapper of DEL links */
    this.html_del_link_class = null;
    /* class of span wrapper of UP links */
    this.html_up_link_class = null;
    /* class of span wrapper of DOWN links */
    this.html_down_link_class = null;


    this.table_handler = null;
    this.custom_init_fn = null;

    /* callbacks */
    this.on_add_mod_url = null;
    this.on_add_get_pos = null;
    this.on_add_enable  = null; //called when add link should be enabled or disabled
    this.on_add         = null;
    this.on_del         = null;
    this.on_up          = null;
    this.on_down        = null;
    this.on_pre_up      = null;
    this.on_pre_down    = null;
}

/**
 *  init function
 *
 */
Growable_Forms_ctl.prototype.init = function(form_name){

    var forms = document.forms;
    var self = this;

    for (var i=0; i<forms.length; i++){
        if (forms[i].name == form_name) {
            this.form = forms[i];
            break;
        }
    }

    if (null != this.custom_init_fn){
        executeFunctionByName(this.custom_init_fn, window, this);
    }

    // Register onclick event handlers to add-item link
    if (null != this.html_add_link_id){
        var spanEl = document.getElementById(this.html_add_link_id);
        if (null != spanEl) {
            spanEl.querySelectorAll('a, button').forEach(
                function(element){
                    element.addEventListener('click', self.add_item.bind(self));
            });
        }
    }
};

/**
 *
 */
Growable_Forms_ctl.prototype.set_table_handler = function(handler){
    this.table_handler = handler;
};

/**
 *  generate ID for new item
 *
 *  @return  string
 */
Growable_Forms_ctl.prototype.get_new_item_id = function(){
    return 'x'+(this.new_item_id++);
}

/**
 *  return number of items
 *
 *  @return  int
 */
Growable_Forms_ctl.prototype.count_items = function(){

    if (null != this.table_handler){
        return this.table_handler.count_rows();
    }
    else return 0;
}

/**
 *  Enable/disable controls when page is loaded
 */
Growable_Forms_ctl.prototype.controls_init = function(){

    var rows = this.table_handler.get_rows();
    for(var i=0; i<rows.length; i++){
        this.controls_register_events(rows[i]);
    }

    if (this.view){
        this.disable_controls();
    }
    else{
        this.controls_update();
        this.check_max_items();
        this.check_min_items();
    }
}

/**
 *  Disable all conrol links (UP/DOWN/ADD/DEL)
 */
Growable_Forms_ctl.prototype.disable_controls = function(){

    if (this.table_handler != null){
        var rows = this.table_handler.get_rows();

        for(var i=0; i<rows.length; i++){
            this.enable_del_link(false, rows[i]);
            this.enable_up_link(false, rows[i]);
            this.enable_down_link(false, rows[i]);
        }
    }

    this.enable_add_link(false);
}

/**
 *  Enable/disable up/down controls
 */
Growable_Forms_ctl.prototype.controls_update = function(){

    if (this.table_handler == null) return;

    var rows = this.table_handler.get_rows();

    // if there is only one row in the table
    if (rows.length == 1){
        this.enable_up_link(false, rows[0]);
        this.enable_down_link(false, rows[0]);
    }
    // if there is more than one row in the table
    else if (rows.length > 1){
        for(var i=0; i<rows.length; i++){
            if (i==0){ //first row
                this.enable_up_link(false, rows[i]);
                this.enable_down_link(true, rows[i]);
            }
            else if (i==rows.length-1){ //last row
                this.enable_up_link(true, rows[i]);
                this.enable_down_link(false, rows[i]);
            }
            else{ //middle row
                this.enable_up_link(true, rows[i]);
                this.enable_down_link(true, rows[i]);
            }
        }
    }
}

/**
 *  Register onclick event handlers to conrol links
 *
 *  @param  HTMLTableRowElement row     table row containing the control links link
 */
Growable_Forms_ctl.prototype.controls_register_events = function(row){
    var self = this;
    var row_id = this.table_handler.get_row_id(row);

    if (null != this.html_del_link_class){
        var spanEl = get_element_by_className(row, 'span', this.html_del_link_class);
        spanEl.querySelectorAll('a, button').forEach(
            function(element){
                element.addEventListener('click', self.del_item.bind(self, row_id));
        });
    }

    if (null != this.html_up_link_class){
        var spanEl = get_element_by_className(row, 'span', this.html_up_link_class);
        spanEl.querySelectorAll('a, button').forEach(
            function(element){
                element.addEventListener('click', self.item_up.bind(self, row_id));
        });
    }

    if (null != this.html_down_link_class){
        var spanEl = get_element_by_className(row, 'span', this.html_down_link_class);
        spanEl.querySelectorAll('a, button').forEach(
            function(element){
                element.addEventListener('click', self.item_down.bind(self, row_id));
        });
    }
}

/**
 *  Disable add link when number of items reached the max.
 */
Growable_Forms_ctl.prototype.check_max_items = function(){

    var enable = true;

    if (this.max_items > 0){
        var item_cnt = this.count_items();

        if (this.max_items <= (item_cnt + this.insert_in_proggress)){
            enable = false;
        }
    }

    this.enable_add_link(enable);
}

/**
 *  Disable all del links when number of items reached the min.
 */
Growable_Forms_ctl.prototype.check_min_items = function(){

    var enable = true;

    if (this.min_items > 0){
        var item_cnt = this.count_items();

        if (item_cnt <= this.min_items){
            enable = false;
        }
    }

    this.enable_del_links(enable);
}

/**
 *  Enable/disable 'add' link
 *
 *  @param  bool                en      enable/disable
 */
Growable_Forms_ctl.prototype.enable_add_link = function(en){

    if (typeof(this.on_add_enable) == "function"){
        // if callback "on add enable" is set, call it.
        this.on_add_enable(this, en);
    }

    if (null == this.html_add_link_id)  return; //no add link specified

    var spanEl = document.getElementById(this.html_add_link_id);
    if (null == spanEl) return; //no link found
    enable_link(en, spanEl);
}

/**
 *  Enable/disable all 'delete' links
 *
 *  @param  bool                en      enable/disable
 */
Growable_Forms_ctl.prototype.enable_del_links = function(en){

    if (null != this.table_handler){
        var rows = this.table_handler.get_rows();

        for(var i=0; i<rows.length; i++){
            this.enable_del_link(en, rows[i]);
        }
    }
}

/**
 *  Enable/disable 'delete' link
 *
 *  @param  bool                en      enable/disable
 *  @param  HTMLTableRowElement row     table row containing the 'delete' link
 */
Growable_Forms_ctl.prototype.enable_del_link = function(en, row){

    if (null == this.html_del_link_class)   return; //no add link specified

    var spanEl = get_element_by_className(row, 'span', this.html_del_link_class);
    if (null == spanEl) return; //no link found
    enable_link(en, spanEl);
}

/**
 *  Enable/disable 'up' link
 *
 *  @param  bool                en      enable/disable
 *  @param  HTMLTableRowElement row     table row containing the 'up' link
 */
Growable_Forms_ctl.prototype.enable_up_link = function(en, row){

    if (null == this.html_up_link_class)   return; //no add link specified

    var spanEl = get_element_by_className(row, 'span', this.html_up_link_class);
    if (null == spanEl) return; //no link found
    enable_link(en, spanEl);
}

/**
 *  Enable/disable 'down' link
 *
 *  @param  bool                en      enable/disable
 *  @param  HTMLTableRowElement row     table row containing the 'down' link
 */
Growable_Forms_ctl.prototype.enable_down_link = function(en, row){

    if (null == this.html_down_link_class)   return; //no add link specified

    var spanEl = get_element_by_className(row, 'span', this.html_down_link_class);
    if (null == spanEl) return; //no link found
    enable_link(en, spanEl);
}


/**
 *  Set ordering value of item from given html row element
 *
 *  @param  int                 val     value
 *  @param  HTMLTableRowElement row     table row
 */
Growable_Forms_ctl.prototype.set_ordering = function(val, row){

    if (null == this.ordering_form_el)   return; //no ordering element specified
    var row_id = this.table_handler.get_row_id(row);
    this.form[this.ordering_form_el+row_id].value = val;
}

/**
 *  Get ordering value of item from given html row element
 *
 *  @param  HTMLTableRowElement row     table row
 */
Growable_Forms_ctl.prototype.get_ordering = function(row){

    if (null == this.ordering_form_el)   return; //no ordering element specified
    var row_id = this.table_handler.get_row_id(row);
    return this.form[this.ordering_form_el+row_id].value;
}

/**
 *  Get highest ordering value used
 */
Growable_Forms_ctl.prototype.get_max_order = function(){
    var max_order = 0;
    if (null != this.table_handler){
        var rows = this.table_handler.get_rows();

        for (var i=0; i < rows.length; i++){
            var ord = this.get_ordering(rows[i]);
            if (ord > max_order) max_order = ord;
        }
    }
    return max_order;
}

/**
 *  Add new item
 */
Growable_Forms_ctl.prototype.add_item = function(){

    var first_row = 0;
    var item_cnt = this.count_items();

    // get the first_row flag
    if ((item_cnt == 0) && (this.insert_in_proggress == 0))    first_row = 1;

    this.insert_in_proggress++;

    // disable add link when max number of items have been reached
    if (this.max_items > 0){
        if (this.max_items <= (item_cnt + this.insert_in_proggress)){
            this.enable_add_link(false);
        }
    }

    var new_id = this.get_new_item_id();

    var url = this.add_item_url+
                "&"+this.add_item_url_id_param+"="+new_id+
                "&"+this.add_item_url_fr_param+"="+first_row;

    // if callback for url modification is set, call it. It could
    // e.g. add some new parameters to URL
    if (typeof(this.on_add_mod_url) == "function"){
        url = this.on_add_mod_url(this, url);
    }

    var http_request = ajax_async_request(url, null, this.add_item_callback.bindObj(this));
};

/**
 *      Add new item - callback
 */
Growable_Forms_ctl.prototype.add_item_callback = function(http_request){
    if (http_request.readyState == 4) {

        var response = JSON.parse(http_request.responseText);

        // if table containing items does not exists, create it
        this.table_handler.create_table();

        var parent_el = this.table_handler.get_tmp_row_parent();

        // create temporary parent element
        var el = document.createElement("span");
        // create table inside the parent element
        el.innerHTML = "<"+parent_el+">"+response.tableRows+"</"+parent_el+">";
        // 'el' contain directly the table element
        el = el.firstChild;

        // create temporary parrent element for form fields
        var form_els = document.createElement("form");
        // paste the elements from response there
        form_els.innerHTML = response.formElements;


        // copy form elements to main form
        for (var i=0; i<form_els.elements.length; i++){
            this.form.appendChild(form_els.elements[i].cloneNode(true));
        }

        var h_rows = this.table_handler.get_tbl_head_rows(el);
        if (h_rows.length > 0){
            this.table_handler.add_head_rows(h_rows);
        }

        var new_items = this.form[this.new_items_form_el];
        var row_ids = new Array();
        var b_rows = this.table_handler.get_tbl_rows(el);

        // copy tBodies to html container table
        for (var i=0; i<b_rows.length; i++){
            var row_id = this.table_handler.get_row_id(b_rows[i]);

            // add item ID to the new_items element
            if (new_items.value != "")      new_items.value += ";";
            new_items.value += row_id;
            row_ids.push(row_id);

            var new_row_pos = null;
            if (typeof(this.on_add_get_pos) == "function"){
                // if callback for get row position is set, call it.
                new_row_pos = this.on_add_get_pos(this, b_rows[i], form_els);
            }

            var el_values = this.save_form_values(b_rows[i]); //workaround for IE bug which do not preserve values of some elements
            var row = b_rows[i].cloneNode(true);
            this.table_handler.add_row(row, new_row_pos);
            this.restore_form_values(row, el_values); //workaround for IE bug which do not preserve values of some elements
//            this.restore_form_values(this.html_table_el, el_values); //workaround for IE bug which do not preserve values of some elements

            this.controls_register_events(row);
        }


        // update ordering values of items
        if (this.ordering_form_el != null){
            var order = 0;
            var rows = this.table_handler.get_rows();

            for(var i=0; i<rows.length; i++){
                var row_id = this.table_handler.get_row_id(rows[i]);
                this.form[this.ordering_form_el+row_id].value = order++;
            }
        }
//        if (this.ordering_form_el != null){
//            var max_order = this.get_max_order();
//
//            for(var i=0; i<row_ids.length; i++)
//                this.form[this.ordering_form_el+row_ids[i]].value = ++max_order;
//        }

        this.insert_in_proggress--;

        /* enable del links if more than minimum items is created */
        this.check_min_items();

        // update state of UP/DOWN links
        this.controls_update();

        if (typeof(this.on_add) == "function"){
            // if callback "on add" is set, call it.
            this.on_add(this, row_ids, response);
        }
    }
};

/**
 *  Delete item
 */
Growable_Forms_ctl.prototype.del_item = function(id){

    var del_items = this.form[this.del_items_form_el];

    var rows = this.table_handler.get_rows();

    // search for the tBody to delete
    for (var i=0; i < rows.length; i++){
        if (id == this.table_handler.get_row_id(rows[i])) {
            this.table_handler.del_row(rows[i]);
            break;
        }
    }

    // remove ordering form elements
    if (this.ordering_form_el != null){
        var ordering_el = this.form[this.ordering_form_el+id];
        ordering_el.parentNode.removeChild(ordering_el);
    }

    var item_cnt = this.count_items();

    /* enable add link when max number of items have not been reached */
    if (this.max_items > 0){
        if (this.max_items > (item_cnt + this.insert_in_proggress)){
            this.enable_add_link(true);
        }
    }

    /* disable del link when less than mimimum items is configured */
    this.check_min_items();

    // add item ID to the del_items element
    if (del_items.value != "")      del_items.value += ";";
    del_items.value += id;

    // remove table heading if there are no rows
    if (item_cnt == 0) this.table_handler.del_head();

    // update state of UP/DOWN links
    this.controls_update();

    if (typeof(this.on_del) == "function"){
        // if callback "on del" is set, call it.
        this.on_del(this, id);
    }
}


/**
 *  Rise priority of item
 */
Growable_Forms_ctl.prototype.item_up = function(id){

    if (typeof(this.on_pre_up) == "function"){
        // if callback "on pre up" is set, call it.
        if (!this.on_pre_up(this, id)) return;
    }

    var rowIndex = null;
    var rows = this.table_handler.get_rows();

    // search for the tBody to be rised up
    for (var i=0; i<rows.length; i++){
        if (id == this.table_handler.get_row_id(rows[i])) {
            rowIndex = i;
            break;
        }
    }

    //row not found
    if (rowIndex == null) return;
    //first row - can not move up
    if (rowIndex == 0) return;


    var prevRowIndex = rowIndex - 1;
    var prevRow = rows[prevRowIndex];
    var prevRowId = this.table_handler.get_row_id(prevRow);
    var row = rows[rowIndex];

    // workaround for a bug in IE6 - save current state of form elements
    var el_values = this.save_form_values(row);

    // remove table row
    var tmpRow = row.parentNode.removeChild(row);
    // and insert it back, before the previous Row
    prevRow.parentNode.insertBefore(tmpRow, prevRow);

    // workaround for a bug in IE6 - restore state of form elements
    this.restore_form_values(tmpRow, el_values);


    // get form elements holding the priority
    var cur_order_el  = this.form[this.ordering_form_el+id];
    var prev_order_el = this.form[this.ordering_form_el+prevRowId];

    // switch values of ordering elements
    var tmp = cur_order_el.value;
    cur_order_el.value = prev_order_el.value;
    prev_order_el.value = tmp;

    // update state of UP/DOWN links
    this.controls_update();

    if (typeof(this.on_up) == "function"){
        // if callback "on up" is set, call it.
        this.on_up(this, id);
    }
}


/**
 *  Lower priority of item
 */
Growable_Forms_ctl.prototype.item_down = function(id){

    if (typeof(this.on_pre_down) == "function"){
        // if callback "on pre down" is set, call it.
        if (!this.on_pre_down(this, id)) return;
    }

    var rowIndex = null;
    var rows = this.table_handler.get_rows();

    // search for the tBody to be lowered down
    for (var i=0; i<rows.length; i++){
        if (id == this.table_handler.get_row_id(rows[i])) {
            rowIndex = i;
            break;
        }
    }

    //row not found
    if (rowIndex == null) return;
    //last row - can not move down
    if (rowIndex >= (rows.length - 1)) return;


    var postRowIndex = rowIndex + 1;
    var postRow = rows[postRowIndex];
    var postRowId = this.table_handler.get_row_id(postRow);
    var row = rows[rowIndex];

    // workaround for a bug in IE - save current state of form elements
    var el_values = this.save_form_values(postRow);

    // remove table row
    var tmpPostRow = postRow.parentNode.removeChild(postRow);
    // and insert it back, before the previous Row
    row.parentNode.insertBefore(tmpPostRow, row);

    // workaround for a bug in IE - restore state of form elements
    this.restore_form_values(tmpPostRow, el_values);

    // get form elements holding the priority
    var cur_order_el  = this.form[this.ordering_form_el+id];
    var post_order_el = this.form[this.ordering_form_el+postRowId];

    // switch values of ordering elements
    var tmp = cur_order_el.value;
    cur_order_el.value = post_order_el.value;
    post_order_el.value = tmp;

    // update state of UP/DOWN links
    this.controls_update();

    if (typeof(this.on_down) == "function"){
        // if callback "on down" is set, call it.
        this.on_down(this, id);
    }
}

/**
 *  Get array of elements for the save_form_values() and restore_form_values() functions
 *
 *  @private
 */
Growable_Forms_ctl.prototype.s_r_form_elements = function(row){
    var out = new Array();
    var els;

    // copy all inputs to output array
    els = row.getElementsByTagName('input');
    for (var i=0; i<els.length; i++){
        out.push(els[i]);
    }

    // copy all selects to output array
    els = row.getElementsByTagName('select');
    for (var i=0; i<els.length; i++){
        out.push(els[i]);
    }

    return out;
}

/**
 *  Return object containing values of form elements that are childs of 'row'
 *
 *  @return Object
 */
Growable_Forms_ctl.prototype.save_form_values = function(row){

    // get array of elements
    var els = this.s_r_form_elements(row);
    var values = new Object();

    for (var i=0; i<els.length; i++){
        switch (els[i].type){
        case "checkbox":
        case "radio":
            values[els[i].id] = els[i].checked;
            break;

        case "select-one":
            values[els[i].id] = els[i].selectedIndex;
            break;

        case "select-multiple":
            break;      // @todo later

        default:
            values[els[i].id] = els[i].value;
        }
    }

    return values;
}

/**
 *  Restore values of form elements that are childs of 'row' from values object
 */
Growable_Forms_ctl.prototype.restore_form_values = function(row, values){

    // get array of elements
    var els = this.s_r_form_elements(row);

    for (var i=0; i<els.length; i++){

        // if value of this element is not part of values skip it
        if (typeof(values[els[i].id]) == "undefined") continue;

        switch (els[i].type){
        case "checkbox":
        case "radio":
            els[i].checked = values[els[i].id];
            break;

        case "select-one":
            els[i].selectedIndex = values[els[i].id];
            break;

        case "select-multiple":
            break;      // @todo later

        default:
            els[i].value = values[els[i].id];
        }
    }
}
