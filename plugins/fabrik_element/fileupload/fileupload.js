var FbFileUpload = new Class({
	Extends : FbFileElement,
	initialize : function (element, options) {
		this.plugin = 'fileupload';
		this.parent(element, options);
		this.toppath = this.options.dir;
		if (this.options.folderSelect === "1" && this.options.editable === true) {
			this.ajaxFolder();
		}
		
		this.submitEvent = function (form, json) {
			this.onSubmit(form);
		}.bind(this);
		
		Fabrik.addEvent('fabrik.form.submit.start', this.submitEvent);
		if (this.options.ajax_upload && this.options.editable !== false) {
			this.watchAjax();
			this.options.files = $H(this.options.files);
			if (this.options.files.getLength() !== 0) {
				this.uploader.trigger('FilesAdded', this.options.files);
				this.startbutton.addClass('plupload_disabled');
				this.options.files.each(function (file) {
					var response = {
						'filepath' : file.path,
						uri : file.url
					};
					this.uploader.trigger('UploadProgress', file);
					this.uploader.trigger('FileUploaded', file, {
						response : JSON.encode(response)
					});
					document.id(file.id).getElement('.plupload_file_status').set('text', '100%');
					document.id(file.id).getElement('.plupload_file_size').set('text', file.size);					
				}.bind(this));
				//this.uploader.trigger('Init'); //no as this creates a second div interface
				// hack to reposition the hidden input field over the 'ad' button
				var c = document.id(this.options.element + '_container');
				var diff = document.id(this.options.element + '_browseButton').getPosition().y - c.getPosition().y;
				// $$$ hugh - working on some IE issues
				var file_element = c.getParent('.fabrikElement').getElement('input[type=file]');
				if (file_element) {
					c.getParent('.fabrikElement').getElement('input[type=file]').getParent().setStyle('top', diff);
				}
			}
		}
		
		this.watchDeleteButton();
	},
	
	/**
	 * Single file uploads can allow the user to delee the reference and/or file
	 */
	watchDeleteButton: function () {
		var c = this.getContainer();
		if (!c) {
			return;
		}
		var b = c.getElement('[data-file]');
		if (typeOf(b) !== 'null') {
			b.addEvent('click', function (e) {
				e.stop();
				if (confirm(Joomla.JText._('PLG_ELEMENT_FILEUPLOAD_CONFIRM_SOFT_DELETE'))) {
					new Request({
						url: '',
						data: {
							'option': 'com_fabrik',
							'format': 'raw',
							'task': 'plugin.pluginAjax',
							'plugin': 'fileupload',
							'method': 'ajax_clearFileReference',
							'element_id': this.options.id,
							'formid': this.form.id,
							'rowid': this.form.options.rowid
						}
					}).send();
					if (confirm(Joomla.JText._('PLG_ELEMENT_FILEUPLOAD_CONFIRM_HARD_DELETE'))) {
						this.makeDeletedImageField(this.groupid, b.get('data-file')).inject(this.getContainer(), 'inside');
					}
					/*
					b.getNext().destroy();
					b.destroy();
					*/
					var delete_span = document.id(this.element.id + '_delete_span');
					if (delete_span) {
						delete_span.destroy();
					}
				}
			}.bind(this));
		}
	},
	
	/**
	 * Sets the element key used in Fabrik.blocks.form_X.formElements
	 * overwritten by dbjoin rendered as checkbox 
	 * 
	 * @since   3.0.7
	 * 
	 * @return  string
	 */

	getFormElementsKey: function (elId) {
		if (this.options.ajax_upload && this.options.ajax_max > 1) {
			return this.options.listName + '___' + this.options.elementShortName;
		} else {
			return this.parent(elId);
		}
	},

	/**
	 * when in ajax form, on submit the list will call this, so we can remove the submit event
	 * if we dont do that, upon a second form submission the original submitEvent is used causing a js error
	 * as it still references the files uploaded in the first form
	 */
	removeCustomEvents: function () {
		Fabrik.removeEvent('fabrik.form.submit.start', this.submitEvent);
	},
	
	cloned: function (c) {
		// replaced cloned image with default image
		if (typeOf(this.element.getParent('.fabrikElement')) === 'null') {
			return;
		}
		var i = this.element.getParent('.fabrikElement').getElement('img');
		if (i) {
			i.src = Fabrik.liveSite + this.options.defaultImage;
		}
		this.parent(c);
	},

	decloned: function (groupid) {
		var f = document.id('form_' + this.form.id);
		
		// erm fabrik_deletedimages is never created why test?
		var i = f.getElement('input[name=fabrik_deletedimages[' + groupid + ']');
		if (typeOf(i) === 'null') {
			this.makeDeletedImageField(groupid, this.options.value).inject(f);
		}
	},
	
	/**
	 * Create a hidden input which will tell fabrik, upon form submission, to delete the file
	 * 
	 *  @param  int     groupid  group id
	 *  @param  string  value    file to delete
	 *  
	 *  @return  DOM Node - hidden input
	 */
	makeDeletedImageField: function (groupid, value) {
		return new Element('input', {
			'type' : 'hidden',
			'name' : 'fabrik_fileupload_deletedfile[' + groupid + '][]',
			'value' : value
		});
	},

	update : function (val) {
		if (this.element) {
			var i = this.element.getElement('img');
			if (typeOf(i) !== 'null') {
				i.src = val;
			}
		}
	},

	watchAjax : function () {
		if (this.options.editable === false) {
			return;
		}
		var a, title;
		var el = this.getElement();
		if (typeOf(el) === 'null') {
			return;
		}
		var c = el.getParent('.fabrikSubElementContainer');
		this.container = c;
		var canvas = c.getElement('canvas');
		if (typeOf(canvas) === 'null') {
			return;
		}
		this.widget = new ImageWidget(canvas, {
			
			'imagedim': {
				x: 200,
				y: 200,
				w: this.options.winWidth,
				h: this.options.winHeight
			},
			
			'cropdim' : {
				w: this.options.cropwidth,
				h: this.options.cropheight,
				x: this.options.cropwidth / 2,
				y: this.options.cropheight / 2
			},
			crop: this.options.crop
		});
		this.pluploadContainer = c.getElement('.plupload_container');
		this.pluploadFallback = c.getElement('.plupload_fallback');
		this.droplist = c.getElement('.plupload_filelist');
		this.startbutton = c.getElement('.plupload_start');
		var plupopts = {
			runtimes: this.options.ajax_runtime,
			browse_button: this.element.id + '_browseButton',
			container: this.element.id + '_container',
			drop_element: this.element.id + '_dropList_container',
			url: 'index.php?option=com_fabrik&format=raw&task=plugin.pluginAjax&plugin=fileupload&method=ajax_upload&element_id=' + this.options.elid,
			max_file_size: this.options.max_file_size + 'kb',
			unique_names: false,
			flash_swf_url: this.options.ajax_flash_path,
			silverlight_xap_url: this.options.ajax_silverlight_path,
			chunk_size: this.options.ajax_chunk_size + 'kb',
			multipart: true
		};
		this.uploader = new plupload.Uploader(plupopts);

		// (1) INIT ACTIONS
		this.uploader.bind('Init', function (up, params) {
			// FORCEFULLY NUKE GRACEFUL DEGRADING FALLBACK ON INIT
			this.pluploadFallback.destroy();
			this.pluploadContainer.removeClass("fabrikHide");
		}.bind(this));

		this.uploader.bind('FilesRemoved', function (up, files) {
		});

		// (2) ON FILES ADDED ACTION
		this.uploader.bind('FilesAdded', function (up, files) {
			var count = this.droplist.getElements('li').length;
			this.startbutton.removeClass('plupload_disabled');
			files.each(function (file, idx) {
				if (count >= this.options.ajax_max) {
					alert(Joomla.JText._('PLG_ELEMENT_FILEUPLOAD_MAX_UPLOAD_REACHED'));
				} else {
					count++;
					var del = new Element('div', {
						'class' : 'plupload_file_action'
					}).adopt(new Element('a', {
						'href': '#',
						'style': 'display:block',
						events: {
							'click': function (e) {
								this.pluploadRemoveFile(e, file);
							}.bind(this)
						}
					}));
					if (this.isImage(file)) {
						a = new Element('a', {
							'href' : '#',
							alt : Joomla.JText._('PLG_ELEMENT_FILEUPLOAD_RESIZE'),
							events : {
								'click': function (e) {
									this.pluploadResize(e);
								}.bind(this)
							}
						});
						if (this.options.crop) {
							a.set('html', this.options.resizeButton);
						} else {
							a.set('html', this.options.previewButton);
						}
						title = new Element('span').set('text', file.name);
					} else {
						a = new Element('span');
						title = new Element('a', {'href': file.url}).set('text', file.name);
					}
					
					var filename = new Element('div', {
						'class' : 'plupload_file_name'
					}).adopt([ title, new Element('div', {
						'class' : 'plupload_resize',
						style : 'display:none'
					}).adopt(a) ]);
					var innerli = [ filename, del, new Element('div', {
						'class' : 'plupload_file_status'
					}).set('text', '0%'), new Element('div', {
						'class' : 'plupload_file_size'
					}).set('text', file.size), new Element('div', {
						'class' : 'plupload_clearer'
					}) ];
					this.droplist.adopt(new Element('li', {
						id : file.id,
						'class' : 'plupload_delete'
					}).adopt(innerli));
				}
			}.bind(this));
		}.bind(this));

		// (3) ON FILE UPLOAD PROGRESS ACTION
		this.uploader.bind('UploadProgress', function (up, file) {
			var f = document.id(file.id);
			if (typeOf(f) !== 'null') {
				document.id(file.id).getElement('.plupload_file_status').set('text', file.percent + '%');
			}
		});

		this.uploader.bind('Error', function (up, err) {
			fconsole('Plupload Error:' + err.message);
		});

		this.uploader.bind('ChunkUploaded', function (up, file, response) {
			response = JSON.decode(response.response);
			if (typeOf(response) !== 'null') {
				if (response.error) {
					fconsole(response.error.message);
				}
			}
		});

		this.uploader.bind('FileUploaded', function (up, file, response) {
			response = JSON.decode(response.response);
			if (response.error) {
				alert(response.error);
				document.id(file.id).destroy();
				return;
			}
			var f = document.id(file.id);
			if (typeOf(f) === 'null') {
				fconsole('Filuploaded didnt find: ' + file.id);
				return;
			}
			document.id(file.id).getElement('.plupload_resize').show();
			var resizebutton = document.id(file.id).getElement('.plupload_resize').getElement('a');
			if (resizebutton) {
				resizebutton.href = response.uri;
				resizebutton.id = 'resizebutton_' + file.id;
				resizebutton.store('filepath', response.filepath);
			}
			this.widget.setImage(response.uri, response.filepath, file.params);
			
			// Stores the cropparams which we need to reload the crop widget in the correct state (rotation, zoom, loc etc)
			new Element('input', {
				'type' : 'hidden',
				name : this.options.elementName + '[crop][' + response.filepath + ']',
				'id' : 'coords_' + file.id,
				'value' : JSON.encode(file.params)
			}).inject(this.pluploadContainer, 'after');
			
			// Stores the actual crop image data retrieved from the canvas
			new Element('input', {
				type: 'hidden',
				name : this.options.elementName + '[cropdata][' + response.filepath + ']',
				'id' : 'data_' + file.id
			}).inject(this.pluploadContainer, 'after');
			
			// Stores the image id if > 1 fileupload
			var idvalue = [file.recordid, '0'].pick();
			new Element('input', {
				'type' : 'hidden',
				name : this.options.elementName + '[id][' + response.filepath + ']',
				'id' : 'id_' + file.id,
				'value' : idvalue
			}).inject(this.pluploadContainer, 'after');

			document.id(file.id).removeClass('plupload_file_action').addClass('plupload_done');
			
			
		}.bind(this));

		// (4) UPLOAD FILES FIRE STARTER
		c.getElement('.plupload_start').addEvent('click', function (e) {
			e.stop();
			this.uploader.start();
		}.bind(this));
		// (5) KICK-START PLUPLOAD
		this.uploader.init();
	},
	
	isImage: function (file) {
		if (typeOf(file.type) !== 'null') {
			return file.type === 'image';
		}
		var ext = file.name.split('.').getLast().toLowerCase();
		return ['jpg', 'jpeg', 'png', 'gif'].contains(ext);
	},

	pluploadRemoveFile : function (e, file) {
		e.stop();
		if (!confirm(Joomla.JText._('PLG_ELEMENT_FILEUPLOAD_CONFIRM_HARD_DELETE'))) {
			return;
		}
		var id = e.target.getParent().getParent().id.split('_').getLast();// alreadyuploaded_8_13
		var f = e.target.getParent().getParent().getElement('.plupload_file_name span').get('text');
		
		// Get a list of all of the uploaders files except the one to be deleted
		var newFiles = [];
		this.uploader.files.each(function (f) {
			if (f.id !== id) {
				newFiles.push(f);
			}
		});
		
		// Update the uploader's files with the new list.
		this.uploader.files = newFiles;
		
		// Send a request to delete the file from the server.
		new Request({
			url: '',
			data: {
				'option': 'com_fabrik',
				'format': 'raw',
				'task': 'plugin.pluginAjax',
				'plugin': 'fileupload',
				'method': 'ajax_deleteFile',
				'element_id': this.options.id,
				'file': f,
				'recordid': id
			}
		}).send();
		var li = e.target.getParent('.plupload_delete');
		li.destroy();

		// Remove hidden fields as well
		if (document.id('id_alreadyuploaded_' + this.options.id + '_' + id)) {
			document.id('id_alreadyuploaded_' + this.options.id + '_' + id).destroy();
		}
		if (document.id('coords_alreadyuploaded_' + this.options.id + '_' + id)) {
			document.id('coords_alreadyuploaded_' + this.options.id + '_' + id).destroy();
		}
	},

	pluploadResize : function (e) {
		e.stop();
		var a = e.target.getParent();
		this.widget.setImage(a.href, a.retrieve('filepath'));
	},

	onSubmit : function (form) {
		if (!this.allUploaded()) {
			alert(Joomla.JText._('PLG_ELEMENT_FILEUPLOAD_UPLOAD_ALL_FILES'));
			form.result = false;
			return false;
		}
		if (typeOf(this.widget) !== 'null') {
			this.widget.images.each(function (image, key) {
				key = key.split('\\').getLast();
				var f = document.getElements('input[name*=' + key + ']').filter(function (fld) {
					return fld.name.contains('[crop]');
				});
				f  = f.getLast();
				
				// $$$ rob - seems reloading ajax fileupload element in ajax form (e.g. from db join add record)
				// is producing odd effects where old fileupload object constains info to previously uploaded image?
				if (typeOf(f) !== 'null') {
					f.value = JSON.encode(image);
				}
			});
		}
		return true;
	},

	allUploaded : function () {
		var uploaded = true;
		if (this.uploader) {
			this.uploader.files.each(function (file) {
				if (file.loaded === 0) {
					uploaded = false;
				}
			}.bind(this));
		}
		return uploaded;
	}
});

