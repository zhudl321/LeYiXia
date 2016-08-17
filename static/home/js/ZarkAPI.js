(function(){

    var isFunction = function( fn ) {
        return !!fn && typeof fn != "string" && !fn.nodeName &&
            fn.constructor != Array && /function/i.test( fn + "" );
    }
    var buildURL = function(url, params){
        var tmp = url.split("?");
        var uri = tmp[0];
        var ps = null;
        if (tmp.length > 1) ps = tmp[1].split("&");
        var pnames = uri.match(/{\w+}/g);
        if (pnames != null) {
            for (var i=0; i<pnames.length; ++i){
                var pn = pnames[i];
                var ppn = pnames[i].match(/{(\w+)}/)[1];
                if (!params[ppn]) return null;
                else uri = uri.replace(pn, params[ppn]);
            }
        }
        if (!ps) return uri;
        var re_ps = [];
        for (var i=0; i<ps.length; ++i) {
            var tmp = ps[i].match(/{(\w+)}/);
            if (tmp===null) re_ps.push(ps[i]);
            else {
                var pn = tmp[0];
                var ppn = tmp[1];
                if (typeof params[ppn] !== 'undefined') re_ps.push(encodeURI(ps[i].replace(pn, params[ppn])));
            }
        }
        if (re_ps.length>0) return [uri, re_ps.join("&")].join("?");
        else return uri;
    }
    var jsc = (new Date).getTime();
    var buildTempFunction = function(cb){
        var jsonp = "jsonp" + jsc++;
        window[ jsonp ] = function(data){
            cb(data);
            // Garbage collect
            window[ jsonp ] = undefined;
            try{ delete window[ jsonp ]; } catch(e){}
        };
        return jsonp;
    }
    var sendScriptRequest = function(url){
        var head = document.getElementsByTagName("head")[0];
        var script = document.createElement("script");
        script.src = url;
        script.charset = 'utf-8';
        head.appendChild(script);
    }
    var formatParams = function(params) {
        if (isFunction(params.callback)) params.callback = buildTempFunction(params.callback);
        return params;
    }
    var send = function(url, params){
        var url = buildURL(url, params);
        if (url!=null) sendScriptRequest(url);
    }

    var namespace = 'ZarkAPI';
    var cp = 'callback={callback}';
    var testing = 'test={test}';
    var successcallback = 'successcallback={successcallback}';
    var apis = {
        isLogin: {url:'profile?action=islogin'},
        login: {url:'profile?action=login&email={email}&password={password}&rememberme={rememberme}'},
        register: {url:'register?username={username}&password={password}&email={email}&sex={sex}'},
        judgeJoke: {url:'judge?action=judge&Jokeid={Jokeid}&opinion={opinion}'},
        getUnjudgedJoke: {url:'judge?action=getJoke&currentjokeid={currentjokeid}'},
        isReported: {url:'report?action=isreported&item_type={item_type}&item_id={item_id}'},
        getComments: {url:'comment?action=getOneJokeComments&joke_id={joke_id}&page_num={page_num}&page={page}'},
        passSinsitive:{url:'sinsitive?action=pass&model_name={model_name}&model_id={model_id}'},
        addGatherVideo:{url:'gather-video?action=insert&video_uri={video_uri}&title={title}'},
        deleteGatherVideo:{url:'gather-video?action=delete&GatherVideoid={GatherVideoid}'},
        postAnonymityComment:{url:'anonymity-comment?action=postComment&gather_id={gather_id}&user_name={user_name}&content={content}&post_source={post_source}&gather_type={gather_type}'},
        getAnonymityComments:{url:'anonymity-comment?action=getComments&gather_id={gather_id}&page_num={page_num}&before_id={before_id}&gather_type={gather_type}'},
        increaseFollowWeixinCount:{url:'follow-weixin-count?action=incCount&Gatherid={gather_id}&weixin_id={weixin_id}&gather_type={gather_type}'},
        increaseSharePYQCount:{url:'share-pyq-count?action=incCount&Gatherid={gather_id}&action_type={action_type}&gather_type={gather_type}'},
        getMoreGathers:{url:'gather?action=getGathers&type={type}&page_num={page_num}&gather_type={gather_type}'},
        likeGather: {url: 'gather?action=like&Gatherid={Gatherid}'},
        postTopicComment: {url: 'topic-comment?action=insert&type={type}&Topicid={Topicid}&at_user_id={at_user_id}&content={content}'}
    };
    var base_uri = '/lengxiaohuaapi/';
    for (var name in apis) {
        if (apis[name].url.search(/\?/)!=-1) apis[name].url = base_uri + apis[name].url + '&' + cp + '&' + testing;
        else apis[name].url = base_uri + apis[name].url + '?' + cp + '&' + testing;
    }

    if (!window[namespace]) window[namespace] = {};
    var api_obj = window[namespace];
    for (var name in apis) {
        api_obj[name] = (function(url){
            return function(params){
                if (params === undefined) {
                    params={};
                };
                send(url, formatParams(params));
            };
        })(apis[name].url)
    }

})()
