
dojo.declare("OfflajnImagemanager", null, {
	constructor: function(args) {
    this.dnd = false;
    dojo.mixin(this,args);
    this.map = {};
    var div = document.createElement('div');
    if(typeof(FileReader) != "undefined" && !!FileReader && (('draggable' in div) || ('ondragstart' in div && 'ondrop' in div))){
      this.dnd = true;
    }
    this.init();
  },
  
  
  init: function() {
    this.btn = dojo.byId('offlajnimagemanager'+this.id);
    dojo.connect(this.btn, 'onclick', this, 'showWindow');

    this.selectedImage = "";
    this.hidden = dojo.byId(this.id);
    dojo.connect(this.hidden, 'change', this, 'reset');
    
    this.imgprev = dojo.query('.offlajnimagemanagerimg div', this.btn)[0];
    //if(this.hidden.value != "") dojo.style(this.imgprev,'backgroundImage','url("'+this.root+this.hidden.value+'")');
    if(this.hidden.value != "") dojo.style(this.imgprev,'backgroundImage','url("'+this.hidden.value+'")');
    this.images = new Array();
  },
  
  reset: function(){
    if(this.hidden.value != this.selectedImage){
      this.selectedImage = this.hidden.value;
      if(this.selectedImage == '') this.selectedImage = this.folder;
      this.saveImage();
      this.fireEvent(this.hidden, 'change');
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
  
  showWindow: function(){
    this.showOverlay();
    if(!this.window){
      this.window = dojo.create('div', {'class': 'OfflajnWindow'}, dojo.body());
      var closeBtn = dojo.create('div', {'class': 'OfflajnWindowClose'}, this.window);
      dojo.connect(closeBtn, 'onclick', this, 'closeWindow');
      var inner = dojo.create('div', {'class': 'OfflajnWindowInner'}, this.window);
      dojo.create('h3', {'innerHTML': 'Image Manager'}, inner);
      dojo.create('div', {'class': 'OfflajnWindowLine'}, inner);
      var imgAreaOuter = dojo.create('div', {'class': 'OfflajnWindowImgAreaOuter'}, inner);
      this.imgArea = dojo.create('div', {'class': 'OfflajnWindowImgArea'}, imgAreaOuter);
      
      dojo.place(this.createFrame(''), this.imgArea);
      
      for(var i in this.imgs){
        if(i >=0 )
          dojo.place(this.createFrame(this.imgs[i]), this.imgArea);
      }
      
      var left = dojo.create('div', {'class': 'OfflajnWindowLeftContainer'}, inner);
      var right = dojo.create('div', {'class': 'OfflajnWindowRightContainer'}, inner);
      
      dojo.create('h4', {'innerHTML': 'Upload Your Image'}, left);
      if (this.dnd) {
        this.uploadArea = dojo.create('div', { 'innerHTML': 'Drag images here or<br />', 'class': 'OfflajnWindowUploadarea'}, left);
        
        this.input = dojo.create('input', {'type': 'file'}, this.uploadArea);
        dojo.create('span', {innerHTML: 'Upload', 'class': 'upload'}, this.uploadArea);
        
        dojo.style(this.input, 'display', 'none');
        dojo.connect(this.uploadArea, 'onclick', this, 'openFilebrowser');
        dojo.connect(this.input, 'onchange', this, 'uploadInputFile');
      }else{
        this.uploadArea = dojo.create('form', {
          'action': 'index.php?option=offlajnupload&identifier='+this.identifier,
          'enctype': 'multipart/form-data',
          'method': 'post',
          'target': 'uploadiframe',
          'class': 'OfflajnWindowUploadareaForm'
        }, left);
        dojo.create('input', {'name': 'img', 'type': 'file'}, this.uploadArea);
        dojo.create('button', {'innerHTML': 'Upload', 'type': 'submit'}, this.uploadArea);
        var iframe = dojo.create('iframe', {'name': 'uploadiframe', 'style': 'display:none;'}, this.uploadArea);
        dojo.connect(iframe, 'onload', this, 'alterUpload');
      }
      
      dojo.create('h4', {'innerHTML': 'Currently Selected Image'}, right);
      
      this.selectedframe = dojo.create('div', {'class': 'OfflajnWindowImgFrame'}, right);
      this.selectedframe.img1 = dojo.create('div', {'class': 'OfflajnWindowImgFrameImg'}, this.selectedframe);
      this.selectedframe.img2 = dojo.create('img', {}, this.selectedframe);
      dojo.create('div', {'class': 'OfflajnWindowImgFrameSelected'}, this.selectedframe);
      
      dojo.connect(this.selectedframe, 'onmouseenter', dojo.hitch(this,function(img){dojo.addClass(img, 'show');}, this.selectedframe.img2));
      dojo.connect(this.selectedframe, 'onmouseleave', dojo.hitch(this,function(img){dojo.removeClass(img, 'show');}, this.selectedframe.img2));
      
      dojo.create('div', {'class': 'OfflajnWindowDescription', 'innerHTML': this.description}, right);
      
      var saveCont = dojo.create('div', {'class': 'OfflajnWindowSaveContainer'}, right);
      var savebtn = dojo.create('div', {'class': 'OfflajnWindowSave', 'innerHTML': 'SAVE'}, saveCont);
      dojo.connect(savebtn, 'onclick', this, 'saveImage');
      
      this.initUploadArea();
      
      this.scrollbar = new OfflajnScroller({
        'extraClass': 'multi-select',
        'selectbox': this.imgArea.parentNode,
        'content': this.imgArea,
        'scrollspeed' : 30
      });
    }
    dojo.removeClass(this.window, 'hide');
    this.exit = dojo.connect(document, "onkeypress", this, "keyPressed");
    this.loadSavedImage();
  },
  
  loadSavedImage: function() {
    var val = this.hidden.value;
    if(val == "") val = this.folder;
    val = val.replace(this.siteurl, "");
    if(val == '' || this.images[val] == undefined) return;
    var el = this.images[val];
    el.currentTarget = el.parentNode;
    this.select(el);
  },
  
  closeWindow: function(){
    dojo.addClass(this.window, 'hide');
    dojo.addClass(this.overlayBG, 'hide');
  },
  
  openFilebrowser: function(e){
    if(e.target == this.input) return;
    this.input.click();
  },
  
  createFrame: function(im, folder){
    if(!folder) folder = this.folder;
    if(this.map[im]){
      dojo.place(this.map[im], this.map[im].parentNode, 'last');
      return this.map[im];
    }
    var frame = dojo.create('div', {'class': 'OfflajnWindowImgFrame'});
    dojo.create('div', {'class': 'OfflajnWindowImgFrameImg', 'style': (im != '' ? {
      'backgroundImage': 'url("'+this.root+folder+im+'")'
    }:{}) }, frame);
    if(im != '')
      var img = dojo.create('img', {'src': this.root+folder+im}, frame);
    
    var caption = im != '' ? im.replace(/^.*[\\\/]/, '') : 'No image';
    dojo.create('div', {'class': 'OfflajnWindowImgFrameCaption', 'innerHTML': "<span>"+caption+"</span>"}, frame);
    
    frame.selected = dojo.create('div', {'class': 'OfflajnWindowImgFrameSelected'}, frame);
    
    frame.img = im;
    this.map[im] = frame;
    if(im != ''){
      dojo.connect(frame, 'onmouseenter', dojo.hitch(this,function(img){dojo.addClass(img, 'show');}, img));
      dojo.connect(frame, 'onmouseleave', dojo.hitch(this,function(img){dojo.removeClass(img, 'show');}, img));
      this.images[folder+im] = img;
    }
    dojo.connect(frame, 'onclick', this, 'select');
    return frame;
  },
  
  select: function(e){
    var el = e.currentTarget;
    if(el.img != this.active && this.map[this.active]){
      dojo.removeClass(this.map[this.active], 'active');
    }
    dojo.addClass(el, 'active');
    this.active = el.img;
    dojo.style(this.selectedframe.img1, 'backgroundImage', 'url("'+this.root+this.folder+this.active+'")');
    dojo.attr(this.selectedframe.img2, 'src', this.root+this.folder+this.active);
    this.selectedImage = this.folder+this.active;
    dojo.addClass(this.selectedframe, 'active');
  },
  
  initUploadArea: function(){
    dojo.connect(this.uploadArea, "ondragleave", this, function(e){
      var target = e.target;
    	if (target && target === this.uploadArea) {
    		dojo.removeClass(this.uploadArea, 'over');
    	}
      dojo.stopEvent(e);
    });
    dojo.connect(this.uploadArea, "ondragenter", this, function(e){
    	dojo.addClass(this.uploadArea, 'over');
      dojo.stopEvent(e);
    });
    dojo.connect(this.uploadArea, "ondragover", this, function(e){
      dojo.stopEvent(e);
    });
    dojo.connect(this.uploadArea, "ondrop", this, function(e){
    	this.filesAdded(e.dataTransfer.files);
    	dojo.removeClass(this.uploadArea, 'over');
      dojo.stopEvent(e);
    });
  },
  
  filesAdded: function(files){
    if (typeof files !== "undefined") {
  		for (var i=0, l=files.length; i<l; i++){
  			this.uploadFile(files[i]);
  		}
  	}
    this.scrollbar.scrollReInit();
    this.scrollbar.goToBottom();
  },
  
  uploadInputFile: function(){
    this.uploadFile(this.input.files[0]);
    this.scrollbar.scrollReInit();
    this.scrollbar.goToBottom();
  },
  
  uploadFile: function(file){
    var xhr = new XMLHttpRequest();
    xhr.open("post", this.uploadurl+"&name="+file.name+"&identifier="+this.identifier, true);
    
    // Set appropriate headers
    var boundary = "upload--"+(new Date).getTime();
    //xhr.setRequestHeader("Content-Type", "multipart/form-data; boundary=");
    xhr.setRequestHeader("X-File-Name", file.name);
    xhr.setRequestHeader("X-File-Size", file.fileSize);
    xhr.setRequestHeader("X-File-Type", file.type);

    dojo.connect(xhr, 'onload',dojo.hitch(this,'fileUploaded', file.name, xhr));
    
    if(xhr.upload)
      dojo.connect(xhr.upload, 'onprogress', dojo.hitch(this, 'fileProgress', xhr));
    else
      dojo.connect(xhr, 'onprogress', dojo.hitch(this, 'fileProgress', xhr));
      
    var frame = this.createFrame(file.name);
    this.changeFrameImg(frame, 'blank.png', '/media/system/images/');
    
    frame.span = dojo.query('span', frame)[0];
    frame.span.innerHTML = '0%';
    
    
    var caption = dojo.query('.OfflajnWindowImgFrameCaption', frame)[0];
    frame.progress = dojo.create('div', {'class':'progress'}, caption, 'first');
    dojo.place(frame, this.imgArea);
    this.captionW = dojo.position(caption).w-2;
    
    xhr.frame = frame;
    
    xhr.send(file);
  },
  
  changeFrameImg: function(frame, im, folder){
    if(!folder) folder = this.folder;
    dojo.attr(dojo.query("img", frame)[0], 'src', this.root+folder+im+"?"+new Date().getTime()
);
    dojo.style(dojo.query(".OfflajnWindowImgFrameImg", frame)[0], {
      'backgroundImage': 'url("'+this.root+folder+im+"?"+new Date().getTime()+'")'
    });
  },
  
  fileProgress: function(xhr, e){
    if (e.lengthComputable) {
      var ratio = (e.loaded / e.total);
  		xhr.frame.span.innerHTML = parseInt(ratio * 100) + "%";
      dojo.style(xhr.frame.progress, 'width', (ratio*this.captionW)+'px');
  	}
  },
  
  fileUploaded: function(name, xhr, e, data){
    
    var r = eval("(" + xhr.response+ ")");
    if(r.err){
      this.map[name] = null;
      dojo.destroy(xhr.frame);
      alert(r.err);
      return;
    }
    var img = dojo.query('.OfflajnWindowImgFrameImg',xhr.frame)[0];
    dojo.style(img, 'opacity', 0);
    this.changeFrameImg(xhr.frame, name);
    dojo.style(xhr.frame.progress, 'width', this.captionW+'px');
    xhr.frame.span.innerHTML = name;
    
    dojo.animateProperty({
      node: img,
      duration: 1000,
      properties: {
        opacity : 1
      }
    }).play();
    
    setTimeout(dojo.hitch(this,function(p){
      dojo.animateProperty({
        node: p,
        duration: 300,
        properties: {
          opacity : 0
        }
      }).play();
    },xhr.frame.progress),1000);
  },
  
  alterUpload: function(){
    var data = window["uploadiframe"].document.body.innerHTML;
    if(!data || data == '') return;
    var r = eval("(" + window["uploadiframe"].document.body.innerHTML + ")");
    if(r.err){
      alert(r.err);
      return;
    }else if(r.name){
      var frame = this.createFrame(r.name);
      var caption = dojo.query('.OfflajnWindowImgFrameCaption', frame)[0];
      frame.progress = dojo.create('div', {'class':'progress', 'style' : {'width':(dojo.position(caption).w-2)+'px'} }, caption, 'first');
      dojo.place(frame, this.imgArea);
      this.scrollbar.scrollReInit();
      this.scrollbar.goToBottom();
      setTimeout(dojo.hitch(this,function(p){
        dojo.animateProperty({
          node: p,
          duration: 300,
          properties: {
            opacity : 0
          }
        }).play();
      },frame.progress),1000);
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
  },
  
  keyPressed: function(e) {
    if(e.keyCode == 27) { 
      this.closeWindow();
      dojo.disconnect(this.exit);
    }
  },
  
  saveImage: function() {
    //dojo.style(this.imgprev,'backgroundImage', 'url("'+this.root+this.selectedImage+'")');
    dojo.style(this.imgprev,'backgroundImage', 'url("'+this.selectedImage+'")');
    if(this.selectedImage != this.hidden.value) {
      this.closeWindow();
      if(this.folder == this.selectedImage) this.selectedImage = "";
      this.hidden.value = this.siteurl + this.selectedImage;
      this.fireEvent(this.hidden, 'change');
    }
  }
  
});