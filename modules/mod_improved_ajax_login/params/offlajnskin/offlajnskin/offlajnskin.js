dojo.declare("OfflajnSkin", null, {
	constructor: function(args) {
    dojo.mixin(this,args);
    this.init();
    if(this.hidden.changeSkin){
      this.hidden.changeSkin();
      this.hidden.changeSkin = null;
    }
    if(window[this.name+'delay'] == true){
      window[this.name+'delay'] == false;
      this.hidden.value = this.hidden.options[1].value;
      this.changeSkin();
    }
  },
  
  init: function() {
    this.hidden = dojo.byId(this.id);
    //this.span = dojo.create("span", {style: "margin-left: 10px; position: absolute;"}, this.hidden.parentNode.parentNode, "last");
    this.span = dojo.create("span", {style: "margin-left: 10px;"}, this.hidden.parentNode.parentNode, "last");
    this.c = dojo.connect(this.hidden, 'onchange', this, 'changeSkin');  
  },
  
  changeSkin: function() {
    if(this.hidden.value != 'custom'){
      this.changeSkinNext();
      this.hidden.value = 'custom';
      this.fireEvent(this.hidden, 'change');
    }
  },
  
  changeSkinNext: function() {
    var value = this.hidden.value;
    var def = this.data[value];
    for (var k in def) {
      var p = dojo.byId(this.control + k);
      
      if(!p) {
        var n = this.id.replace(this.name, '');
        p = dojo.byId(n + k);        
      }
      if(p) {
        var v = def[k];
        if(v.indexOf("**") >= 0){
            var newv = v.split('|*|');
            var oldv = p.value.split('|*|');
            for(var i = 0; i < oldv.length; i++){
                if(newv[i] != '**'){
                    oldv[i] = newv[i];
                }
            }
            v = oldv.join('|*|');
        }else if(v.length > 0 && v.indexOf("{") == 0){
          var orig = {};
          if(p.value.length > 0 && p.value.indexOf("{") == 0){
            orig = dojo.fromJson(p.value);
          }
          var newValues = dojo.fromJson(v);
          for(var key in newValues){
            if(!orig[key]) orig[key] = {};
            for(var key2 in newValues[key]){
              orig[key][key2] = newValues[key][key2];
            }
          }
          v = dojo.toJson(orig);
        }
        p.value = v;
        this.fireEvent(p, 'change');
      }
    } 
    this.span.innerHTML = "The <b>"+value.replace(/_/g," ").replace("default2","default")+" skin</b> parameters has been set.";
        
    if(this.dependency){
      window[this.dependency+'delay'] = true;
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