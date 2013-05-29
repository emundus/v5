
dojo.declare("OfflajnGradient", null, {
	constructor: function(args) {
    dojo.mixin(this,args);
    this.init();
  },
  
  init: function() {
    this.start.alphaSupport=false; 
    this.startc = jQuery(this.start).jPicker({
      window:{
        expandable: true,
        alphaSupport: false
      }
    });
    
    this.end.alphaSupport=false; 
    this.endc = jQuery(this.end).jPicker({
      window:{
        expandable: true,
        alphaSupport: false
      }
    });
    
    if(dojo.isIE){
      dojo.style(this.start.parentNode.parentNode, 'zoom', '1');
    }
    //this.changeGradient();
    
    this.container = this.start.parentNode.parentNode.parentNode;
    if (!this.onoff) {
      this.container.style.marginLeft = 0;
      dojo.byId("offlajnonoff"+this.switcher.id).style.display = 'none';
    }
    
    this.hider = dojo.create("div", { "class": "gradient_hider" }, this.container, "last");
    
    dojo.style(this.hider, 'position', 'absolute');
    dojo.style(this.hider, "display", "none");
    
    if(!parseInt(this.switcher.value)){
      dojo.style(this.container, 'opacity', 0.15);
      dojo.style(this.hider, "display", "block");
    }
    this.changeValues();
  
    dojo.connect(this.switcher, 'onchange', this, 'onSwitch');
    dojo.connect(this.start, 'onchange', this, 'changeGradient');
    dojo.connect(this.end, 'onchange', this, 'changeGradient');
    dojo.connect(this.hidden, 'onchange', this, 'changeValues');
    this.onResize();
    dojo.connect(window, 'onresize', this, 'onResize');
  },
     
  onResize: function(){ 
    var j15 = 0;
    if(this.container.parentNode.tagName == 'TD') j15 = 1;
    var w = dojo.coords(j15 ? this.container.parentNode.parentNode:this.container.parentNode).w-30;
    var c = this.container.parentNode.children;
    for(var i = 0; i < c.length-1 && c[i] != this.container; i++){
      w-=dojo.marginBox(c[i]).w;
    }
    if(j15) w-=160;
    dojo.style(this.container, 'width', w+'px');
    dojo.style(this.hider, "width", w+"px");
  },
  
  onSwitch: function(){
    if(this.anim) this.anim.stop();
    if(parseInt(this.switcher.value)){
      this.anim = dojo.animateProperty({
        node: this.container,
        properties: {
            opacity : 1
        },
        onEnd : dojo.hitch(this,function() {
                  dojo.style(this.hider, "display", "none");
                })
      }).play();
    }else{
      this.anim = dojo.animateProperty({
        node: this.container,
        properties: {
            opacity : 0.15
        },
        onBegin : dojo.hitch(this,function() {
                  dojo.style(this.hider, "display", "block");
                })
      }).play();
    }
    this.changeGradient();
  },
  
  changeGradient: function() {
      if(dojo.isIE){
        dojo.style(this.start.parentNode.parentNode, 'filter', 'progid:DXImageTransform.Microsoft.Gradient(GradientType=1,StartColorStr=#'+this.start.value+',EndColorStr=#'+this.end.value+')');
      }else if (dojo.isFF ) {
        dojo.style(this.start.parentNode.parentNode, 'background', '-moz-linear-gradient( left, #'+this.start.value+', #'+this.end.value+')');
      } else if (dojo.isMozilla ) {
        dojo.style(this.start.parentNode.parentNode, 'background', '-moz-linear-gradient( left, #'+this.start.value+', #'+this.end.value+')');
      } else if (dojo.isOpera ) {
        dojo.style(this.start.parentNode.parentNode, 'background-image', '-o-linear-gradient(right, #'+this.start.value+', #'+this.end.value+')');
      } else {
        dojo.style(this.start.parentNode.parentNode, 'background', '-webkit-gradient( linear, left top, right top, from(#'+this.start.value+'), to(#'+this.end.value+'))');
      }
      this.hidden.value = this.switcher.value+'-'+this.start.value+'-'+this.end.value;
  },
  
  changeValues: function() {
    var val = this.hidden.value.split("-");
    //console.log(val);
    this.switcher.value = val[0];
    this.fireEvent(this.switcher, 'change');
    this.onSwitch();
    if(val[1] && val[2]) {
      this.start.value = val[1];
      this.startc[0].color.active.val('hex', val[1]);
      //this.fireEvent(this.start, 'change');
      this.end.value = val[2];
      this.endc[0].color.active.val('hex', val[2]);
      //this.fireEvent(this.end, 'change');
      this.changeGradient();
    }
  },
  
  fireEvent: function(element,event){
    if ((document.createEventObject && !dojo.isIE) || (document.createEventObject && dojo.isIE && dojo.isIE < 9)){
      var evt = document.createEventObject();
      return element.fireEvent('on'+event,evt);
    }else{
      var evt = document.createEvent("HTMLEvents");
      evt.initEvent(event, true, true );
      return !element.dispatchEvent(evt);
    }
  }
});