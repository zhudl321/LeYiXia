(function($) {
    $(function(){
        if (!window['FX']) window['FX'] = {};
        FX = window['FX'];

        FX.FX_NAME         = 'fx';
        FX.PATH            = '';

        $("script").each(function() {
            var src = this.src;
            if (src.indexOf('?') !== -1){
                src = src.substr(0, src.indexOf('?'));
            }
            if( /zarkfx.js$/.test(src) ) {
                FX.PATH = src.replace(/zarkfx.js$/, "");
            }
        });

        FX.JS_PATH         = FX.PATH + 'jslib/';
        FX.CSS_PATH        = FX.PATH + 'css/';
        FX.SWF_PATH        = FX.PATH + 'swf/';
        FX.IMG_PATH        = FX.PATH + 'img/';
        FX.FRAME_PATH      = FX.PATH + 'frame/';
        FX.loaded_fx       = {};
        FX.loaded_scripts  = {};
        FX.loaded_frames   = {};

        FX.loaded_frames["jquery-" + $.fn.jquery] = jQuery;

        FX.loaded_css      = {};

        // get UUID
        FX.JSC = (new Date).getTime();
        FX.getJSC = function(){
            return 'zarkfx_'+FX.JSC++;
        };

        $.ajaxSetup({ cache: true });

        FX.getFrame = function(frame_name, cb){
            if (FX.loaded_frames[frame_name] === undefined){
                FX.loaded_frames[frame_name] = 'loading';

                $.ajax({
                    async:      false,
                    cache:      true,
                    dataType:   'script',
                    type:       'GET',
                    url:        FX.FRAME_PATH + frame_name + '.js'
                });

                FX.loaded_frames[frame_name] = jQuery.noConflict(true);

                cb && cb(FX.loaded_frames[frame_name]);

            }else if(FX.loaded_frames[frame_name] === 'loading'){
                setTimeout(function(){
                    FX.getFrame(frame_name, cb);
                }, 10);
            }else{
                cb && cb(FX.loaded_frames[frame_name]);
            };
        };

        // load js files and run cb function, bug only load once per file
        FX.getScript = function(scripts, cb){
            if (typeof scripts === 'string'){
                scripts = [scripts];
            };

            if (scripts.length === 0){
                cb && cb();
            }else{
                var first_script = $.trim(scripts[0]),
                    sub_scripts  = scripts.slice(1);
                if (FX.loaded_scripts[first_script] === undefined){
                    FX.loaded_scripts[first_script] = 'loading';
                    $.ajax({
                        async:      true,
                        cache:      true,
                        dataType:   'script',
                        type:       'GET',
                        url:        FX.JS_PATH + first_script + '.js',
                        success:    function(){
                            FX.loaded_scripts[first_script] = true;
                            FX.getScript(sub_scripts, cb);
                        }
                    });
                }else if(FX.loaded_scripts[first_script] === 'loading'){
                    setTimeout(function(){
                        FX.getScript(scripts, cb);
                    }, 10);
                }else{
                    FX.getScript(sub_scripts, cb);
                };
            };

        };

        FX.run = function(fx_name, cb, defaults, deps){
            var current_browser = (FX.detect.browser + FX.detect.version).toLowerCase();
            var ready = function(){
                //todo 这里有一个隐患, 如果某个fx的名称包含另一个fx的名称, 那么选择器会出错
                $('['+FX.FX_NAME+'*='+fx_name+']').each(function(){
                    var attrs_array = FX.getAllAttrs(this, fx_name);
                    if (attrs_array !== undefined){
                        var i = 0;
                        for ( ; i < attrs_array.length; i++){
                            var attrs = attrs_array[i],
                                jump = false,
                                j = 0;
                            // jump some browsers by notWork or onlyWork attributes
                            if (typeof attrs.notWork !== 'undefined'){
                                var no_browsers = FX.splitValue(attrs.notWork),
                                    no_browser;
                                for( ; j < no_browsers.length; j++){
                                    no_browser = no_browsers[j].toLowerCase();
                                    if (current_browser.indexOf(no_browser) === 0){
                                        jump = true; // jump this one
                                        break;
                                    }
                                };
                            };
                            if (jump === false && typeof attrs.onlyWork !== 'undefined'){
                                jump = true;
                                var only_browsers = FX.splitValue(attrs.onlyWork),
                                    only_browser;
                                for( ; j < only_browsers.length; j++){
                                    only_browser = only_browsers[j].toLowerCase();
                                    if (current_browser.indexOf(only_browser) === 0){
                                        jump = false;
                                        break;
                                    }
                                };
                            };
                            if (jump) continue;
                            // change attrs's data type like defaults
                            // don't use $.extend function, coz it will change the type of data of attrs
                            for(var k in defaults){
                                if (attrs[k] === undefined){
                                    attrs[k] = defaults[k];
                                }else{
                                    if(typeof(defaults[k]) === 'number') {
                                        if (attrs[k].indexOf('.') === -1){
                                            attrs[k] = parseInt(attrs[k]);
                                        }else{
                                            attrs[k] = parseFloat(attrs[k]);
                                        }
                                    }
                                    if(typeof(defaults[k]) === 'boolean') attrs[k] = attrs[k] === true;
                                };
                            };
                            cb && cb.call(this, attrs);
                            if (attrs.finished === 'show'){
                                $(this).show();
                            };
                            if (typeof attrs.onload !== 'undefined'){
                                eval(attrs.onload+'(this)');
                            };
                            if (typeof attrs.hoverClass !== 'undefined'){
                                $(this).mouseover(function(){
                                    $(this).addClass(attrs.hoverClass);
                                }).mouseout(function(){
                                    $(this).removeClass(attrs.hoverClass);
                                });
                            }
                        };
                    };
                });
            };
            if (deps !== undefined){
                FX.getScript(deps, ready);
            }else{
                ready();
            };
        };

        // load CSS dynamic
        FX.getCSS = function(css_url){
            if (FX.loaded_css[css_url] === undefined){
                if (document.createStyleSheet) {
                    document.createStyleSheet(css_url);
                }else{
                    var linkobj=$('<link type="text/css" rel="stylesheet" />');
                    linkobj.attr('href', css_url);
                    $('head').append(linkobj); 
                };
                FX.loaded_css[css_url] = true;
            };
        };

        // parse fx string, return a dict which made up of list.
        // and per list is made up of attrs.
        FX.parseFX = function(fx_string){

            var parseOne = function(s_fx){
                var re_strip = /^\s+|\s+$/g;
                var re_var_name = /^[A-z_][A-z_0-9]*$/;

                var res = {name: "", attrs: {}, remain: ""};
                var err = {idx: 0, msg: "", fx_name: "Unknown FX"};

                var idx = s_fx.search(/\S/);
                if(idx == -1) {
                    return res;
                };
                err.idx = idx;

                var idx2 = idx + s_fx.slice(idx).search(/[\s\[]/);
                if(idx2 < idx) {
                    idx2 = s_fx.length;
                };
                res.name = s_fx.slice(idx, idx2);

                if( !re_var_name.test(res.name) ) {
                    err.msg = "Illegal FX name.";
                    throw err;
                };
                err.fx_name = res.name;

                idx = s_fx.indexOf("[", idx2);
                if( (idx == -1) || (s_fx.slice(idx2, idx).search(/\S/) != -1) ) {
                    res.remain = s_fx.slice(idx2);
                    return res;
                };

                var state = 0, escaped, t;
                var key, value;
                for(idx+=1; idx<s_fx.length; idx+=1) {
                    switch(state) {
                        case 0: // init
                            key = "";
                            value = "";
                            err.idx = idx;
                            state = 1;
                            idx -= 1;
                            break;
                        case 1: // parse key
                            if( /[;\]]/.test(s_fx.charAt(idx)) ) {
                                key = key.replace(re_strip, "");
                                if(key != "") {
                                    if( !re_var_name.test(key) ) {
                                        err.msg = "Illegal FX attr name.";
                                        throw err;
                                    };
                                    res.attrs[key] = true;
                                };
                                if(s_fx.charAt(idx) == ";") {
                                    state = 0;
                                } else {
                                    state = "finished";
                                };
                            } else if(s_fx.charAt(idx) == "=") {
                                key = key.replace(re_strip, "");
                                if( !re_var_name.test(key) ) {
                                    err.msg = "Illegal FX attr name.";
                                    throw err;
                                };
                                err.idx = idx + 1;
                                state = 2;
                                escaped = 0;
                            } else {
                                key += s_fx.charAt(idx);
                            };
                            break;
                        case 2: // parse value
                            if(escaped == 0) {
                                if(s_fx.charAt(idx) == "&") {
                                    escaped = 1;
                                } else if(s_fx.charAt(idx) == ";") {
                                    res.attrs[key] = value;
                                    state = 0;
                                } else if(s_fx.charAt(idx) == "]") {
                                    res.attrs[key] = value;
                                    state = "finished";
                                } else {
                                    value += s_fx.charAt(idx);
                                }
                            } else if(escaped == 1) {
                                if(s_fx.charAt(idx) == "u") {
                                    t = "0000";
                                    escaped = 2;
                                } else {
                                    if(s_fx.charAt(idx) == "'") {
                                        value += '"';
                                    } else if(s_fx.charAt(idx) == '"') {
                                        value += "'";
                                    } else {
                                        value += s_fx.charAt(idx);
                                    };
                                    escaped = 0;
                                };
                            } else if(escaped == 2) { // "&uxxxx;"
                                if(s_fx.charAt(idx) == ";") {
                                    eval('t = "\\u' + t + '"');
                                    value += t;
                                    escaped = 0;
                                } else if( /[A-Fa-f0-9]/.test(s_fx.charAt(idx)) ) {
                                    t = t.slice(1) + s_fx.charAt(idx);
                                } else {
                                    err.idx = idx;
                                    err.msg = "Illegal character in hex environment.";
                                    throw err;
                                };
                            };
                            break;
                    };
                    if(state == "finished") {
                        break;
                    };
                };

                if(state != "finished") {
                    err.idx = idx;
                    err.msg = "Unexpected ending.";
                    throw err;
                };

                res.remain = s_fx.slice(idx + 1);

                return res;
            }; // end of parseOne

            var t, out, ret_fxs = {};
            t = fx_string;
            while(t != "") {
                try {
                    out = parseOne(t);
                    if(out.name != "") {
                        if (typeof ret_fxs[out.name] === 'undefined'){
                            ret_fxs[out.name] = [];
                        }
                        ret_fxs[out.name].push(out.attrs);
                    };
                    t = out.remain;
                } catch(err) {
                    if (err.idx !== undefined) {
                        alert( err.fx_name + ": " + (err.idx + fx_string.length - t.length) + ": " + err.msg );
                    } else {
                        throw err;
                    };
                    break;
                };
            };
            return ret_fxs;
        };

        FX.getAllAttrs = function(obj, fx_name){
            return FX.parseFX($(obj).attr(FX.FX_NAME))[fx_name];
        };

        // 此函数暂时不支持value里面包含逗号的情况, 需要改进
        FX.splitValue = function(value){ 
            var values = value.split(','),
                i = 0;
            for ( ; i < values.length; i++){
                values[i]  = $.trim(values[i]);
            };
            return values;
        };

        FX.hasFX = function(obj, fx_name){
            return typeof FX.parseFX($(obj).attr(FX.FX_NAME))[fx_name] !== 'undefined';
        };

        // detect client browser, version, and OS
        var ua = navigator.userAgent, pf = navigator.platform, ve = navigator.vendor;
        FX.detect = {
            _init: function () {
                this.browser = this._searchString(this._dataBrowser) || "unknown";
                this.version = this._searchVersion(ua)
                    || this._searchVersion(navigator.appVersion)
                    || "unknown";
                this.OS = this._searchString(this.dataOS) || "unknown";
            },
            _searchString: function (data) {
                for (var i=0;i<data.length;i++)	{
                    var dataString = data[i].s;
                    this.versionSearchString = data[i].versionSearch || data[i].i;
                    if (dataString) {
                        if (dataString.indexOf(data[i].k) != -1)
                            return data[i].i;
                    }else if (data[i].prop) return data[i].i;
                }
            },
            _searchVersion: function (dataString) {
                var index = dataString.indexOf(this.versionSearchString);
                if (index == -1) return;
                return parseFloat(dataString.substring(index+this.versionSearchString.length+1));
            },
            _dataBrowser: [
                { s: ua, k: "Chrome", i: "Chrome" },
                { s: ua, k: "OmniWeb", versionSearch: "OmniWeb/", i: "OmniWeb" },
                { s: ve, k: "Apple", i: "Safari", versionSearch: "Version" },
                { prop: window.opera, i: "Opera", versionSearch: "Version" },
                { s: ve, k: "iCab", i: "iCab" },
                { s: ve, k: "KDE", i: "Konqueror" },
                { s: ua, k: "Firefox", i: "Firefox" },
                { s: ve, k: "Camino", i: "Camino" },
                { s: ua, k: "Netscape", i: "Netscape" },
                { s: ua, k: "MSIE", i: "IE", versionSearch: "MSIE" },
                { s: ua, k: "Gecko", i: "Mozilla", versionSearch: "rv" },
                { s: ua, k: "Mozilla", i: "Netscape", versionSearch: "Mozilla" }
            ],
            dataOS : [
                { s: pf, k: "Win", i: "Windows" },
                { s: pf, k: "Mac", i: "Mac" },
                { s: ua, k: "iPhone", i: "iPhone/iPod" },
                { s: pf, k: "Linux", i: "Linux" }
            ]

        };
        FX.detect._init();

        // load and run all FXs
        $('['+FX.FX_NAME+']').each(function(){
            var fx_string = $(this).attr(FX.FX_NAME);
            for(var k in FX.parseFX(fx_string)){
                if(FX.loaded_fx[k] === undefined){
                    $.getScript(FX.PATH+'fx/'+k+'.js');
                    FX.loaded_fx[k] = true;
                };
            };
        });

    });
})(jQuery);
