
dojo.declare("OfflajnRadio", null, {
	constructor: function(args) {
	 dojo.mixin(this,args);
   this.selected = -1;
	 this.init();
  },
  
  init: function() {
    this.hidden = dojo.byId(this.id);
    this.hidden.radioobj = this;
    dojo.connect(this.hidden, 'change', this, 'reset');
    this.container = dojo.byId('offlajnradiocontainer' + this.id);
    this.items = dojo.query('.radioelement', this.container);
    if(this.mode == "image") this.imgitems = dojo.query('.radioelement_img', this.container);
    dojo.forEach(this.items, function(item, i){
      if(this.hidden.value == this.values[i]) this.selected = i;
      dojo.connect(item, 'onclick', dojo.hitch(this, 'selectItem', i));
    }, this);
    
    this.reset();
  },
  
  reset: function(){
    var i = this.map[this.hidden.value];
    if(!i) i = 0;
    this.selectItem(i);
  },
  
  selectItem: function(i) {
    if(this.selected == i) {
      if(this.mode == "image") this.changeImage(i);
     return;
    }
    if(this.selected >= 0) dojo.removeClass(this.items[this.selected], 'selected');
    if(this.mode == "image") this.changeImage(i);
    this.selected = i;
    dojo.addClass(this.items[this.selected], 'selected');
    if(this.hidden.value != this.values[this.selected]){
      this.hidden.value = this.values[this.selected];
      this.fireEvent(this.hidden, 'change');
    }
  },
  
  changeImage: function(i) {
    dojo.style(this.imgitems[this.selected], 'backgroundPosition', '0px 0px');
    dojo.style(this.imgitems[i], 'backgroundPosition', '0px -8px');
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
