dojo.require("dojo.window");

dojo.declare("FontConfigurator", null, {
	constructor: function(args) {  
    dojo.mixin(this,args);
    window.loadedFont = {};
    this.init();
  },
  
  
  init: function() {
    this.btn = dojo.byId(this.id+'change');
    dojo.connect(this.btn, 'onclick', this, 'showWindow');    
    this.settings = dojo.clone(this.origsettings);
    this.hidden = dojo.byId(this.id);
    dojo.connect(this.hidden, 'onchange', this, 'reset');
    this.reset();
  },
  
  reset: function(){  
    if(this.hidden.value == '') this.hidden.value = dojo.toJson(this.settings);        
    if(this.hidden.value != dojo.toJson(this.settings)){
      var newsettings = {};
      try{            
        newsettings = dojo.fromJson(this.hidden.value.replace(/\\"/g, '"'));
        if(dojo.isArray(newsettings)){
          newsettings = {};
        }                
      }catch(e){
        this.hidden.value = dojo.toJson(newsettings);
      }      
      for(var s in this.origsettings){
        if(!newsettings[s]){
          newsettings[s] = this.origsettings[s];
        }
      } 
      this.settings = this.origsettings = newsettings;
    }
  },
  
  showOverlay: function(){
    if(!this.overlayBG){
      this.overlayBG = dojo.create('div',{'class': 'blackBg'}, dojo.body());
    }
    dojo.removeClass(this.overlayBG, 'hide');
    dojo.style(this.overlayBG,{
      'opacity': 0.3
    });
  },
  
  showWindow: function(e){
    dojo.stopEvent(e);
    this.showOverlay();
    if(!this.window){
      this.window = dojo.create('div', {'class': 'OfflajnWindowFont'}, dojo.body());
      var closeBtn = dojo.create('div', {'class': 'OfflajnWindowClose'}, this.window);
      dojo.connect(closeBtn, 'onclick', this, 'closeWindow');
      var inner = dojo.create('div', {'class': 'OfflajnWindowInner'}, this.window);
      var h3 = dojo.create('h3', {'innerHTML': 'Font selector'+this.elements.tab['html']}, inner);
      
      this.reset = dojo.create('div', {'class': 'offlajnfont_reset hasOfflajnTip', 'tooltippos': 'T','title' : 'It will clear the settings on the current tab.', 'innerHTML': '<div class="offlajnfont_reset_img"></div>'}, h3);
      dojo.global.toolTips.connectToolTips(h3);
      dojo.connect(this.reset, 'onclick', this, 'resetValues');
      
      this.tab = dojo.byId(this.id+'tab');
      
      dojo.connect(this.tab, 'change', this, 'changeTab');

      dojo.create('div', {'class': 'OfflajnWindowLine'}, inner);
      var fields = dojo.create('div', {'class': 'OfflajnWindowFields'}, inner);
      
      
      dojo.create('div', {'class': 'OfflajnWindowField', 'innerHTML': 'Type<br />'+this.elements.type['html']}, fields);
      this.type = dojo.byId(this.elements.type.id);

      this.familyc = dojo.create('div', {'class': 'OfflajnWindowField'}, fields);
      
      
      dojo.create('div', {'class': 'OfflajnWindowField', 'innerHTML': 'Size<br />'+this.elements.size['html']}, fields);
      this.size = dojo.byId(this.elements.size['id']);
      
      dojo.create('div', {'class': 'OfflajnWindowField', 'innerHTML': 'Color<br />'+this.elements.color['html']}, fields);
      this.color = dojo.byId(this.elements.color['id']);
      
      dojo.create('div', {'class': 'OfflajnWindowField', 'innerHTML': 'Decoration<br />'+this.elements.bold['html']+this.elements.italic['html']+this.elements.underline['html']}, fields);
      this.bold = dojo.byId(this.elements.bold['id']);
      this.italic = dojo.byId(this.elements.italic['id']);
      this.underline = dojo.byId(this.elements.underline['id']);
      
      dojo.create('div', {'class': 'OfflajnWindowField', 'innerHTML': 'Align<br />'+this.elements.align['html']}, fields);
      this.align = dojo.byId(this.elements.align['id']);
      
      dojo.create('div', {'class': 'OfflajnWindowField', 'innerHTML': 'Alternative font<br />'+this.elements.afont['html']}, fields);
      this.afont = dojo.byId(this.elements.afont['id']);
      
      dojo.create('div', {'class': 'OfflajnWindowField', 'innerHTML': 'Text shadow<br />'+this.elements.tshadow['html']}, fields);
      this.tshadow = dojo.byId(this.elements.tshadow['id']);
      
      dojo.create('div', {'class': 'OfflajnWindowField', 'innerHTML': 'Line height<br />'+this.elements.lineheight['html']}, fields);
      this.lineheight = dojo.byId(this.elements.lineheight['id']);
      
      dojo.create('div', {'class': 'OfflajnWindowTester', 'innerHTML': '<span>Grumpy wizards make toxic brew for the evil Queen and Jack.</span>'}, inner);
      this.tester = dojo.query('.OfflajnWindowTester span', inner)[0];
      var saveCont = dojo.create('div', {'class': 'OfflajnWindowSaveContainer'}, inner);
      var savebtn = dojo.create('div', {'class': 'OfflajnWindowSave', 'innerHTML': 'SAVE'}, saveCont);
      dojo.connect(savebtn, 'onclick', this, 'save');
      eval(this.script);
      
      
      dojo.connect(this.type, 'change', this, 'changeType');
      dojo.connect(this.size, 'change', dojo.hitch(this, 'changeSet', 'size'));
      dojo.connect(this.size, 'change', this, 'changeSize');
      dojo.connect(this.color, 'change', dojo.hitch(this, 'changeSet', 'color'));
      dojo.connect(this.color, 'change', this, 'changeColor');
      dojo.connect(this.bold, 'change', dojo.hitch(this, 'changeSet', 'bold'));
      dojo.connect(this.bold, 'change', this, 'changeWeight');
      dojo.connect(this.italic, 'change', dojo.hitch(this, 'changeSet', 'italic'));
      dojo.connect(this.italic, 'change', this, 'changeItalic');
      dojo.connect(this.underline, 'change', dojo.hitch(this, 'changeSet', 'underline'));
      dojo.connect(this.underline, 'change', this, 'changeUnderline');
      dojo.connect(this.afont, 'change', dojo.hitch(this, 'changeSet', 'afont'));
      dojo.connect(this.afont, 'change', this, 'changeFamily');
      dojo.connect(this.align, 'change', dojo.hitch(this, 'changeSet', 'align'));
      dojo.connect(this.align, 'change', this, 'changeAlign');
      dojo.connect(this.tshadow, 'change', dojo.hitch(this, 'changeSet', 'tshadow'));
      dojo.connect(this.tshadow, 'change', this, 'changeTshadow');
      dojo.connect(this.lineheight, 'change', dojo.hitch(this, 'changeSet', 'lineheight'));
      dojo.connect(this.lineheight, 'change', this, 'changeLineheight');
      
      dojo.addOnLoad(this, function(){
        this.changeTab();
        this.changeType();
      });
    }else{
      this.settings = dojo.fromJson(this.hidden.value.replace(/\\"/g, '"'));
      this.loadSettings();
    }
    dojo.removeClass(this.window, 'hide');
    this.exit = dojo.connect(document, "onkeypress", this, "keyPressed");
  },
  
  closeWindow: function(){
    dojo.addClass(this.window, 'hide');
    dojo.addClass(this.overlayBG, 'hide');
  },
  
  save: function(){  
    this.hidden.value = dojo.toJson(this.settings);
    this.closeWindow();
  },
  
  loadSettings: function(){
    if(this.defaultTab!=this.t){
      this._loadSettings(this.defaultTab, true);
    }
    this._loadSettings(this.t, false);
    this.refreshFont();
  },
  
  _loadSettings: function(tab, def){
    var set = this.settings[tab];
    for(s in set){
      if(this[s] && (!def || def && !this.settings[this.t][s])){
        this.changeHidden(this[s], set[s]);
      }
    }
  },
  
  resetValues: function() {
    if(this.t != this.defaultTab) {
      this.settings[this.t] = {};
      this.loadSettings();
    }
  },
  
  loadFamily: function(e){
    dojo.stopEvent(e);
    var list = this.family.listobj;
    
    this.maxIteminWindow = parseInt(list.scrollbar.windowHeight/list.lineHeight)+1;
    this.loadFamilyScroll();
    list.scrollbar.onScroll = dojo.hitch(this, 'loadFamilyScroll');
  },
  
  loadFamilyScroll: function(){
    var set = this.settings[this.t];
    var list = this.family.listobj;
    var start = parseInt(list.scrollbar.curr*-1/list.lineHeight);
    for(var i = start; i <= start+this.maxIteminWindow && i < list.list.length; i++){
      var item = list.list[i];
      var option = list.options[i].value;
      this.loadGoogleFont(set.subset, option);
      dojo.style(item, 'fontFamily', "'"+option+"'");
    }
  },
  
  loadGoogleFont: function(subset, family, weight, italic){
    if(!weight) weight = 400;
    italic ? italic = 'italic' : italic = '';
    var hash = subset+family+weight+italic;
    if(!window.loadedFont[hash]){
      window.loadedFont[hash] = true; 
      setTimeout(function(){
        dojo.create('link', {rel:'stylesheet', type: 'text/css', href: 'http://fonts.googleapis.com/css?family='+family+':'+weight+italic+'&subset='+subset}, dojo.body())
      },500);
    } 
  },
  
  changeType: function(e){
    if(e){
      var obj = e.target.listobj;
      if(obj.map[obj.hidden.value] != obj.hidden.selectedIndex) return;
    }
    var set = this.settings[this.t];
    set.type = this.type.value;
    if(!this.elements.type[set.type]){
      if(!this.family){
        this.familyc.innerHTML = 'Family<br />'+this.elements.type['Latin']['html'];
        this.family = dojo.byId(this.elements.type['Latin']['id']);
        eval(this.elements.type['Latin']['script']);
      }
      dojo.addOnLoad(this, function(){
        dojo.style(this.family.listobj.container,'visibility', 'hidden');
      });
      set.family = '';
      this.changeFamily();
      return;
    }
    this.familyc.innerHTML = 'Family<br />'+this.elements.type[set.type]['html'];
    this.family = dojo.byId(this.elements.type[set.type]['id']);
    
    dojo.connect(this.family, 'change', dojo.hitch(this, 'changeSet', 'family'));
    dojo.connect(this.family, 'click', this, 'loadFamily');
    dojo.connect(this.family, 'change', this, 'refreshFont');
    eval(this.elements.type[set.type]['script']);
    if(set.family){
      dojo.addOnLoad(this, function(){
        var set = this.settings[this.t];
        this.changeHidden(this.family, set.family);
      });
    }
    var subset = this.type.value;
    if(subset == 'LatinExtended'){
      subset = 'latin,latin-ext';
    }else if(subset == 'CyrillicExtended'){
      subset = 'cyrillic,cyrillic-ext';
    }else if(subset == 'GreekExtended'){
      subset = 'greek,greek-ext';
    }
    set.subset = subset;
  },
  
  changeSet: function(name, e){
    var set = this.settings[this.t];
    set[name] = e.target.value;
  },
  
  refreshFont: function(){
    var set = this.settings[this.t];
    if(this.bold) this.changeWeight();
    if(this.italic) this.changeItalic();
    if(this.underline) this.changeUnderline();
    this.changeFamily();
    if(this.size) this.changeSize();
    if(this.color) this.changeColor();
    if(this.align) this.changeAlign();
    if(this.tshadow) this.changeTshadow();
    if(this.lineheight) this.changeLineheight();
  },
  
  changeWeight: function(){
    dojo.style(this.tester, 'fontWeight', (parseInt(this.bold.value) ? 'bold' : 'normal'));
  },
  
  changeItalic: function(){
    dojo.style(this.tester, 'fontStyle', (parseInt(this.italic.value) ? 'italic' : 'normal'));
  },
  
  changeUnderline: function(){
    dojo.style(this.tester, 'textDecoration', (parseInt(this.underline.value) ? 'underline' : 'none'));
  },
  
  changeFamily: function(){
    var set = this.settings[this.t];
    var f = '';
    if(this.family && set.type != '0'){
      f = "'"+this.family.value+"'";
      this.loadGoogleFont(set.subset, this.family.value, (this.bold && parseInt(this.bold.value) ? '700' : '400'), parseInt(this.italic.value));
    }
    if(this.afont){
      var afont = this.afont.value.split('||'); 
      if(afont[0] != '' && parseInt(afont[1])){
        if(f != '') f+=',';
        f+=afont[0];
      }
    }
    dojo.style(this.tester, 'fontFamily', f);
  },
  
  changeSize: function(){
    dojo.style(this.tester, 'fontSize', this.size.value.replace('||', '') );
  },
  
  changeColor: function(){
    dojo.style(this.tester, 'color', '#'+this.color.value );
  },
  
  changeAlign: function(){
    dojo.style(this.tester.parentNode, 'textAlign', this.align.value );
  },
  
  changeTshadow: function(){
    var s = this.tshadow.value.replace(/\|\|/g,'').split('|*|');
    var shadow = '';
    if(parseInt(s[4])){
      s[4] = '';
      if (s[3].length > 6) {
        var c = s[3].match(/(..)(..)(..)(..)/);
        s[3]='rgba('+Number('0x'+c[1])+','+Number('0x'+c[2])+','+Number('0x'+c[3])+','+Number('0x'+c[4])/255.+')';
      } else s[3] = '#'+s[3];
      shadow = s.join(' ');
    }
    dojo.style(this.tester, 'textShadow', shadow);
  },
  
  changeLineheight: function(){
    dojo.style(this.tester, 'lineHeight', this.lineheight.value);
  },
  
  changeTab: function(){
    var radio = this.tab.radioobj;
    this.t = this.tab.value;
    if(this.t != this.defaultTab){
      dojo.style(this.reset,'display','block');
    }else{
      dojo.style(this.reset,'display','none');
    }
    this.loadSettings();
  },
  
  changeHidden: function(el, value){
    if(el.value == value) return;
    el.value = value;
    this.fireEvent(el, 'change');
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
  },
  
  keyPressed: function(e) {
    if(e.keyCode == 27) { 
      this.closeWindow();
      dojo.disconnect(this.exit);
    }
  }
});
