
dojo.declare("OfflajnText", null, {
	constructor: function(args) {
    dojo.mixin(this,args);
    this.init();
  },
  
  
  init: function() {
    this.hidden = dojo.byId(this.id);
    dojo.connect(this.hidden, 'change', this, 'reset');
    
    this.input = dojo.byId(this.id+'input');
    this.switcher = dojo.byId(this.id+'unit');
  
    
    if(this.validation == 'int'){
      dojo.connect(this.input, 'keyup', this, 'validateInt');
      this.validateInt();
    }else if(this.validation == 'float'){
      dojo.connect(this.input, 'keyup', this, 'validateFloat');
      this.validateFloat();
    }
    dojo.connect(this.input, 'onblur', this, 'change');
    if(this.switcher){
      dojo.connect(this.switcher, 'change', this, 'change');
    }else{
      if(this.attachunit != '')
        this.switcher = {'value': this.attachunit, 'noelement':true};
      
    }
    this.container = dojo.byId('offlajntextcontainer' + this.id);
    if(this.mode == 'increment') {
      this.arrows = dojo.query('.arrow', this.container);
      dojo.connect(this.arrows[0], 'onmousedown', dojo.hitch(this, 'mouseDown', 1));
      dojo.connect(this.arrows[1], 'onmousedown', dojo.hitch(this, 'mouseDown', -1));
    }
    dojo.connect(this.input, 'onfocus', this, dojo.hitch(this, 'setFocus', 1));
    dojo.connect(this.input, 'onblur', this, dojo.hitch(this, 'setFocus', 0));
  },
  
  reset: function(e){
    if(this.hidden.value != this.input.value+(this.switcher? '||'+this.switcher.value : '')){
      var v = this.hidden.value.split('||');
      this.input.value = v[0];
      if(this.switcher && this.switcher.noelement != true){
        this.switcher.value = v[1];
        this.fireEvent(this.switcher, 'change');
      }
      if(e) dojo.stopEvent(e);
      this.fireEvent(this.input, 'change');
    }
  },
  
  change: function(){
    this.hidden.value = this.input.value+(this.switcher? '||'+this.switcher.value : '');
    this.fireEvent(this.hidden, 'change');
    if(this.onoff) this.hider();
  },
  
  setFocus: function(mode) {
    if(mode){
      dojo.addClass(this.input.parentNode, 'focus');
    } else {
      dojo.removeClass(this.input.parentNode, 'focus');
    }
  },
  
  hider: function() {
    if(!this.hiderdiv) {
      this.hiderdiv = dojo.create('div', {'class': 'offlajntext_hider'}, this.container);
      dojo.style(this.hiderdiv, 'width', dojo.position(this.container).w + 'px');
    }
    if(parseInt(this.switcher.value)) {
      dojo.style(this.container, 'opacity', '1');
      dojo.style(this.hiderdiv, 'display', 'none');
    } else {
      dojo.style(this.container, 'opacity', '0.5');
      dojo.style(this.hiderdiv, 'display', 'block');
    }
  },
  
  validateInt: function(){
    var val = parseInt(this.input.value, 10);
    if(!val) val = 0;
    this.input.value = val;
  },
  
  validateFloat: function(){
    var val = parseFloat(this.input.value);
    if(!val) val = 0;
    this.input.value = val;
  },
  
  mouseDown: function(m){
    dojo.connect(document, 'onmouseup', this, 'mouseUp');
    var f = dojo.hitch(this, 'modifyValue', m);
    f();
    this.interval = setInterval(f, 200);
  },
  
  mouseUp: function(){
    clearInterval(this.interval);
  },

  modifyValue: function(m) {
    var val = 0;
    if(this.validation == 'int') {
      val = parseInt(this.input.value);
    } else if(this.validation == 'float') {
      val = parseFloat(this.input.value);
    }
    val = val + m*this.scale;
    if(val < 0 && this.minus == 0) val = 0; 
    this.input.value = val;
    this.change();
    this.fireEvent(this.input, 'change');
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