//ZARK's common function
(function(){

    var namespace = 'ZarkF';
    if (!window[namespace]) window[namespace] = {};
    var zark_functions = window[namespace];

    zark_functions.user_datas = {}

    zark_functions.setUserDatas = function(key, value){
        zark_functions.user_datas[key] = value;
    }

    zark_functions.clearUserDatas = function(){
        zark_functions.user_datas = {}
    }

    zark_functions.getUserDatas = function( key )
    {
        if (typeof zark_functions.user_datas[key] !== 'undefined' ){
            return zark_functions.user_datas[key];
        }else{
            return '';
        }
    };

    zark_functions.login = function(callback){
        ZarkAPI.isLogin({callback:function(data){
            if (data.state == true) callback();
            else zark_functions.pleaseLogin(callback);
        }});
    };

    var tfc = (new Date).getTime();
    zark_functions.buildLocalFuncion = function(cb){
        var local_function = namespace + tfc++;
        window[ local_function ] = function(data){
            cb & cb(data);
            zark_functions.deleteLocalFunction(local_function);//只能运行一次
        };
        return local_function;
    };

    zark_functions.deleteLocalFunction = function(local_function){
        window[ local_function ] = undefined;
        try{ delete window[ local_function ]; } catch(e){}
    };

    //弹出一个登录框，当用户成功登录后执行callback
    //前条件： ZarkF.isLogin() == false
    var login_box_id = 'login_box';
    var jump_login_box_id = 'jump_login_form';
    var jump_register_box_id = 'jump_register_form';
    zark_functions.jump_login_callback = null;
    zark_functions.jump_register_callback = null;

    zark_functions.jumpLogin = function(){
        var post_data = {
            email:   $.trim($('#'+jump_login_box_id+' input[name=email]').val()),
            password:   $('#'+jump_login_box_id+' input[name=password]').val(),
            error:      $('#jump_error_tip'),
            rememberme: $('#'+jump_login_box_id+' input:checked[name=rememberme]').val()
        }
        
        $(this).blur();
        if (!post_data.email){
            post_data.error.html('请输入邮箱地址').show();
            return false;
        };
        if (!post_data.password){
            post_data.error.html('请输入密码').show();
            return false;
        };
        zark_functions.tryLogin(post_data, zark_functions.jump_login_callback);
    };

    zark_functions.jumpRegister = function(){
        var post_data = {
            username:   $.trim($('#'+jump_register_box_id+' input[name=username]').val()),
            password:   $('#'+jump_register_box_id+' input[name=password]').val(),
            password2:  $('#jump_reg_password2').val(),
            email:      $.trim($('#'+jump_register_box_id+' input[name=email]').val()),
            sex:        $('#'+jump_register_box_id+' select[name=sex]').val(),
            error:      $('#jump_reg_error_tip')
        }
        $(this).blur();
        if (!post_data.username){
            post_data.error.html('请输入用户名').show();
            return false;
        };
        if (!post_data.password){
            post_data.error.html('请输入密码').show();
            return false;
        };
        if (post_data.password !== post_data.password2){
            post_data.error.html('两次输入的密码不相同').show();
            return false;
        };
        if (!post_data.email){
            post_data.error.html('请输入邮箱地址').show();
            return false;
        };
        zark_functions.tryRegister(post_data, zark_functions.jump_register_callback);
    };

    zark_functions.pleaseLogin = function(callback){
        var success_callback = zark_functions.buildLocalFuncion(callback);
        zark_functions.jump_login_callback = callback;
        $('#'+jump_register_box_id).hide();
        $('#'+jump_login_box_id).show();
        $('#overlayer').show();
        $('#'+login_box_id).fadeIn();
        $('#jump_login_form input[name=username]').focus();
    };

    zark_functions.unpleaseLogin = function(callback){
        $('#'+login_box_id).hide();
        $('#overlayer').hide();
    };

    zark_functions.canclLogin = function(success_callback){
        zark_functions.deleteLocalFunction(success_callback);
        $('#'+login_box_id).remove();
    };

    zark_functions.loginSuccess = function(user_name, user_id, success_callback){
        $('#'+login_box_id).fadeOut('fast');
        $('#overlayer').hide();
        $('#right_sign_box').fadeOut('fast');
        $('#user_info').fadeIn();
        $('#user_info #home_link').html(user_name + ' 的主页').attr('href', '/user/'+ user_id);
        success_callback && success_callback();
        zark_functions.setUserDatas('myid', user_id);
        zark_functions.showForMe();
        zark_functions.showForOthers();
        zark_functions.showForLogin();
    };

    zark_functions.showForMe = function(){
        if (zark_functions.getUserDatas('myid')){
            $('[showforme][showforme!='+zark_functions.getUserDatas('myid')+']').css('display', 'none');
            $('[showforme][showforme='+zark_functions.getUserDatas('myid')+']').css('display', 'block');
        }else{
            $('[showforme]').css('display', 'none');
        };
    };

    zark_functions.showForOthers = function(){
        if (zark_functions.getUserDatas('myid')){
            $('[showforothers][showforothers='+zark_functions.getUserDatas('myid')+']').css('display', 'none');
            $('[showforothers][showforothers!='+zark_functions.getUserDatas('myid')+']').css('display', 'block');
        }else{
            $('[showforothers]').css('display', 'block');
        };
    };

    zark_functions.showForLogin = function(){
        if (zark_functions.getUserDatas('myid')){
            $('[showforlogin]').show();
            $('[showforunlogin]').hide();
        }else{
            $('[showforlogin]').hide();
            $('[showforunlogin]').show();
        };
    };

    zark_functions.tryLogin = function(post_data, success_callback){
        post_data.callback = function(data){
            if (data.is_login == true) {
                zark_functions.loginSuccess(data.user_name, data.user_id, success_callback);
            }else{
                if (typeof data.msg != 'undefined'){
                    post_data.error.html(data.msg).show();
                }else{
                    post_data.error.html('用户名或密码不对').show();
                }
            };
        };
        zark_functions.clearUserDatas();
        ZarkAPI.login(post_data);
    };

    zark_functions.tryRegister = function(post_data, success_callback){
        post_data.callback = function(data){
            if (data.success == true) {
                zark_functions.loginSuccess(data.user_name, data.user_id, success_callback);
            }else{
                post_data.error.html(data.msg).show();
            };
        };
        ZarkAPI.register(post_data);
    };

    //删除元素
    zark_functions.remove = function(rubbish_selector){
        $(rubbish_selector).remove();
    };

    //刷新当前页面
    zark_functions.refresh = function(){
        window.location.reload();
    };

    $('a, input').click(function(){
        $(this).blur();
        return true;
    });

    zark_functions.logout = function(){
        window.location.href = '/logout';
    };

})();
