// Avoid `console` errors in browsers that lack a console.
(function() {
    var method;
    var noop = function noop() {};
    var methods = [
        'assert', 'clear', 'count', 'debug', 'dir', 'dirxml', 'error',
        'exception', 'group', 'groupCollapsed', 'groupEnd', 'info', 'log',
        'markTimeline', 'profile', 'profileEnd', 'table', 'time', 'timeEnd',
        'timeStamp', 'trace', 'warn'
    ];
    var length = methods.length;
    var console = (window.console = window.console || {});

    while (length--) {
        method = methods[length];

        // Only stub undefined methods.
        if (!console[method]) {
            console[method] = noop;
        }
    }
}());

// Place any jQuery/helper plugins in here.

/**
 *  FileReader : FileAPI polyfill using Flash, jQuery and jQuery UI
 *  https://github.com/Jahdrien/FileReader
 */
//(function(e){var t,n=e.Callbacks("once unique memory"),r=0,i=null;e.fn.fileReader=function(r){var i;return r=e.extend({id:"fileReaderSWFObject",multiple:null,accept:null,label:null,extensions:null,filereader:"files/filereader.swf",expressInstall:null,debugMode:!1,callback:!1},r),i=this,n.add(function(){return t(i,r)}),e.isFunction(r.callback)&&n.add(r.callback),FileAPIProxy.ready||FileAPIProxy.init(r),this},t=function(t,n){return t.each(function(t,s){s=e(s);var o=s.attr("id");o||(o="flashFileInput"+r,s.attr("id",o),r++),n.multiple=n.multiple===null?!!s.attr("multiple"):!!n.multiple,n.accept=n.accept===null?s.attr("accept"):n.multiple,FileAPIProxy.inputs[o]=s,FileAPIProxy.swfObject.add(o,n.multiple,n.accept,n.label,n.extensions),s.css("z-index",0).mouseover(function(e){o!==i&&(e=e||window.event,i=o,FileAPIProxy.swfObject.mouseover(o),FileAPIProxy.container.height(s.outerHeight()).width(s.outerWidth()).position({of:s}))}).click(function(e){return e.preventDefault(),e.stopPropagation(),e.stopImmediatePropagation(),!1})})},window.FileAPIProxy={ready:!1,init:function(t){var r=this;this.debugMode=t.debugMode,this.container=e("<div>").attr("id",t.id).wrap("<div>").parent().css({position:"fixed",width:"1px",height:"1px",display:"inline-block",background:"transparent","z-index":99999}).on("mouseover mouseout mousedown mouseup",function(t){i&&e("#"+i).trigger(t.type)}).appendTo("body"),swfobject.embedSWF(t.filereader,t.id,"100%","100%","10",t.expressInstall,{debugMode:t.debugMode?!0:""},{wmode:"transparent",allowScriptAccess:"sameDomain"},{},function(t){r.swfObject=t.ref,e(r.swfObject).css({display:"block",outline:0}).attr("tabindex",0),r.ready&&n.fire(),r.ready=t.success})},swfObject:null,container:null,inputs:{},readers:{},onFileInputEvent:function(e){var t;this.debugMode&&console.info("FileInput Event ",e.type,e),e.target in this.inputs&&(t=this.inputs[e.target],e.target=t[0],e.type==="change"&&(e.files=new FileList(e.files),e.target={files:e.files}),t.trigger(e)),window.focus()},onFileReaderEvent:function(e){var t;this.debugMode&&console.info("FileReader Event ",e.type,e,e.target in this.readers),e.target in this.readers&&(t=this.readers[e.target],e.target=t,t._handleFlashEvent.call(t,e))},onFileReaderError:function(e){this.debugMode&&console.log(e)},onSWFReady:function(){return this.container.css({position:"absolute"}),this.ready&&n.fire(),this.ready=!0,!0}},window.FileReader=function(){this.EMPTY=0,this.LOADING=1,this.DONE=2,this.readyState=0,this.result=null,this.error=null,this.onloadstart=null,this.onprogress=null,this.onload=null,this.onabort=null,this.onerror=null,this.onloadend=null,this._callbacks={loadstart:e.Callbacks("unique"),progress:e.Callbacks("unique"),abort:e.Callbacks("unique"),error:e.Callbacks("unique"),load:e.Callbacks("unique"),loadend:e.Callbacks("unique")},this._id=null},window.FileReader.prototype={readAsBinaryString:function(e){this._start(e),FileAPIProxy.swfObject.read(e.input,e.name,"readAsBinaryString")},readAsText:function(e){this._start(e),FileAPIProxy.swfObject.read(e.input,e.name,"readAsText")},readAsDataURL:function(e){this._start(e),FileAPIProxy.swfObject.read(e.input,e.name,"readAsDataURL")},readAsArrayBuffer:function(){throw"Whoops FileReader.readAsArrayBuffer is unimplemented"},abort:function(){this.result=null;if(this.readyState===this.EMPTY||this.readyState===this.DONE)return;FileAPIProxy.swfObject.abort(this._id)},addEventListener:function(e,t){e in this._callbacks&&this._callbacks[e].add(t)},removeEventListener:function(e,t){e in this._callbacks&&this._callbacks[e].remove(t)},dispatchEvent:function(t){var n;return t.target=this,t.type in this._callbacks&&(n=this["on"+t.type],e.isFunction(n)&&n(t),this._callbacks[t.type].fire(t)),!0},_register:function(e){this._id=e.input+"."+e.name,FileAPIProxy.readers[this._id]=this},_start:function(e){this._register(e);if(this.readyState===this.LOADING)throw{type:"InvalidStateError",code:11,message:"The object is in an invalid state."}},_handleFlashEvent:function(e){switch(e.type){case"loadstart":this.readyState=this.LOADING;break;case"loadend":this.readyState=this.DONE;break;case"load":this.readyState=this.DONE,this.result=FileAPIProxy.swfObject.result(this._id);break;case"error":this.result=null,this.error={name:"NotReadableError",message:"The File cannot be read!"}}this.dispatchEvent(new FileReaderEvent(e))}},FileReaderEvent=function(e){this.initEvent(e)},FileReaderEvent.prototype={initEvent:function(t){e.extend(this,{type:null,target:null,currentTarget:null,eventPhase:2,bubbles:!1,cancelable:!1,defaultPrevented:!1,isTrusted:!1,timeStamp:(new Date).getTime()},t)},stopPropagation:function(){},stopImmediatePropagation:function(){},preventDefault:function(){}},FileList=function(e){var t;if(e){for(t=0;t<e.length;t++)this[t]=e[t];this.length=e.length}else this.length=0},FileList.prototype={item:function(e){return e in this?this[e]:null}}})(jQuery);
if (!window.File && !window.FileReader && !window.FileList && !window.Blob) {

    (function( $ ){
        var readyCallbacks = $.Callbacks('once unique memory'),
        inputsCount = 0,
        currentTarget = null;
        
        /**
        * JQuery Plugin
        */
        $.fn.fileReader = function( options ) {  
            options = $.extend({
                id              : 'fileReaderSWFObject', // ID for the created swf object container,
                multiple        : null,
                accept          : null,
                label           : null,
                extensions      : null,
                filereader      : 'files/filereader.swf', // The path to the filereader swf file
                expressInstall  : null, // The path to the express install swf file
                debugMode       : false,
                callback        : false // Callback function when Filereader is ready
            }, options);
            
            var self = this;
            readyCallbacks.add(function() {
                return main(self, options);
            });
            if ($.isFunction(options.callback)) readyCallbacks.add(options.callback);
            
            if (!FileAPIProxy.ready) {
                FileAPIProxy.init(options);
            }
            return this;
        };
        
        /**
        * Plugin callback
        *     adds an input to registry
        */
        var main = function(el, options) {
            return el.each(function(i, input) {
                input = $(input);
                var id = input.attr('id');
                if (!id) {
                    id = 'flashFileInput' + inputsCount;
                    input.attr('id', id);
                    inputsCount++;
                }
                options.multiple = !!(options.multiple === null ? input.attr('multiple') : options.multiple);
                options.accept = options.accept === null ? input.attr('accept') : options.multiple;
                
                FileAPIProxy.inputs[id] = input;
                FileAPIProxy.swfObject.add(id, options.multiple, options.accept, options.label, options.extensions);
                
                input.css('z-index', 0)
                    .mouseover(function (e) {
                        if (id !== currentTarget) {
                            e = e || window.event;
                            currentTarget = id;
                            FileAPIProxy.swfObject.mouseover(id);
                            FileAPIProxy.container
                                .height(input.outerHeight())
                                .width(input.outerWidth())
                                .position({of:input});
                        }
                    })
                    .click(function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        e.stopImmediatePropagation();
                        return false;
                    });
            });
        };
        
        /**
        * Flash FileReader Proxy
        */
        window.FileAPIProxy = {
            ready: false,
            init: function(o) {
                var self = this;
                this.debugMode = o.debugMode;
                this.container = $('<div>').attr('id', o.id)
                    .wrap('<div>')
                    .parent()
                    .css({
                        position:'fixed',
                        // top:'0px',
                        width:'1px',
                        height:'1px',
                        display:'inline-block',
                        background:'transparent',
                        'z-index':99999
                    })
                    // Hands over mouse events to original input for css styles
                    .on('mouseover mouseout mousedown mouseup', function(evt) {
                        if(currentTarget) $('#' + currentTarget).trigger(evt.type);
                    })
                    .appendTo('body');
                
                swfobject.embedSWF(o.filereader, o.id, '100%', '100%', '10', o.expressInstall, {debugMode: o.debugMode ? true : ''}, {'wmode':'transparent','allowScriptAccess':'sameDomain'}, {}, function(e) {
                    self.swfObject = e.ref;
                    $(self.swfObject)
                        .css({
                            display: 'block',
                            outline: 0
                        })
                        .attr('tabindex', 0);
                    if (self.ready) {
                        readyCallbacks.fire();
                    }
                    self.ready = e.success && typeof e.ref.add === "function";
                });
            },
            swfObject: null,
            container: null,
            // Inputs Registry
            inputs: {},
            // Readers Registry
            readers: {},
            // Receives FileInput events
            onFileInputEvent: function(evt) {
                if (this.debugMode) console.info('FileInput Event ', evt.type, evt);
                if (evt.target in this.inputs) {
                    var el = this.inputs[evt.target];
                    evt.target = el[0];
                    if( evt.type === 'change') {
                        evt.files = new FileList(evt.files);
                        evt.target = {files: evt.files};
                    }
                    el.trigger(evt);
                }
                window.focus();
            },
            // Receives FileReader ProgressEvents
            onFileReaderEvent: function(evt) {
                if (this.debugMode) console.info('FileReader Event ', evt.type, evt, evt.target in this.readers);
                if (evt.target in this.readers) {
                    var reader = this.readers[evt.target];
                    evt.target = reader;
                    reader._handleFlashEvent.call(reader, evt);
                }
            },
            // Receives flash FileReader Error Events
            onFileReaderError: function(error) {
                if (this.debugMode) console.log(error);
            },
            onSWFReady: function() {
                        this.container.css({position: 'absolute'});
                        this.ready = typeof this.swfObject.add === "function";
                if (this.ready) {
                    readyCallbacks.fire();
                }
                
                return true;
            }
        };
        
        
        /**
        * Add FileReader to the window object
        */
        window.FileReader = function () {
            // states
            this.EMPTY = 0;
            this.LOADING = 1;
            this.DONE = 2;

            this.readyState = 0;

            // File or Blob data
            this.result = null;

            this.error = null;

            // event handler attributes
            this.onloadstart = null;
            this.onprogress = null;
            this.onload = null;
            this.onabort = null;
            this.onerror = null;
            this.onloadend = null;
            
            // Event Listeners handling using JQuery Callbacks
            this._callbacks = {
                loadstart : $.Callbacks( "unique" ),
                progress  : $.Callbacks( "unique" ),
                abort     : $.Callbacks( "unique" ),
                error     : $.Callbacks( "unique" ),
                load      : $.Callbacks( "unique" ),
                loadend   : $.Callbacks( "unique" )
            };
            
            // Custom properties
            this._id = null;
        };
        
        window.FileReader.prototype = {
            // async read methods
            readAsBinaryString: function (file) {
                this._start(file);
                FileAPIProxy.swfObject.read(file.input, file.name, 'readAsBinaryString');
            },
            readAsText: function (file, encoding) {
                this._start(file);
                FileAPIProxy.swfObject.read(file.input, file.name, 'readAsText');
            },
            readAsDataURL: function (file) {
                this._start(file);
                FileAPIProxy.swfObject.read(file.input, file.name, 'readAsDataURL');
            },
            readAsArrayBuffer: function(file){
                throw("Whoops FileReader.readAsArrayBuffer is unimplemented");
            },
            
            abort: function () {
                this.result = null;
                if (this.readyState === this.EMPTY || this.readyState === this.DONE) return;
                FileAPIProxy.swfObject.abort(this._id);
            },
            
            // Event Target interface
            addEventListener: function (type, listener) {
                if (type in this._callbacks) this._callbacks[type].add(listener);
            },
            removeEventListener: function (type, listener) {
                if (type in this._callbacks) this._callbacks[type].remove(listener);
            },
            dispatchEvent: function (event) {
                event.target = this;
                if (event.type in this._callbacks) {
                    var fn = this['on' + event.type];
                    if ($.isFunction(fn)) fn(event);
                    this._callbacks[event.type].fire(event);
                }
                return true;
            },
            
            // Custom private methods
            
            // Registers FileReader instance for flash callbacks
            _register: function(file) {
                this._id = file.input + '.' + file.name;
                FileAPIProxy.readers[this._id] = this;
            },
            _start: function(file) {
                this._register(file);
                if (this.readyState === this.LOADING) throw {type: 'InvalidStateError', code: 11, message: 'The object is in an invalid state.'};
            },
            _handleFlashEvent: function(evt) {
                switch (evt.type) {
                    case 'loadstart':
                        this.readyState = this.LOADING;
                        break;
                    case 'loadend':
                        this.readyState = this.DONE;
                        break;
                    case 'load':
                        this.readyState = this.DONE;
                        this.result = FileAPIProxy.swfObject.result(this._id);
                        break;
                    case 'error':
                        this.result = null;
                        this.error = {
                            name: 'NotReadableError',
                            message: 'The File cannot be read!'
                        };
                }
                this.dispatchEvent(new FileReaderEvent(evt));
            }
        };
        
        /**
        * FileReader ProgressEvent implenting Event interface
        */
        FileReaderEvent = function (e) {
            this.initEvent(e);
        };

        FileReaderEvent.prototype = {
            initEvent: function (event) {
                $.extend(this, {
                    type: null,
                    target: null,
                    currentTarget: null,
                
                    eventPhase: 2,

                    bubbles: false,
                    cancelable: false,
             
                    defaultPrevented: false,

                    isTrusted: false,
                    timeStamp: new Date().getTime()
                }, event);
            },
            stopPropagation: function (){
            },
            stopImmediatePropagation: function (){
            },
            preventDefault: function (){
            }
        };
        
        /**
        * FileList interface (Object with item function)
        */
        FileList = function(array) {
            if (array) {
                for (var i = 0; i < array.length; i++) {
                    this[i] = array[i];
                }
                this.length = array.length;
            } else {
                this.length = 0;
            }
        };
        
        FileList.prototype = {
            item: function(index) {
                if (index in this) return this[index];
                return null;
            }
        };
        
    })( jQuery );
}