var ImageWidget = new Class({

	initialize : function (canvas, opts) {
		this.canvas = canvas;

		this.imageDefault = {
			'rotation': 0,
			'scale': 100,
			'imagedim': {
				x: 200,
				y: 200,
				w: 400,
				h: 400
			},
			'cropdim': {
				x: 75,
				y: 25,
				w: 150,
				h: 50
			}
		};

		Object.append(this.imageDefault, opts);

		this.windowopts = {
			'id': this.canvas.id + '-mocha',
			'type': 'modal',
			content: this.canvas.getParent(),
			loadMethod: 'html',
			width: this.imageDefault.imagedim.w.toInt() + 20,
			height: this.imageDefault.imagedim.h.toInt() + 140,
			storeOnClose: true,
			createShowOverLay: false,
			crop: opts.crop,
			destroy: false,
			onClose : function () {
				this.storeActiveImageData();
			}.bind(this),
			onContentLoaded : function () {
				this.center();
			}
		};
		this.windowopts.title = opts.crop ? Joomla.JText._('PLG_ELEMENT_FILEUPLOAD_CROP_AND_SCALE') : Joomla.JText._('PLG_ELEMENT_FILEUPLOAD_PREVIEW');
		this.showWin();
		this.images = $H({});
		var parent = this;
		this.CANVAS = new FbCanvas({
			canvasElement: document.id(this.canvas.id),
			enableMouse: true,
			cacheCtxPos: false
		});

		this.CANVAS.layers.add(new Layer({
			id: 'bg-layer'
		}));
		this.CANVAS.layers.add(new Layer({
			id: 'image-layer'
		}));
		if (opts.crop) {
			this.CANVAS.layers.add(new Layer({
				id: 'overlay-layer'
			}));
			this.CANVAS.layers.add(new Layer({
				id: 'crop-layer'
			}));
		}
		var bg = new CanvasItem({
			id: 'bg',
			scale: 1,
			events: {
				onDraw: function (ctx) {
					if (typeOf(ctx) === 'null') {
						//return;
						ctx = this.CANVAS.ctx;
					}
					ctx.fillStyle = "#DFDFDF";
					ctx.fillRect(0, 0, this.imageDefault.imagedim.w / this.scale, this.imageDefault.imagedim.h / this.scale);
				}.bind(this)
			}
		});

		this.CANVAS.layers.get('bg-layer').add(bg);
		if (opts.crop) {
			this.overlay = new CanvasItem({
				id: 'overlay',
				events: {
					onDraw: function (ctx) {
						if (typeOf(ctx) === 'null') {
							ctx = this.CANVAS.ctx;
						}
						this.withinCrop = true;
						if (this.withinCrop) {
							var top = {
								x: 0,
								y: 0
							};
							var bottom = {
								x: this.imageDefault.imagedim.w,
								y: this.imageDefault.imagedim.h
							};
							ctx.fillStyle = "rgba(0, 0, 0, 0.3)";
							var cropper = this.cropperCanvas;
							ctx.fillRect(top.x, top.y, bottom.x, cropper.y - (cropper.h / 2));// top
							ctx.fillRect(top.x - (cropper.w / 2), top.y + cropper.y - (cropper.h / 2), top.x + cropper.x, cropper.h);// left
							ctx.fillRect(top.x + cropper.x + cropper.w - (cropper.w / 2), top.y + cropper.y - (cropper.h / 2), bottom.x, cropper.h);// right
							ctx.fillRect(top.x, top.y + (cropper.y + cropper.h) - (cropper.h / 2), bottom.x, bottom.y);// bottom
						}
					}.bind(this)
				}
			});
	
			this.CANVAS.layers.get('overlay-layer').add(this.overlay);
		}

		this.imgCanvas = this.makeImgCanvas();

		this.CANVAS.layers.get('image-layer').add(this.imgCanvas);

		this.cropperCanvas = this.makeCropperCanvas();
		if (opts.crop) {
			// add an item
			this.CANVAS.layers.get('crop-layer').add(this.cropperCanvas);
		}
		this.makeThread();
		this.watchZoom();
		this.watchRotate();
		this.watchClose();
		this.win.close();
	},
	
	/**
	 * Add or make active an image in the editor
	 * 
	 * @param  string  uri  Image URI
	 * @param  string  filepath  Path to file
	 * @param  object  params    Initial parameters
	 */
	
	setImage: function (uri, filepath, params) {
		this.activeFilePath = filepath;
		if (!this.images.has(filepath)) {
			
			// New image
			var img = Asset.image(uri, {
				onLoad: function () {
					
					var params = this.storeImageDimensions(filepath, img, params);
					this.img = params.img;
					this.setInterfaceDimensions(params);
					this.showWin();
					this.storeActiveImageData(filepath);
					this.win.close();
				}.bind(this)
			});
		} else {
			
			// Previously set up image
			params = this.images.get(filepath);
			this.img = params.img;
			this.setInterfaceDimensions(params);
			this.showWin();
		}
	},
	
	/**
	 * Set rotate, scale, image and crop values for a given image
	 * 
	 * @param   object  params  Image parameters
	 */
	setInterfaceDimensions: function (params) {
		if (this.scaleSlide) {
			this.scaleSlide.set(params.scale);
		}
		if (this.rotateSlide) {
			this.rotateSlide.set(params.rotation);
		}
		
		if (this.cropperCanvas && params.cropdim) {
			this.cropperCanvas.x = params.cropdim.x;
			this.cropperCanvas.y = params.cropdim.y;
			this.cropperCanvas.w = params.cropdim.w;
			this.cropperCanvas.h = params.cropdim.h;
		}
		this.imgCanvas.w = params.mainimagedim.w;
		this.imgCanvas.h = params.mainimagedim.h;
		this.imgCanvas.x = typeOf(params.imagedim) !== 'null' ? params.imagedim.x : 0;
		this.imgCanvas.y = typeOf(params.imagedim) !== 'null' ? params.imagedim.y : 0;
	},
	
	/**
	 * One time call to store initial image crop info in this.images
	 * 
	 * @param   string   filepath  Path to image
	 * @param   DOMnode  img       Image - just created 
	 * @param   params   object    Image parameters  
	 * 
	 * @return  object   Update image parameters
	 */
	
	storeImageDimensions: function (filepath, img, params) {
		img.inject(document.body).hide();
		params = params ? params : new CloneObject(this.imageDefault, true, []);
		var s = img.getDimensions(true);
		if (!params.imagedim) {
			params.mainimagedim = {};
		} else {
			params.mainimagedim = params.imagedim;
		}
		params.mainimagedim.w = s.width;
		params.mainimagedim.h = s.height;
		params.img = img;
		this.images.set(filepath, params);
		return params;
	},
	
	makeImgCanvas: function () {
		var parent = this;
		return new CanvasItem({
			id: 'imgtocrop',
			w: this.imageDefault.imagedim.w,
			h: this.imageDefault.imagedim.h,
			x: 200,
			y: 200,
			interactive: true,
			rotation: 0,
			scale: 1,
			offset: [ 0, 0 ],
			events: {
				onMousemove: function (x, y) {
					if (this.dragging) {
						var w = this.w * this.scale;
						var h = this.h * this.scale;
						this.x = x - this.offset[0] + w * 0.5;
						this.y = y - this.offset[1] + h * 0.5;
					}
				},
				onDraw : function (ctx) {
					ctx = parent.CANVAS.ctx;
					if (typeOf(parent.img) === 'null') {
						//console.log('no parent img', parent);
						return;
					}
						
					var w = this.w * this.scale;
					var h = this.h * this.scale;
					var x = this.x - w * 0.5;
					var y = this.y - h * 0.5;

					// standard Canvas rotation operation
					ctx.save();
					ctx.translate(this.x, this.y);
					ctx.rotate(this.rotation * Math.PI / 180);

					this.hover ? ctx.strokeStyle = '#f00' : ctx.strokeStyle = '#000'; // red/black
					ctx.strokeRect(w * -0.5, h * -0.5, w, h);
					if (typeOf(parent.img) !== 'null') {
						try {
							ctx.drawImage(parent.img, w * -0.5, h * -0.5, w, h);
						} catch (err) {
							// only show this for debugging as if we upload a pdf then we get shown lots of these errors.
							//fconsole(err, parent.img, w * -0.5, h * -0.5, w, h);
						}
					}
					ctx.restore();
					if (typeOf(parent.img) !== 'null' && parent.images.get(parent.activeFilePath)) {
						parent.images.get(parent.activeFilePath).imagedim = {
							x : this.x,
							y : this.y,
							w : w,
							h : h
						};

					}
					this.setDims(x, y, w, h);
				},

				onMousedown : function (x, y) {
					parent.CANVAS.setDrag(this);
					this.offset = [ x - this.dims[0], y - this.dims[1] ];
					this.dragging = true;
				},

				onMouseup : function () {
					parent.CANVAS.clearDrag();
					this.dragging = false;
				},

				onMouseover : function () {
					parent.overImg = true;
					document.body.style.cursor = "move";
				},

				onMouseout : function () {
					parent.overImg = false;
					if (!parent.overCrop) {
						document.body.style.cursor = "default";
					}
				}
			}
		});
	},
	
	makeCropperCanvas: function () {
		var parent = this;
		return new CanvasItem({
			id: 'item',
			x: 175,
			y: 175,
			w: 150,
			h: 50,
			interactive: true,
			offset: [ 0, 0 ],
			events: {
				onDraw: function (ctx) {
					ctx = parent.CANVAS.ctx;
					if (typeOf(ctx) === 'null') {
						return;
					}
					/*
					 * calculate dimensions locally because they are have to be translated
					 * in order to use translate and rotate with the desired effect:
					 * rotate the item around its visual center
					 */

					var w = this.w;
					var h = this.h;
					var x = this.x - w * 0.5;
					var y = this.y - h * 0.5;

					// standard Canvas rotation operation

					ctx.save();
					ctx.translate(this.x, this.y);

					this.hover ? ctx.strokeStyle = '#f00' : ctx.strokeStyle = '#000'; // red/black
					ctx.strokeRect(w * -0.5, h * -0.5, w, h);
					ctx.restore();

					/*
					 * used to determine the whether the mouse is over an item or not.
					 */

					if (typeOf(parent.img) !== 'null' && parent.images.get(parent.activeFilePath)) {
						parent.images.get(parent.activeFilePath).cropdim = {
							x : this.x,
							y : this.y,
							w : w,
							h : h
						};
					}
					this.setDims(x, y, w, h);
				},

				onMousedown : function (x, y) {
					parent.CANVAS.setDrag(this);
					this.offset = [ x - this.dims[0], y - this.dims[1] ];
					this.dragging = true;
					parent.overlay.withinCrop = true;
				},

				onMousemove : function (x, y) {
					document.body.style.cursor = "move";
					if (this.dragging) {
						var w = this.w;
						var h = this.h;
						this.x = x - this.offset[0] + w * 0.5;
						this.y = y - this.offset[1] + h * 0.5;
					}
				},

				onMouseup : function () {
					parent.CANVAS.clearDrag();
					this.dragging = false;
					parent.overlay.withinCrop = false;
				},

				onMouseover : function () {
					this.hover = true;
					parent.overCrop = true;

				},

				onMouseout : function () {
					if (!parent.overImg) {
						document.body.style.cursor = "default";
					}
					parent.overCrop = false;
					this.hover = false;
				}
			}
		});
	},
	
	makeThread: function () {
		this.CANVAS.addThread(new Thread({
			id : 'myThread',
			onExec : function () {
				if (typeOf(this.CANVAS) !== 'null') {
					if (typeOf(this.CANVAS.ctxEl) !== 'null') {
						this.CANVAS.clear().draw();
					}
				}
			}.bind(this)
		}));
	},
	
	/**
	 * watch the close button
	 */
	
	watchClose: function () {
		var w = document.id(this.windowopts.id);
		w.getElement('input[name=close-crop]').addEvent('click', function (e) {
			this.storeActiveImageData();
			this.win.close();
		}.bind(this));
	},
	
	/**
	 * Takes the current active image and creates cropped image data via a canvas element
	 * 
	 * @param   string  filepath  File path to image to crop. If blank use this.activeFilePath
	 */
	storeActiveImageData: function (filepath) {
		filepath = filepath ? filepath : this.activeFilePath;
		if (typeOf(filepath) === 'null') {
			return;
		}
		var x = this.cropperCanvas.x;
		var y = this.cropperCanvas.y;
		var w = this.cropperCanvas.w;
		var h = this.cropperCanvas.h;
		x = x - (w / 2);
		y = y - (h / 2);
		
		var win = document.id(this.windowopts.id);
		if (typeOf(win) === 'null') {
			console.log('storeActiveImageData no window found for ' + this.windowopts.id);
			return;
		}
		var canvas = win.getElement('canvas');
		
		var target = new Element('canvas', {'width': w + 'px', 'height': h + 'px' }).inject(document.body);
		var ctx = target.getContext('2d');
		
		var file = filepath.split('\\').getLast();
		var f = document.getElements('input[name*=' + file + ']').filter(function (fld) {
			return fld.name.contains('cropdata');
		});
		
		
		ctx.drawImage(canvas, x, y, w, h, 0, 0, w, h);
		f.set('value', target.toDataURL());
		target.destroy();
	},
	
	/**
	 * set up and wath the zoom slide and input field
	 */
	
	watchZoom: function () {
		var w = document.id(this.windowopts.id);
		if (!this.windowopts.crop) {
			return;
		}
		this.scaleField = w.getElement('input[name=zoom-val]');
		this.scaleSlide = new Slider(w.getElement('.fabrikslider-line'), w.getElement('.knob'), {
			range : [ 20, 300 ],
			onChange : function (pos) {
				this.imgCanvas.scale = pos / 100;
				if (typeOf(this.img) !== 'null') {
					try {
						this.images.get(this.activeFilePath).scale = pos;
					} catch (err) {
						fconsole('didnt get active file path:' + this.activeFilePath);
					}
				}
				this.scaleField.value = pos;
			}.bind(this)
		}).set(100);

		this.scaleField.addEvent('keyup', function (e) {
			this.scaleSlide.set(e.target.get('value'));
		}.bind(this));
	},
	
	/**
	 * set up and wath the rotate slide and input field
	 */
	
	watchRotate: function () {
		var w = document.id(this.windowopts.id);
		if (!this.windowopts.crop) {
			return;
		}
		var r = w.getElement('.rotate');
		this.rotateField = r.getElement('input[name=rotate-val]');
		this.rotateSlide = new Slider(r.getElement('.fabrikslider-line'), r.getElement('.knob'), {
			onChange : function (pos) {
				this.imgCanvas.rotation = pos;
				if (typeOf(this.img) !== 'null') {
					try {
						this.images.get(this.activeFilePath).rotation = pos;
					} catch (err) {
						fconsole('rorate err' + this.activeFilePath);
					}
				}
				this.rotateField.value = pos;
			}.bind(this),
			steps : 360
		}).set(0);
		this.rotateField.addEvent('keyup', function (e) {
			this.rotateSlide.set(e.target.get('value'));
		}.bind(this));
	},
	
	showWin : function () {
		this.win = Fabrik.getWindow(this.windowopts);
		if (typeOf(this.CANVAS) === 'null') {
			return;
		}
		if (typeOf(this.CANVAS.ctxEl) !== 'null') {
			this.CANVAS.ctxPos = document.id(this.CANVAS.ctxEl).getPosition();
		}
		
		if (typeOf(this.CANVAS.threads) !== 'null') {
			if (typeOf(this.CANVAS.threads.get('myThread')) !== 'null') {
				
				// Fixes issue where sometime canvas thread is not started/running so nothing is drawn
				this.CANVAS.threads.get('myThread').start();
			}
		}
		this.win.center();
	}
});