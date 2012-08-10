// jQuery Cookie plugin
eval(function(p,a,c,k,e,d){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--){d[e(c)]=k[c]||e(c)}k=[function(e){return d[e]}];e=function(){return'\\w+'};c=1};while(c--){if(k[c]){p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c])}}return p}('o.5=B(9,b,2){6(h b!=\'E\'){2=2||{};6(b===n){b=\'\';2.3=-1}4 3=\'\';6(2.3&&(h 2.3==\'j\'||2.3.k)){4 7;6(h 2.3==\'j\'){7=w u();7.t(7.q()+(2.3*r*l*l*x))}m{7=2.3}3=\'; 3=\'+7.k()}4 8=2.8?\'; 8=\'+(2.8):\'\';4 a=2.a?\'; a=\'+(2.a):\'\';4 c=2.c?\'; c\':\'\';d.5=[9,\'=\',C(b),3,8,a,c].y(\'\')}m{4 e=n;6(d.5&&d.5!=\'\'){4 g=d.5.A(\';\');s(4 i=0;i<g.f;i++){4 5=o.z(g[i]);6(5.p(0,9.f+1)==(9+\'=\')){e=D(5.p(9.f+1));v}}}F e}};',42,42,'||options|expires|var|cookie|if|date|path|name|domain|value|secure|document|cookieValue|length|cookies|typeof||number|toUTCString|60|else|null|jQuery|substring|getTime|24|for|setTime|Date|break|new|1000|join|trim|split|function|encodeURIComponent|decodeURIComponent|undefined|return'.split('|'),0,{}))

// Borrowed from Bit.ly
var CopyClipboardButton={};CopyClipboardButton.getCopyText=function(a){var b=document.getElementById(a);try{return(b.value||b.innerText||b.textContent)}catch(c){return""}};

var theme_url = '/themes/default';
var sharurl;

jQuery(document).ready(function() {
	sharurl = new SharURL();
});

