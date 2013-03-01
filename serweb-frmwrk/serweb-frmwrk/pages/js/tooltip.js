/**
 *  Class displaying customized tooltips. To enable it just link this file to
 *  HTML document.  
 */ 
function Tooltip(varname){

    this.varname = varname;

    /** offset of mouse cursor where the tooltip is displayed */
    this.ox=10;
    this.oy=10;

    /** Last position of mouse cursor. It is used to determine whether mouse 
     *  moved till last invoke of event handler. 
     */
    this.lastXPos = 0;
    this.lastYPos = 0;

    /** timer */
    this.timer = null;
    /** determine whether tooltip is already displayed - timer expired */
    this.displayed=false;
    /** mouse position */
    this.mouseX = null;
    this.mouseY = null;
    /** element which invoked the event handler last time */
    this.LSE = null;

    /** indicate that customized tooltips are active */
    this.active = true;
    
    /** container of tooltip */
    this.oDv=document.createElement("div");
    this.oFrm=document.createElement("iframe");

    /** Actualy displayed tooltip message. It is used as param for function  
     *  showToolTip() which is invoked via timer.
     */
    this.tooltip;
}


/**
 *  init function
 */ 
Tooltip.prototype.init = function(){
    this.oDv.style.position="absolute";
    this.oDv.style.visibility='hidden';
    this.oDv.style.zIndex='999';

    /* iframe is used by IE6 Hack to enable tooltips over select elements */
    this.oFrm.style.border='0px none';
    this.oFrm.style.display='none';
    this.oFrm.style.position='absolute';
    this.oFrm.style.zIndex='998';
    this.oFrm.scrolling='no';

    document.body.appendChild(this.oDv);    
    document.body.appendChild(this.oFrm);   
};

/**
 *  Deactivate customized tooltips
 */
Tooltip.prototype.deactivate = function(){
    this.active = false;
}

/**
 *  Activate customized tooltips
 */
Tooltip.prototype.activate = function(){
    this.active = true;
}

/** Set styles for the tooltip */
Tooltip.prototype.defTooltipStyle = function(){
    this.oDv.style.border='1px solid #333333';
    this.oDv.style.maxWidth='300px';

    this.oDv.style.fontFamily='arial';
    this.oDv.style.fontSize='11';
    this.oDv.style.padding='2px';
    this.oDv.style.color='#000000';
    this.oDv.style.background='#fffedf';
//  this.oDv.style.filter='alpha(opacity=85)'; // IE
//  this.oDv.style.opacity='0.85'; // FF

    // maxWidth for the IE
    if (this.oDv.clientWidth > 300) this.oDv.style.width = '300px';
}

/** Apply styles to tooltip */
Tooltip.prototype.applyStyles = function(content){
    this.oDv.style.width='';
    this.oDv.innerHTML=content;
    this.defTooltipStyle();
}

/** Display the tooltip */
Tooltip.prototype.showToolTip = function(){

    this.applyStyles(this.tooltip);

    this.oDv.style.left = (this.mouseX+this.ox)+"px";
    this.oDv.style.top  = (this.mouseY+this.oy)+"px";       

    /* IE6 Hack to enable tooltips over select elements */
    this.oFrm.style.left =      this.oDv.style.left;
    this.oFrm.style.top =       this.oDv.style.top;
    this.oFrm.style.height =    this.oDv.offsetHeight;
    this.oFrm.style.width =     this.oDv.offsetWidth;
    this.oFrm.style.display =   'block';

    this.oDv.style.visibility = 'visible';

    this.displayed=true;
    this.timer=null;
}

/** Hide the tooltip */
Tooltip.prototype.hideToolTip = function(){
    this.oDv.innerHTML = '';
    this.oDv.style.visibility = 'hidden';
    this.oFrm.style.display =   'none';
    this.displayed=false;
    if (this.timer != null)  clearTimeout(this.timer);
}

/**
 *  Determine whether mouse position has been changed. This function is needed
 *  because of bug in IE. OnMouseMove is periodicaly invoked on select elemens
 *  even if the mouse did not moved.  
 */
Tooltip.prototype.mousePosChanged = function(e){
    var moved;
    var xPos = e.pageX ? e.pageX : e.clientX;
    var yPos = e.pageY ? e.pageY : e.clientY;

    if (xPos != this.lastXPos || yPos != this.lastYPos) moved=true;
    else moved=false;

    this.lastXPos = xPos;
    this.lastYPos = yPos;

    return moved;
}

/**
 *  OnMOuseMove event handler
 */
Tooltip.prototype.onMouseMove = function(e){
    var evt;
    var CSE;
    var bodyScrollTop, bodyScrollLet;

    // do nothing if customized tooltips are not active 
    if (!this.active) return;
    
    if (e)  evt=e;
    else    evt=window.event;
    
    CSE = evt.target ? evt.target : evt.srcElement;

    // On mouse move over disabled element in IE the CSE is empty object with
    // no methods and no attributes. Handle this case and exit, so IE do not
    // generate errors. 
    if (!CSE.setAttribute) return;

    if (CSE.title != ''){
        // move the title to another attribute. In oposite case Opera display 
        // the tooltip twice 
        CSE.setAttribute('data-tooltip', CSE.title);
        CSE.title = '';
    }

    var tooltip = CSE.getAttribute('data-tooltip');
    
    if (tooltip != null){
        /* this.mousePosChanged() function detect whether mouse position has 
           been changed sice last exec of this method. It is because of IE6 
           bug. It sometimes raises onmousemove event even if mouse did not 
           moved*/
        if (!this.displayed && this.mousePosChanged(evt)){

            if (this.timer != null)  clearTimeout(this.timer);
            this.tooltip = tooltip;

            this.timer = setTimeout(this.varname+".showToolTip()", 500);
    
            // This added to alleviate bug in IE6 w.r.t DOCTYPE
            bodyScrollTop = document.documentElement && document.documentElement.scrollTop ? 
                            document.documentElement.scrollTop : document.body.scrollTop;
            bodyScrollLet = document.documentElement && document.documentElement.scrollLeft ?
                            document.documentElement.scrollLeft : document.body.scrollLeft;
    
            this.mouseX = evt.pageX ? evt.pageX-bodyScrollLet : evt.clientX-document.body.clientLeft;
            this.mouseY = evt.pageY ? evt.pageY-bodyScrollTop : evt.clientY-document.body.clientTop;
            
            this.mouseX += bodyScrollLet;
            this.mouseY += bodyScrollTop;
        }
    }

    if (this.LSE != CSE){
        if (this.timer != null)    clearTimeout(this.timer);
        if (this.displayed)     this.hideToolTip();
    }

    this.LSE = CSE;
}




/**
 *  Create instance of Tooltip class
 */
var tooltip_obj = new Tooltip('tooltip_obj');
function Tooltip_init(e){ tooltip_obj.init(e); }
function Tooltip_mouseMove(e){ tooltip_obj.onMouseMove(e); }


/* set the event handlers */
if (typeof(document.attachEvent) != 'undefined'){
   window.attachEvent('onload', Tooltip_init);
   document.attachEvent('onmousemove', Tooltip_mouseMove);
}
else {
   window.addEventListener('load', Tooltip_init, false);
   document.addEventListener('mousemove', Tooltip_mouseMove, false);
}
