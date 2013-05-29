dojo.declare("WW.Login", null, {

	constructor: function(args) {
	  dojo.mixin(this, args);
	  this.initFadeOut();
	  this.wnd = dojo.byId("loginWnd");
    this.xBtn = dojo.byId("xBtn");
	  this.btnPos = {x:0, y:0};
	  dojo.place(this.wnd, dojo.body(), "last");
		this.upArrow = dojo.byId("upArrow");
	  dojo.style(this.wnd, "opacity", 0);
	  this.open = false;
	  this.animOffset = 0;
	  if (this.isGuest) this.initGuest();
	  else this.initUser();
	  if (this.useAJAX) window.onpopstate = dojo.hitch(this, "handleOnpopstate");
	  dojo.connect(this.black, "onclick", this, "closeWnd");
	  if (this.btn) dojo.connect(this.btn, this.openEvent, this, "onclickLoginBtn");
    dojo.connect(document, "onkeypress", this, "onkeypressWnd");
    dojo.connect(window, "onresize", this, "positionWnd");
    if (this.openEvent != "onclick")
      dojo.connect(this.btn, "onclick", function(e) {e.preventDefault()});
    if (this.oauth.length && this.isGuest) this.initSocial();
	},

  initSocial: function() {
    var ico = dojo.query(this.socialType=="socialIco"? ".socialIco" : "span.submitBtn", this.logLyr);
    for (var i=0; i<ico.length; ++i)
      dojo.connect(ico[i], "onclick", dojo.hitch(this, "openSocialWnd", this.oauth[i]));
    if (this.regLyr) {
      ico = dojo.query(this.socialType=="socialIco"? ".socialIco" : "span.submitBtn", this.regLyr);
      for (var i=0; i<ico.length; ++i)
        dojo.connect(ico[i], "onclick", dojo.hitch(this, "openSocialWnd", this.oauth[i]));
    }
  },

  openSocialWnd: function(oauth, e) {
    this.socialWnd = window.open("", 'Login', "width=450, height=450, screenX="+(screen.width/2-225)+", screenY="+(screen.height/2-(oauth.name=="Twitter"?450:225)));
    var sw = this.socialWnd;
    sw.focus(); 
    if (dojo.isIE>6 && dojo.isIE<9
    || navigator.userAgent.indexOf('iPhone') >= 0
    || navigator.userAgent.indexOf('iPad') >= 0) {
      sw.document.write("<br/><h2 style='margin-left:160px;font-family:Verdana;color:#444;text-shadow:1px 1px 2px #aaa'>Loading..</h2>");
      sw.load = this.socialWnd.document.body.children[1];
      sw.setInterval("load.innerHTML+='.'", 250);
    } else {
      sw.document.write("<style>body{margin:150px auto; text-align:center}</style><div class='loginWndInside' style='inline-block'><h1 class='loginH1' style=''>Please wait</h1>"+
        '<label id="strongFields" style="width:250px"><div class="strongField"></div><div class="empty strongField"></div><div class="empty strongField"></div><div class="empty strongField"></div><div class="empty strongField"></div></label><br/></div>');
      var ss;
      for (var i=0; i<document.styleSheets.length; ++i)
        if (document.styleSheets[i].href && document.styleSheets[i].href.match(/improved_ajax_login/)) {
          ss = document.styleSheets[i];
          for (var j=0; j<ss.cssRules.length; ++j)
            if (ss.cssRules[j].selectorText == "#strongFields") {
              var tmp = sw.document.styleSheets[0];
              tmp.insertRule(ss.cssRules[j++].cssText, tmp.cssRules.length);
              tmp.insertRule(ss.cssRules[j++].cssText, tmp.cssRules.length);
              tmp.insertRule(ss.cssRules[j++].cssText, tmp.cssRules.length);
              tmp.insertRule(ss.cssRules[j++].cssText, tmp.cssRules.length);
              tmp.insertRule(ss.cssRules[j++].cssText, tmp.cssRules.length);
              tmp.insertRule("body, "+ss.cssRules[j].cssText, tmp.cssRules.length);
              break;
            }
          break;
        }
      sw.load = sw.document.body.children[0].children[1].children;
      sw.dojo = dojo;
      sw.i = 0;
      sw.setInterval("dojo.addClass(load[i], 'empty'); i = (i+1)%load.length; dojo.removeClass(load[i], 'empty');", this.dur+50);
    }
    sw.location.href = oauth.url;
    this.socialBtn = e.currentTarget;
  },

	handleOnpopstate: function(e) {
		if ((dojo.isChrome || dojo.isSafari)
		&& !window.firstPopstate) window.firstPopstate = true;
		else this.ajaxLoadPage({
			url: location.href,
			onpopstate: true
		});
	},

	ie7Fix: function() {
	  var w = dojo.position(this.form).w;
    this.form.parentNode.style.width = w+"px";    
    dojo.connect(this.submit, "onclick", this, "onsubmitLogin");
	},

	initGuest: function() {
    this.logLyr = dojo.byId("logLyr");
    this.regLyr = dojo.byId("regLyr");
    // init registration form
    if (this.regLyr) {
      this.regForm = dojo.byId("regForm");
      var input = dojo.query("input[type=text],input[type=password]", this.regForm);
      for (var i=0; i<input.length; ++i) {
        input[i].pos = input[i].parentNode.attributes["class"].value == "columnL"? 'R' : 'L';
        input[i].value = "";
        if (this.showHint && input[i].title) {
          dojo.connect(input[i], "onfocus", dojo.hitch(null, function(p) {
      	    new WW.LoginMsg(p);
          }, {
    		    parent: input[i],
    		    pos: input[i].pos,
    		    ico: "Inf",
    		    msg: input[i].title.replace(/\s+-\s+/, ' -<br />')
      		}));
          input[i].title = "";
        }
      }
      this.submitReg = dojo.byId("submitReg");
      dojo.connect(this.regForm, "onsubmit", this, "onsubmitReg");
      dojo.connect(this.regLyr, "onclick", function(e) {e.stopPropagation()});
      dojo.connect(this.regForm.name, "onblur", this, "checkName");
      dojo.connect(this.regForm.username, "onblur", this, "checkUsername");
      dojo.connect(this.regForm.passwd, "onblur", this, "checkPasswd");
      dojo.connect(this.regForm.passwd2, "onblur", this, "checkPasswd");
      dojo.connect(this.regForm.email, "onblur", this, "checkEmail");
      dojo.connect(this.regForm.email2, "onblur", this, "checkEmail");
      dojo.connect(this.regForm.email2, "onfocus", dojo.hitch(this, function() {
        if (!this.regForm.email.value && !this.regForm.email2.value) this.regForm.email.focus();
      }));
      dojo.connect(this.regForm.passwd2, "onfocus", dojo.hitch(this, function() {
        if (!this.regForm.passwd.value && !this.regForm.passwd2.value) this.regForm.passwd.focus();
      }));
  	  this.reg = dojo.byId("regBtn");
      this.passStrongness = dojo.byId("passStrongness");
      this.strongFields = dojo.byId("strongFields");
      this.strongField = this.strongFields.children;
      dojo.connect(this.regForm.elements['passwd'], "onkeyup", this, "checkPassStrength");
      if (this.regLyr && !this.reg) this.regComp = true;
      if (this.captcha) this.initCaptcha();
    }
	  //  init login form
    this.btn = dojo.byId("loginBtn");
	  this.load = false;
	  this.form = dojo.byId("ologinForm");
	  this.submit = dojo.byId("submitBtn");
	  this.name = this.form.elements["username"];
    if (!this.name) this.name = this.form.elements["email"];
	  this.pass = this.form.elements["passwd"];
	  this.forgot = dojo.query('a', this.form)[0];
	  this.onblurUserTxt();
		this.onblurPassTxt();
		if (dojo.isIE == 7) this.ie7Fix();
		//  init connects
	  dojo.connect(this.xBtn, "onclick", this, "closeWnd");
	  dojo.connect(this.logLyr, "onclick", function(e) {e.stopPropagation()});
	  dojo.connect(this.name, "onfocus", this, "onfocusUserTxt");
	  dojo.connect(this.pass, "onfocus", this, "onfocusPassTxt");
	  dojo.connect(this.name, "onblur", this, "onblurUserTxt");
	  dojo.connect(this.pass, "onblur", this, "onblurPassTxt");
    if (this.form.remember) dojo.connect(this.form.remember.parentNode, "onclick", this, "onclickCheckBox");
	  dojo.connect(this.form, "onsubmit", this, "onsubmitLogin");
	  if (this.reg && this.regPage == 'joomla') {
      dojo.connect(this.reg, this.openEvent, this, "onclickLoginBtn");
	  }
	},

	initUser: function() {
	  this.btn = dojo.byId("userBtn");
	  this.form = dojo.byId("logoutForm");
		var links = dojo.query('a', this.wnd);
		for (var i=0; i<links.length; ++i)
	    dojo.connect(links[i], "onclick", dojo.hitch(this, "ajaxLoadPage",
				links.length==i+1? {form: this.form} : {url: links[i].href}));
	},

  onsubmitReg: function(e) {
    var input = dojo.query("input.required", this.regForm);
    e.preventDefault();
    // first check empty fields
    for (var i=0; i<input.length; ++i)
      if (!input[i].value) {
        new WW.LoginMsg({
  		    parent: input[i],
  		    wnd: this.wnd,
          pos: input[i].parentNode.attributes["class"].value == "columnL"? 'R' : 'L',
  		    ico: "Err",
  		    msg: this.requiredLng
    		});
        return;
      }
    for (var i=0; i<input.length; ++i)
      if (!dojo.hasClass(input[i], "correct") && input[i].name != "recaptchaResponse") {
        if (this.wait.style.display == "block" && this.wait.nextSibling.type == "text") {
          this.autosubmit = true;
        } else {
          input[i].focus();
          input[i].blur();
        }
        return;
      }
    this.wait = dojo.query(".waitAnim", this.submitReg)[0];
    this.wait.style.display = "block";
    this.waitInterval = setInterval(dojo.hitch(this, "waitAnim"), 1000/18);
    if (!location.href.match(/^https/) && this.regForm.action.match(/^https/)) {
      this.regForm.ajax.value = 1;
      this.regForm.submit();
    } else dojo.xhrPost({
      form: this.regForm,
      load: dojo.hitch(this, "onLoadReg"),
      timeout: this.timeout,
      handleAs: "json",
      preventCache: true
    });
  },

  onLoadReg: function(res) {
    if (res.error) {
      clearInterval(this.waitInterval);
      this.wait.style.display = "none";
      var field = this.regForm[res.field];
      new WW.LoginMsg({
		    parent: field,
		    pos: field.parentNode.className == "columnL"? 'R' : 'L',
		    ico: "Err",
		    msg: res.message
  		});
      if (res.error == 1) location.href = location.href;
      else if (this.captcha) this.reloadCaptchaImg();
    } else {
      var f = dojo.position(this.regForm);
      this.regLyr.innerHTML = "";
      dojo.create("h1", {
        "class": "loginH1",
        innerHTML: '<div class="correct">&nbsp;</div>'+this.regLng
      }, this.regLyr);
      dojo.create("span", {
        innerHTML: res.message+"<br />",
        "class": "smallTxt",
        style: "font-size:12px; width:"+f.w+"px"
      }, this.regLyr);
      var ok = dojo.create("span", {
        "class": "loginBtn submitBtn",
        style: "width:50%; display:inline-block; float:right; margin-top:10px",
        innerHTML: "OK"
      }, this.regLyr);
      if (this.regComp)
        dojo.connect(ok, "onclick", dojo.hitch(this, "onclickLoginBtn", {currentTarget: this.btn}));
      else dojo.connect(ok, "onclick", this, "closeWnd");
    }
  },
/*
  checkEmpty: function(e) {
    var input = e.currentTarget;
    if (!input.value) dojo.removeClass(input, "correct");
    else dojo.addClass(input, "correct");
  },
*/
  checkName: function(e) {
    var input = e.currentTarget;
    dojo.removeClass(input, "correct");
    if (!input.value) return;
    if (input.value.length < 2) {
      new WW.LoginMsg({
		    parent: input,
		    pos: input.pos,
		    ico: "Err",
		    msg: input.nextSibling.innerHTML
  		});
      return;
    }
    dojo.addClass(input, "correct");
  },

  checkUsername: function(e) {
    var m, input = e.currentTarget;
    dojo.removeClass(input, "correct");
    if (!input.value) return;
    if (input.value.length < 2 || input.value.match(/[\s<>\\'"%;\(\)&]/)) {
      new WW.LoginMsg({
		    parent: input,
		    pos: input.pos,
		    ico: "Err",
		    msg: input.nextSibling.innerHTML
  		});
      return;
    }
    dojo.attr(input, "disabled", "disabled");
    input.style.backgroundPosition = "-20px";
    this.wait = input.previousSibling;
    this.wait.style.display = "block";
    this.waitInterval = setInterval(dojo.hitch(this, "waitAnim"), 1000/18);
    dojo.xhrGet({
      url: this.base+"index.php?option=com_improved_ajax_login&task=register&check=username&value="+input.value,
      load: dojo.hitch(this, function(resp) {
        if (!resp.match("success")) {
          new WW.LoginMsg({
    		    parent: input,
    		    pos: input.pos,
    		    ico: "Err",
    		    msg: resp.replace(/\.\s+/, ".<br />")
      		});
        } else {
          dojo.addClass(input, "correct");
        }
        clearInterval(this.waitInterval);
        this.wait.style.display = "none";
        input.style.backgroundPosition = "";
        dojo.removeAttr(input, "disabled");
        if (this.autosubmit) {
          this.autosubmit = false;
          this.submitReg.click();
        }
      })
    });    
  },

  checkEmail: function(e) {
    var input = e.currentTarget;
    dojo.removeClass(this.regForm.email, "correct");
    dojo.removeClass(this.regForm.email2, "correct");
    if (!input.value) return;
    var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
    if (!re.test(input.value)) {
      new WW.LoginMsg({
		    parent: input,
		    pos: input.pos,
		    ico: "Err",
		    msg: this.regForm.email.nextSibling.innerHTML
  		});
      return;
    }
    if (!this.regForm.email2.value) return;
    if (this.regForm.email.value != this.regForm.email2.value) {
      new WW.LoginMsg({
		    parent: input == this.regForm.email? this.regForm.email : this.regForm.email2,
        parent2: input != this.regForm.email? this.regForm.email : this.regForm.email2,
		    pos: input.pos,
		    ico: "Err",
		    msg: this.regForm.email2.nextSibling.innerHTML
  		});
      return;
    }
    dojo.attr(this.regForm.email, "disabled", "disabled");
    dojo.attr(this.regForm.email2, "disabled", "disabled");
    input.style.backgroundPosition = "-20px";
    this.wait = input.previousSibling;
    this.wait.style.display = "block";
    this.waitInterval = setInterval(dojo.hitch(this, "waitAnim"), 1000/18);
    dojo.xhrGet({
      url: this.base+"index.php?option=com_improved_ajax_login&task=register&check=email&value="+input.value,
      load: dojo.hitch(this, function(resp) {
        if (!resp.match("success")) {
          new WW.LoginMsg({
    		    parent: this.regForm.email,
    		    pos: this.regForm.email.pos,
    		    ico: "Err",
    		    msg: resp.replace(/\.\s+/, ".<br />")
      		});
          this.regForm.email2.value = "";
        } else {
          dojo.addClass(this.regForm.email, "correct");
          dojo.addClass(this.regForm.email2, "correct");
        }
        clearInterval(this.waitInterval);
        this.wait.style.display = "none";
        input.style.backgroundPosition = "";
        dojo.removeAttr(this.regForm.email, "disabled");
        dojo.removeAttr(this.regForm.email2, "disabled");
        if (this.autosubmit) {
          this.autosubmit = false;
          this.submitReg.click();
        }
      })
    });
  },

  checkPasswd: function(e) {
    var input = e.currentTarget;
    dojo.removeClass(this.regForm.passwd, "correct");
    dojo.removeClass(this.regForm.passwd2, "correct");
    if (!input.value) return;
    if (input.value.length < 4) {
      new WW.LoginMsg({
		    parent: input,
		    pos: input.pos,
		    ico: "Err",
		    msg: this.regForm.passwd.nextSibling.innerHTML
  		});
      return;
    }
    if (!this.regForm.passwd2.value) return;
    if (this.regForm.passwd.value != this.regForm.passwd2.value) {
      new WW.LoginMsg({
		    parent: input == this.regForm.passwd? this.regForm.passwd : this.regForm.passwd2,
        parent2: input != this.regForm.passwd? this.regForm.passwd : this.regForm.passwd2,
		    pos: input.pos,
		    ico: "Err",
		    msg: this.regForm.passwd2.nextSibling.innerHTML
  		});
      return;
    }
    dojo.addClass(this.regForm.passwd, "correct");
    dojo.addClass(this.regForm.passwd2, "correct");
  },

  initCaptcha: function() {
    var refresh = dojo.byId("refreshBtn");
    dojo.place('<img id="recaptchaImg" onload="odojo.addClass(this, \'fadeIn\')"/>', refresh, "after");
    this.regForm.recaptchaChallenge.value = "";
    var reloadImg = function(c) {
      dojo.byId("recaptchaChallenge").value = c;
      dojo.byId("recaptchaImg").src="http://www.google.com/recaptcha/api/image?c="+c;
      Recaptcha.noclick = false;
    };
    if (!window.Recaptcha)
      Recaptcha = {
        finish_reload: reloadImg,
        challenge_callback: function() {reloadImg(RecaptchaState.challenge)}
      };
    else {
      Recaptcha._finish_reload = Recaptcha.finish_reload;
      Recaptcha.finish_reload = function(c, type) {
        if (Recaptcha.noclick) reloadImg(c);
        else Recaptcha._finish_reload(c, type);
      };
      Recaptcha._challenge_callback = Recaptcha.challenge_callback;
      Recaptcha.challenge_callback = function() {
        if (RecaptchaState.site == login.captcha) reloadImg(RecaptchaState.challenge);
        else Recaptcha._challenge_callback();
      };
    }
    dojo.connect(refresh, "onclick", this, "reloadCaptchaImg");
    if (this.regComp) dojo.create("script", {
      type: "text/javascript",
      src: "http://www.google.com/recaptcha/api/challenge?ajax=1&k="+this.captcha+"&nocache="+Math.random(),
      async: "async"
    }, document.head);
  },

  reloadCaptchaImg: function() {
    dojo.removeClass("recaptchaImg", "fadeIn");
    Recaptcha.noclick = true;
    dojo.create("script", {
      type: "text/javascript",
      src: "http://www.google.com/recaptcha/api/reload?k="+this.captcha+"&c="+RecaptchaState.challenge+"&type=image",
      async: "async"
    }, document.head);
    this.regForm.recaptchaResponse.value = "";
  },

  checkPassStrength: function(e) {
    var pass = e.currentTarget,
        strong = 0;
    if (pass.value.length > 3) {
      ++strong;
      if (pass.value.length > 7) ++strong;
      if (pass.value.match(/\d/)) ++strong;
      if (pass.value.match(/[A-Z]/)) ++strong;
      if (pass.value.match(/\W/)) ++strong;
    }
    this.passStrongness.innerHTML = this.passwdCat[strong];
    for (var i=0; i<this.strongField.length; ++i)
      if (strong > i) dojo.removeClass(this.strongField[i], "empty");
      else dojo.addClass(this.strongField[i], "empty");
  },

	onkeypressWnd: function(e) {
	  if (this.open && e.keyCode == 27) this.closeWnd(0);
	},

	waitAnim: function() {
	  this.wait.style.backgroundPosition = '0 '+this.animOffset+'px';
    this.animOffset -= 14;
	},

	ajaxLoadPage: function(args, e) {
	  if (e) {
			e.preventDefault();	// onclick="return false" doesn't work in IE
		  var t = e.currentTarget;
		  if (t.href) {
		  	dojo.addClass(t, 'active');
		    this.wait = dojo.place('<div class="waitAnim"></div>', t, 'before');
		    this.waitInterval = setInterval(dojo.hitch(this, "waitAnim"), 1000/18);
			}
		}
	  if (!this.isGuest || !this.open) {
		  this.black.style.display = "block";
this.fadeOut.play();
		}
		if (window.history.pushState && args.url && !args.onpopstate)
			try {
				window.history.pushState(null, null, args.url);
			} catch(ex) {
			  location.href = args.url.split('#')[0];
			}
    if (!this.useAJAX)
			if (args.url) location.href = args.url.split('#')[0];
			else args.form.submit();
		else dojo.xhrPost(dojo.mixin({
			load: dojo.hitch(this, "replaceContent"),
			error: args.url?
				dojo.hitch(window, "eval", "location.href='"+args.url+"'.split('#')[0];") :
				dojo.hitch(args.form, "submit"),
			timeout: this.timeout,
      preventCache: true
		}, args));
	},

	initFadeOut: function() {
		this.black = dojo.place("<div class='blackBg'></div>", dojo.body());
    if (navigator.userAgent.indexOf("Android") > -1) {
	    var bp = dojo.position(dojo.body());
	    this.black.style.width = bp.w + "px";
	    this.black.style.height = bp.h + "px";
    } else this.black.style.position = "fixed";
		dojo.style(this.black, "opacity", 0);
		this.fadeOut = dojo.animateProperty({
	    node: this.black,
	    duration: this.dur,
	    properties: {opacity: this.bgOpacity}
		});
	},

	positionWnd: function() {
    if (this.isGuest && this.wndCenter) {
      this.upArrow.style.display = "none";
  		this.wnd.style.position = "fixed";
  		var b = dojo.position(this.wnd);
  		var w = window.innerWidth? window.innerWidth : document.documentElement.clientWidth;
      var h = window.innerHeight? window.innerHeight : document.documentElement.clientHeight;
  		this.wnd.style.top = (this.wnd.top = (h-b.h)/2) + "px";
  		this.wnd.style.left = (w-b.w)/2 +"px";
    } else {
      var btn = this.activeBtn;
      if (!btn) return;
  		var b = dojo.position(btn, true);
      this.wnd.children[0].style.minWidth = (b.w-12)+"px";
  		if (b.x==this.btnPos.x && b.y==this.btnPos.y) return;
  		this.btnPos = b;
  		var bodyW = dojo.position(document.documentElement).w;
  		this.left = b.w/2+b.x < bodyW/2;
  		this.wnd.style.top = (this.wnd.top = b.y+b.h+15) + "px";
  		this.upArrow.style.top = -11-this.border + "px";
  		if (this.left) {
  		  this.wnd.style.left = b.x-this.border + "px";
  		  this.upArrow.style.left = b.w/2-10 + "px";
  		} else {
  		  this.wnd.style.right = bodyW-b.x-b.w-this.border + "px";
  		  this.upArrow.style.right = b.w/2+10 + "px";
  		}
    }
	  this.openAni = dojo.animateProperty({
	    node: this.wnd,
	    duration: this.dur,
	    properties: {
				opacity: 1,
				top: {
					start: this.wnd.top+(this.isGuest && this.wndCenter? -30 : 30),
					end: this.wnd.top
				}
			},
			onEnd: this.isGuest? dojo.hitch(this.fadeOut, "play") : null
		});
	  this.closeAni = dojo.animateProperty({
	    node: this.wnd,
	    duration: this.dur,
	    properties: {
				opacity: 0,
				top: this.wnd.top+30
			},
	    onEnd: dojo.hitch(this, "onEndCloseAni")
		});
	},

	onsubmitLogin: function(e) {
    e.preventDefault();
	  if (this.load) return;
	  this.load = true;
    this.wait = dojo.query(".waitAnim", this.submit)[0];
	  this.wait.style.display = 'block';
	  this.waitInterval = setInterval(dojo.hitch(this, "waitAnim"), 1000/18);
    if (!location.href.match(/^https/) && this.form.action.match(/^https/)) {
      this.form.ajax.value = 1;
      this.form.submit();
    } else dojo.xhrPost({
      form: this.form,
			load: dojo.hitch(this, "getAjaxResult"),
			error: dojo.hitch(this.form, "submit"),
			timeout: this.timeout,
      preventCache: true
		});
	},

	getAjaxResult: function(data, args) {
	  try {
	    data = dojo.fromJson(data)
	    var msg = new WW.LoginMsg({
		    parent: this.pass,
		    wnd: this.btn? this.wnd : dojo.body(),
		    pos: this.left? 'L' : 'R',
		    ico: "Err",
		    msg: data.errorMsg
			});
      console.log(msg)
	    if (data.error == 1) {
        setTimeout("location.href = location.href;", this.dur);
        return;
      }
			this.load = false;
			this.wait.style.display = 'none';
			clearInterval(this.waitInterval);
		} catch (ex) {
		  if (this.useAJAX) this.replaceContent(data, args);
			else location.href = data.split('#')[0];
		}
	},

	runJS: function() {
    var js = dojo.query("script[src=]")
    for (var i=0; i<js.length; ++i) {
      window._node = js[i];
      eval("try {\n"+js[i].innerHTML+"\n} catch(ex) {if (console) console.error(ex)}");
    }
    delete window._node;
  },

	replaceContent: function(content, args) {
	  clearInterval(this.waitInterval);
    document.write = document.writeln = function(text) {
      if (window._node) dojo.place(text, window._node, "after");
      else dojo.place(text, dojo.body());
    };
    var jsrc = [];
    var js = document.getElementsByTagName("script");
    for (var i=0; i<js.length; ++i) jsrc[js[i].src] = true;
		document.documentElement.innerHTML = content.match(/<html[^>]*>((.|[\n\r])*)<\/html[^>]*>/i)[1];
    window.scrollTo(0, 0);
    js = dojo.query("script");
    var last = 0;
    for (var i=0; i<js.length; ++i)
      if (js[i].src && !jsrc[js[i].src]) {
        last = dojo.create("script", {type: js[i].type, src: js[i].src, async: "async"}, document.head);
        js[i].parentNode.removeChild(js[i]);
      }
    if (last) dojo.connect(last, "onload", this.runJS);
    else this.runJS();
	},

	closeWnd: function(e) {
		if (!this.open
		|| (e.target && dojo.hasClass(e.target.parentNode, "loginMsg"))
    || (e.button && e.button > 0)) return;
    this.black.style.display = "none";
    if (this.btn) dojo.removeClass(this.btn, "active");
    if (this.reg) dojo.removeClass(this.reg, "active");
    this.openAni.stop();
    this.closeAni.play();
	},

	onclickLoginBtn: function(e) {
    if (e.preventDefault) e.preventDefault();
		if (e.stopPropagation) e.stopPropagation();
	  if (this.open) {
      if (this.openEvent == "onclick") this.closeWnd(0);
		} else {
      this.activeBtn = e.currentTarget
      if (this.activeBtn == this.btn) {
        if (this.regLyr && this.reg) this.regLyr.style.display = "none";
        if (this.logLyr) this.logLyr.style.display = "block";
      } else {
        if (this.btn) this.logLyr.style.display = "none";
        this.regLyr.style.display = "block";
        // first captcha img load
        if (this.captcha && !this.regForm.recaptchaChallenge.value) {
          dojo.create("script", {
            type: "text/javascript",
            src: "http://www.google.com/recaptcha/api/challenge?ajax=1&k="+this.captcha+"&nocache="+Math.random(),
            async: "async"
          }, document.head);
        }
      }
		  this.positionWnd();
		  dojo.addClass(this.activeBtn, "active");
		  this.wnd.style.visibility = "visible";
			dojo.style(this.black, {opacity:0, display:"block"});
			this.closeAni.stop();
			this.openAni.play();
			this.open = true;
		}
	},

	onEndCloseAni: function() {
    this.wnd.style.visibility = "hidden";
	  this.open = false;
	},

	onfocusUserTxt: function() {
	  if (this.name.value == this.userLng) this.name.value = "";
	},

	onblurUserTxt: function() {
	  if (this.name.value == "") this.name.value = this.userLng;
	},

	onfocusPassTxt: function() {
	  if (this.pass.value == this.passLng) {
			this.pass.value = "";
	  	this.pass.type = "password";
		} else this.pass.select();
	},

	onblurPassTxt: function() {
	  if (this.pass.value == "") {
	    try {
	    	this.pass.type = "text";
			} catch (ex) {}
			this.pass.value = this.passLng;
		}
	},

	onclickCheckBox: function(e) {
	  if (e.currentTarget.children[1].checked) {
			dojo.removeClass(e.currentTarget.children[0], "active");
      e.currentTarget.children[1].checked = false;
    } else {
      dojo.addClass(e.currentTarget.children[0], "active");
      e.currentTarget.children[1].checked = true;
    }
	}

});

dojo.declare("WW.LoginMsg", null, {

	// msg: String
	//  message
	msg: "",

	// dur: Integer
	//  animation duration
	dur: 300,

	// ico: String
	//  message icon
	// values:
	//	"Inf", "Err"
	ico: "Err",

	// pos: String
	//  horizontal message position
	// values:
	//  "L", "R"
	pos: "L",

	constructor: function(args) {
	  dojo.mixin(this, args);
    if (!ologin.open && ologin.btn && !ologin.regComp) return;
	  this.domNode = dojo.place(
			'<div class="loginMsg '+this.ico+'"><span class="login'+this.ico+'"><div class="arrow'+this.pos+'"></div><div class="icon'+this.ico+'">&nbsp;</div>'+this.msg+'</span></div>',
			dojo.body());
		dojo.style(this.domNode, "opacity", 0);
    this.initPosition();
    if (this.ico == "Inf") this.onBlur = dojo.connect(this.parent, "onblur", this, "close");
    else this.onFocus = dojo.connect(this.parent, "onfocus", this, "close");
    if (this.parent2) this.onFocus2 = dojo.connect(this.parent2, "onfocus", this, "close");
		this.onclickDoc = dojo.connect(document, "onclick", this, "close");
		this.onEsc = dojo.connect(document, "onkeypress", this, "onkeypress");
    this.onResize = dojo.connect(window, "onresize", this, "close");
		this.ani.play();
	},

  initPosition: function() {
		var p = dojo.position(this.parent, true);
		var bw = dojo.position(document.documentElement).w;
		this.domNode.style.top = p.y + "px";
		this.domNode.style.display = "block";
    var w = dojo.position(this.domNode).w;
    // if there is place then place it to the other side
    if (this.pos == 'L' && bw-p.x-p.w < w) {
      this.pos = 'R';
      this.domNode.children[0].children[0].attributes["class"].value = "arrow"+this.pos;
    }
    if (this.pos == 'R' && p.x < w) {
      this.pos = 'L';
      this.domNode.children[0].children[0].attributes["class"].value = "arrow"+this.pos;
    }
		this.prop = this.pos == 'L' ?
			{left: {start:p.x+p.w+60, end:p.x+p.w+10}} :
			{right:{start:bw-p.x+60, end:bw-p.x+10}}
		this.prop.opacity = 1;
  	this.ani = dojo.animateProperty({
	    node: this.domNode,
	    duration: this.dur,
	    properties: this.prop
		});
  },

	onkeypress: function(e) {
	  if (e.keyCode == 27) this.close();
	},

	close: function() {
	  this.ani.stop();
    if (this.onBlur) dojo.disconnect(this.onBlur);
    if (this.onFocus) dojo.disconnect(this.onFocus);
    if (this.onFocus2) dojo.disconnect(this.onFocus2);
    dojo.disconnect(this.onResize);
    dojo.disconnect(this.onEsc);
	  dojo.disconnect(this.onclickDoc);
	  var prop = this.prop.left?
	    {left: this.prop.left.start} :
	    {right:this.prop.right.start};
		prop.opacity = 0;
  	dojo.animateProperty({
	    node: this.domNode,
	    duration: this.dur,
	    properties: prop,
			onEnd: function() {dojo.destroy(this.node)}
		}).play();
	}

});