function SharURL() {
	var self = this;
	this.cancel = false;
	this.total_bits = 0;
	this.total_progress = 0;
	this.file_size_limit = '500MB';
	
	this.account = {
		init : function() {
			jQuery('table.urls tr td.details').each(function() {
				var num_files_show = 3;
				var td = jQuery(this);
				var files = jQuery('ul.files', this);
				var num_files = jQuery('li', files).size();
				
				if (num_files > num_files_show) {
					var num_more = num_files - num_files_show;
					var more_txt = num_more + ' more...';
					td.append('<a href="" class="more-less more">' + more_txt + '</span>');
					jQuery('.more', td).click(function() {
						if (jQuery(this).hasClass('more')) {
							jQuery('li', files).show();
							jQuery(this).text('less...')
							jQuery(this).removeClass('more').addClass('less');
						}
						else {
							jQuery('li', files).slice(num_files_show).hide();
							jQuery(this).text(more_txt)
							jQuery(this).removeClass('less').addClass('more');
						}
						
						return false;
					})
				}
			});
		}
	}
	
	this.init = function() {
		var form = jQuery('#header form');
		var email = jQuery('input.email', form);
		var passwd = jQuery('input[type=password].password', form);
		var passwd_txt = jQuery('input[type=text].password', form);

		self.title_as_value(email);

		passwd_txt.focus(function() {
			passwd_txt.hide();
			passwd.show().focus();
		});
		
		var blur = function() {
			if (passwd.val() == '') {
				passwd.hide();
				passwd_txt.show();
			}
		}

		blur();
		passwd.blur(blur);
		
		self.account.init();
	}
	
	this.title_as_value = function(field) {
		var title = field.attr('title');
		var form = field.parents('form').eq(0);

		form.submit(function() {
			if (field.val() == title) {
				field.val('');
			}
		});
		
		var blur = function() {
			if (field.val() == '') {
				field.val(title).addClass('grayed');
			}
		}
		
		var focus = function() {
			if (title == field.val()) {
				field.val('').removeClass('grayed');
			}
		}
		
		blur();
		
		field.focus(focus).blur(blur);
	}
	
	this.api = {
		upload : {
			token : '',
			
			start : function(file_count, callback) {
				jQuery.getJSON('/upload/start/', { file_count: file_count, size: self.total_bits }, callback);
			}

			/*
			finish : function(callback) {
				jQuery.getJSON('/upload/finish/', callback);
			}
			*/
		},
		
		toJSON : function(data) {
			return eval("(" + data + ")");
		}
	}
	
	this.handler = {
		fileDialogStart : function() {
			self.total_bits = 0;
			self.total_progress = 0;
			self.cancel = false;
			self.html.setup();
			self.html.tp_start();
		},
		
		fileQueued : function(file) {
			self.total_bits += file.size;
		},
		
		fileQueueError : function(file, code, message) {
			if (code == -110) {
				self.html.show_error('The file <b>"' + file.name + '"</b> exceeds the individual file size limit of ' + self.file_size_limit + '.');
			}
			else if (code == -120) {
				self.html.show_error('The file <b>"' + file.name + '"</b> is either empty, exceeds the individual file size limit of ' + self.file_size_limit + ', or cannot be accessed.');
			}
			else {
				self.html.show_error('Error queing ' + file.name + ': ' + message + code);
			}
		},
		
		fileDialogComplete : function(selected, queued, inqueued) {
			if (queued > 0) {
				self.api.upload.start(queued, function(data) {
					if (data.success == '0') {
						self.html.show_error(data.error_msg);
						return;
					}
					
					self.swfu.addPostParam('token', data.token);
					//self.swfu.setButtonDisabled(true);
					self.api.upload.token = data.token;
					self.html.start();
				});
			}
		},
		
		uploadStart : function(file) {
			self.html.fp_start(file);
		},
		
		uploadProgress : function(file, complete, total) {
			var total_complete = self.total_progress + complete;
			self.html.tp_update(total_complete);
			self.html.fp_update(file, complete);
		},
		
		uploadError : function(file, code, message) {
			if (self.cancel) return;
			self.html.show_error('Error uploading ' + file.name + ': ' + message);
		},
		
		uploadSuccess : function(file, data) {
			if (self.cancel) return;
			
			data = self.api.toJSON(data);
			if (!data.success && data.error_msg) {
				self.html.show_error(data.error_msg);
			}
			
			if (self.swfu.getStats().files_queued == 0) {
				self.swfu.setButtonDisabled(false);
				self.html.tp_complete();
				self.html.show_url();
			}
			else {
				self.swfu.startUpload();
			}
		},
		
		uploadComplete : function(file) {
			if (self.cancel) return;
			
			self.total_progress += file.size;
			self.html.tp_update(self.total_progress);
			self.html.fp_complete(file);
		}
	}
	
	this.html = {
		tp_width : 325,
		
		setup : function() {
			if (!jQuery('.file-upload .uploading').get(0)) {
				jQuery('\
					<div class="output uploading" style="display: none;">\
						<img src="' + theme_url + '/img/loading.gif" alt="" />\
						<div class="controls">\
							<p class="status"><span class="desc"></span> <span class="percent"></span> <span class="filename"></span></p>\
							<div class="progress"><div></div></div>\
							<a href="" class="button button-cancel">Cancel</a>\
						</div>\
					</div>\
				').appendTo('.file-upload');
				
				jQuery('.uploading .button-cancel').click(function() {
					if (self.cancel) return false;
					
					self.cancel = true;
					self.swfu.stopUpload();
					while (self.swfu.getStats().files_queued != 0) {
						self.swfu.cancelUpload(null, false);
					}
					jQuery('.uploading').fadeOut();
					return false;
				});
			}
			
			if (!jQuery('.file-upload .ready').get(0)) {
				jQuery('\
					<div class="output ready" style="display: none;">\
						<p class="status">Your SharURL is ready!</p>\
						<div class="controls">\
							<input type="text" class="url" id="short_url" />\
							<object width="61" height="36">\
							<param name="movie" value="' + theme_url + '/swf/clipboard.swf?v=3.0"/>\
							<param name="FlashVars" value="copyTextContainerId=short_url&fontSize=14&fontFace=Helvetica&fontColor=#000000&imageUrl=' + theme_url + '/img/home/button-copy.gif&copyText="/>\
							<param name="quality" value="high"/>\
							<param name="menu" value="false"/>\
							<param name="wmode" value="transparent"/>\
							<embed width="61" height="36" type="application/x-shockwave-flash" src="' + theme_url + '/swf/clipboard.swf?v=3.0" flashvars="copyTextContainerId=short_url&fontSize=14&fontFace=Helvetica&fontColor=#000000&imageUrl=' + theme_url + '/img/home/button-copy.gif&copyText=" wmode="transparent"/>\
							</object>\
						</div>\
					</div>\
			   ').appendTo('.file-upload');
			}
			
			jQuery('.uploading, .ready').hide();
			
			jQuery('.upload-error').hide();
		},
		
		start : function() {
			jQuery('.uploading .status .desc').html('Initializing...');
			jQuery('.uploading .filename, .uploading .percent').html('');
			jQuery('.uploading').fadeIn(function() {
				self.swfu.startUpload();
			});
		},
		
		tp_start : function(file) {
			jQuery('.progress div').css('width', '0px');
		},
		
		tp_update : function(bits) {
			var width = Math.ceil(self.html.tp_width * (bits / self.total_bits));
			jQuery('.progress div').css('width', width + 'px');
		},
		
		tp_complete : function() {
			jQuery('.progress div').css('width', self.html.tp_width + 'px');
			jQuery('.uploading .filename, .uploading .percent').hide();
		},
		
		fp_start : function(file) {
			jQuery('.uploading .status .desc').html('Uploading files...');
			jQuery('.uploading .filename').html(file.name).show();
			jQuery('.uploading .percent').html('0%').show();
		},
		
		fp_update : function(file, bits) {
			var percent = Math.ceil(bits / file.size * 100);
			jQuery('.uploading .percent').html(percent + '%');
		},
		
		fp_complete : function(file) {
			jQuery('.uploading .percent').html('100%');
		},

		show_url : function() {
			jQuery.post('/url/', { token : self.api.upload.token }, function(data) {
				jQuery('.uploading').fadeOut();
				jQuery('.ready').fadeIn();
				jQuery('.ready .url').val(data).click(function() {
					jQuery(this).select();
				});
			});
		},
		
		show_error : function(message) {
			if (!jQuery('.upload-error').get(0)) {
				jQuery('<div class="upload-error"></div>').appendTo('.file-upload');
			}

			jQuery('.uploading, .ready').hide();
			jQuery('.upload-error').hide().html(message).fadeIn();
		}
	}
	
	this.copy_clipboard = function(text) {
        var swf = theme_url + '/swf/clipboard.swf';

        if (!jQuery('#swf-clipboard').get(0)) {
			jQuery('body').eq(0).append('<div id="swf-clipboard"></div>');
		}

		jQuery('#swf-clipboard')
			.html('<embed src="' + swf + '" flashvars="clipboard=' + encodeURIComponent(text) + '" width="0" height="0" type="application/x-shockwave-flash"></embed>');
	}

	if (jQuery('#upload-button').get(0)) {
		this.swfu = new SWFUpload({
			debug: false,
			
			upload_url: '/upload/file/',
			flash_url: theme_url + '/swf/swfupload.swf',
			file_size_limit: self.file_size_limit,
			
			button_placeholder_id: "upload-button",
			button_image_url: theme_url + '/img/home/button.jpg',
			button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
			button_width: 404,
			button_height: 148,
		   
			file_dialog_start_handler : self.handler.fileDialogStart,
			file_queued_handler : self.handler.fileQueued,
			file_queue_error_handler : self.handler.fileQueueError,
			file_dialog_complete_handler : self.handler.fileDialogComplete,
			upload_start_handler : self.handler.uploadStart,
			upload_progress_handler : self.handler.uploadProgress,
			upload_error_handler : self.handler.uploadError,
			upload_success_handler : self.handler.uploadSuccess,
			upload_complete_handler : self.handler.uploadComplete
		});
	}
	
	self.init();
	
	/*
	self.api.upload.token = '64113b933c44f1718cb0df942edef03f';
	self.html.setup();
	self.html.start();
	self.html.show_url();
	*/
}
