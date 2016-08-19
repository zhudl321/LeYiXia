function changeLoginBox(box_name){

    var setFocus = function(){
        if (box_name === 'login'){
            $('#login_form input[name=email]').focus();
        }else if(box_name === 'register'){
            $('#signup_form input[name=email]').focus();
        };
    };
    if($('#login_form').css('display') === 'none' && $('#signup_form').css('display') === 'none'){
        if (box_name === 'login' && $('#login_form').css('display') === 'none'){
            $('#login_form').fadeIn(setFocus);
        }else if(box_name === 'register' && $('#signup_form').css('display') === 'none'){
            $('#signup_form').fadeIn(setFocus);
        };
    }else if ($('#login_form').css('display') === 'none' && $('#signup_form').css('display') !== 'none' && box_name === 'login'){
        $('#signup_form').fadeOut('fast', function(){
            $('#login_form').fadeIn(setFocus);
        });
    }else if ($('#signup_form').css('display') === 'none' && $('#login_form').css('display') !== 'none' && box_name === 'register'){
        $('#login_form').fadeOut('fast', function(){
            $('#signup_form').fadeIn(setFocus);
        });
    };

};

function rightRegister(){
    var post_data = {
        username:       $('#signup_form input[name=username]').val(),
        password:       $('#signup_form input[name=password]').val(),
        password2:      $('#signup_form input[name=password2]').val(),
        email:          $('#signup_form input[name=email]').val(),
        sex:            $('#signup_form select[name=sex]').val(),
        error:          $('#reg_error_tip')
    };


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
    ZarkF.tryRegister(post_data);

};

function expendContent(joke_id){
    if ($('#joke_content_' +joke_id ).css('display') === 'none'){
        $('#joke_content_' +joke_id ).show();
        $('#joke_summary_' +joke_id ).hide();
        $('#joke_expend_' +joke_id ).html('收起');
    }else{
        $('#joke_content_' +joke_id ).hide();
        $('#joke_summary_' +joke_id ).show();
        $('#joke_expend_' +joke_id ).html('展开');
    };
};

function postComment(joke_id, type){
    // gather的comment，type=gather， 否则不传type
    // type=gather时， joke_id就表示gather_id

    ZarkF.login(function(){
        var post_url, action;
        if (typeof type !== 'undefined' && type === 'gather'){
            post_url = '/lengxiaohuaapi/gather-comment';
            action   = 'postComment';
        }else{
            post_url = '/lengxiaohuaapi/comment';
            action   = 'post_comment';
        }

        var data = {
            content:        $.trim($('#comment_content_' + joke_id).val()),
            action:         action,
            joke_id:        joke_id, //仅在type为空时有用
            gather_id:      joke_id  //仅在type=gather时有用
        };
        if (!data.content){
            return false;
        };
        $.ajax({
            data:       data,
            dataType:   'json',
            type:       'POST',
            url:        post_url,
            success:    function(response_data){
                if (response_data.success) {
                    $('#post_comment_' + joke_id + ' div[name=post_btn] a').html('继续发表');
                    $('#post_comment_' + joke_id + ' div[name=post_btn]').show();
                    $('#comment_content_' + joke_id).val('');
                    $('#no_comment_tip_' + joke_id).hide();
                    var current_count = parseInt($('#comment_count_' + joke_id).val()) + 1;
                    $('#comment_count_' + joke_id).val(current_count);
                    $('#show_comment_count_' + joke_id).html('('+ current_count +')');
                    $('#post_comment_'+joke_id+' div[name=post_box]').hide();
                    $('<li js="comment_li" class="comment_id_'+ current_count +' comment_li clearfix" style="_padding:10px 0 0;_height:46px; " onmouseover="showCommentBtns(this);" onmouseout="hideCommentBtns(this);" > <div class="user_head">'+response_data.author_portrait+' </div><div class="comment_box"><div class="name_para"><a href="/user/'+response_data.author_user_id+'" class="u_name">'+response_data.author_user_name+ '</a><div class="edit_btn" js="edit_com_hide"><a href="javascript:;" onclick="openModifyComment('+response_data.new_comment_id+'); return false;" class="btn_cmt" js="comment_btn" style="display:block; visibility:hidden;" ><span></span></a></div></div><div class="text_para" id="exists_comment_'+response_data.new_comment_id+'"> <span js="content" class="comment_para">'+data.content+'</span></div><div js="edit_com_hide" class="floor_tool"></div><div id="modify_comment_'+response_data.new_comment_id+'" style="display:none;" > <textarea js="modify_content"  class="modify_para" autocomplete="off" ></textarea> <p js="error_tip" class="left" style="color:red;"></p> <div class="right"> <a href="javascript:;" onclick="modifyComment('+response_data.new_comment_id+'); return false;" class="right"  style="display: block; margin-left:10px;" >保存</a> <a href="javascript:;" onclick="canclModifyComment('+response_data.new_comment_id+'); return false;" class="right" style="display: block;" >取消</a>  </div> </div> </div> </li>').appendTo('#comments_'+joke_id);
                }else{
                    var $error_tip = $('#post_comment_' + joke_id + ' [js=error_tip]');
                    $error_tip.html(response_data.error).show().css('opacity', '1');
                    window.setInterval(function(){
                        $error_tip.animate({opacity: 0});
                    }, 3000);
                };
            },
            error:      function(){
            }
        });
    });

};

function openComments(joke_id, page){
    if ($('#j_comment_'+joke_id).css('display') === 'none'){

        var showBoxFunction = function(){
            $('#j_comment_'+joke_id).show();
            $('#j_comment_'+joke_id+' .user_head img').each(function(){
                $(this).css('width','');
                $(this).css('height','');
                $(this).load();
            });
            if ( $('#comments_'+joke_id+ ' > li').length > 0){
                $('[js=comment_line_'+joke_id+']').show();
            }
        };

        // request comments for show
        if ($.trim($('#j_comment_'+joke_id+' [js=comments]').html()) === ''){
            var loading_handle = setTimeout(function(){
                $('[js=loading_'+joke_id+']').show();
            }, 1000);

            ZarkAPI.getComments({
                joke_id: joke_id,
                page:    page,
                callback: function(data){
                    if (data.success){
                        $('#j_comment_'+joke_id+' [js=comments]').html(data.html).show(showBoxFunction);
                        ZarkF.showForMe();
                        ZarkF.showForOthers();
                        ZarkF.showForLogin();
                    };
                    if (loading_handle){
                        window.clearTimeout(loading_handle);
                    }
                    $('[js=loading_'+joke_id+']').hide();
                }
            });
        }else{
            showBoxFunction();
        };

    }else{
        $('#j_comment_'+joke_id).hide();
        $('[js=comment_line_'+joke_id+']').hide();
    };

};

function openPostComment(joke_id){
    ZarkF.login(function(){
        $('#post_comment_'+joke_id+ ' div[name=post_box]').show();
        $('#j_comment_'+joke_id+' [name=post_btn]').hide();
        $('[js=comment_line_'+joke_id+']').show();
        $('#comment_content_'+joke_id).focus();

    });
};

function rightLogin(){
    var post_data = {
        email:   $.trim($('#login_form input[name=email]').val()),
        password:   $('#login_form input[name=password]').val(),
        error:      $('#login_error_tip'),
        rememberme: $('#login_form input:checked[name=rememberme]').val()
    }
    if (!post_data.email){
        post_data.error.html('请输入邮箱地址').show();
        return false;
    };
    if (!post_data.password){
        post_data.error.html('请输入密码').show();
        return false;
    };
    ZarkF.tryLogin(post_data);

};

function changeJumpLogin(box_name){
    var setFocus = function(){
        if (box_name === 'login'){
            $('#jump_login_form input[name=email]').focus();
        }else if(box_name === 'register'){
            $('#jump_register_form input[name=email]').focus();
        };
    };

    if (box_name === 'register'){
        $('#jump_login_form').fadeOut('fast', function(){
            $('#jump_register_form').fadeIn('fast', setFocus);
        });
    }else if (box_name === 'login'){
        $('#jump_register_form').fadeOut('fast', function(){
            $('#jump_login_form').fadeIn('fast', setFocus);
        });
    };

};

function openModifyComment(comment_id){
    ZarkF.login(function(){
        $('#modify_comment_'+comment_id + ' [js=modify_content]').html($('#exists_comment_'+comment_id+' [js=content]').html());
        $('#exists_comment_'+comment_id).parents('li[js=comment_li]').find('[js=edit_com_hide]').hide();
        $('#exists_comment_'+comment_id).hide();
        $('#modify_comment_'+comment_id).show();
    });
};

function canclModifyComment(comment_id){
    $('#modify_comment_'+comment_id).hide();
    $('#exists_comment_'+comment_id).show();
    $('#exists_comment_'+comment_id).parents('li[js=comment_li]').find('[js=edit_com_hide]').show();
};

function modifyComment(comment_id, type){
    // gather的comment，type=gather， 否则不传type
    ZarkF.login(function(){
        var post_url, action;
        if (typeof type !== 'undefined' && type === 'gather'){
            post_url = '/lengxiaohuaapi/gather-comment';
            action   = 'modifyComment';
        }else{
            post_url = '/lengxiaohuaapi/comment';
            action   = 'modify_comment';
        }

        var data = {
            content:    $.trim($('#modify_comment_'+comment_id + ' [js=modify_content]').val()),
            action:     action,
            comment_id: comment_id
        };

        if(!data.content){
            $('#modify_comment_'+comment_id + ' [js=error_tip]').html('来点内容嘛!');
            return false;
        };

        $.ajax({
            data:       data,
            dataType:   'json',
            type:       'POST',
            url:        post_url,
            success:    function(data){
                if (data.success) {
                    $('#exists_comment_'+comment_id+' [js=content]').html($('#modify_comment_'+comment_id + ' [js=modify_content]').val());
                    $('#modify_comment_'+comment_id).hide();
                    $('#exists_comment_'+comment_id).show();
                    $('#modify_comment_'+comment_id + ' [js=error_tip]').hide();
                    $('#exists_comment_'+comment_id).parents('li[js=comment_li]').find('[js=edit_com_hide]').show();
                }else{
                    var $error_tip = $('#modify_comment_' + comment_id + ' [js=error_tip]');
                    $error_tip.html(data.error).show().css('opacity', '1');
                    window.setInterval(function(){
                        $error_tip.animate({opacity: 0});
                    }, 3000);
                };
            },
            error:      function(){
                    $('#modify_comment_'+comment_id + ' [js=error_tip]').html('不好意思, 更新失败, 请稍后尝试');
            }
        });

    });
};

function ie6Hack(){
    // 屏蔽ie6下的fadeIn 和 fadeOut
    var old_fade_in = $.fn.fadeIn;
    var old_fade_out = $.fn.fadeOut;
    $.fn.fadeIn = function(){
        $(this).show();
        if (typeof arguments[arguments.length-1] === 'function'){
            arguments[arguments.length-1]();
        };

        return this;
    };
    $.fn.fadeOut = function(){
        $(this).hide();
        if (typeof arguments[arguments.length-1] === 'function'){
            arguments[arguments.length-1]();
        };
        return this;
    };

    // 屏蔽掉ie6的div a获得焦点时的虚线框
    $('div a').focus(function(){
        $(this).blur();
    });

};

function showCommentBtns(obj){
    $('[js=comment_btn]', obj).css('visibility', 'visible');
}

function hideCommentBtns(obj){
    $('[js=comment_btn]', obj).css('visibility', 'hidden');
}

function showReportBtns(obj){
    $('[js=report_joke]', obj).css('visibility', 'visible');
}

function hideReportBtns(obj){
    $('[js=report_joke]', obj).css('visibility', 'hidden');
}

function reportJoke(obj){
    var $this = $(obj);
    var back_text, faild_text,type;
    var item_type = $this.attr('report');
    if (item_type  === 'report_joke' ){
        back_text  = '收到';
        faild_text = '举报过';
    }else if (item_type  === 'report_comment' ){
        back_text  = '收到';
        faild_text = '举报过';
    }else if (item_type.indexOf('like_') === 0 ){
        type = 'le';
        back_text = '已顶';
        faild_text = '顶过';
    }else if (item_type.indexOf('unlike_') === 0 ){
        type = 'nu';
        back_text = '已踩';
        faild_text = '踩过';
    };


        var data = {
            type:      type,
            share_id:        $this.attr('jokeid'),
        };

        $.ajax({
            data:       data,
            dataType:   'json',
            type:       'POST',
            url:        '/Sever/share_click',

            success:    function(data){
                if (data.status ==1) {
                    if (item_type.indexOf('like_')===0){
                        $this.html($this.html()+'+1').addClass('color_red');
                        $this.closest('div').find('[report^=unlike_]').html('<span></span>');
                    }else if (item_type.indexOf('unlike_')===0){
                        $this.html('-1');
                        var like_btn = $this.closest('div').find('[report^=like_]'),
                            like_btn_html = like_btn.html();
                        like_btn.removeClass('color_red');
                        if (like_btn_html.indexOf('+1') !== -1){
                            like_btn.html(like_btn_html.substr(0, like_btn_html.length-2));
                        }
                        $this.css('display', 'block').css('color', '#999');
                    }else{
                        $this.html(back_text);
                        $this.css('display', 'block').css('color', '#999');
                    }
                }else{
                    $this.html(faild_text);
                    $this.css('display', 'block').css('color', '#999');
                };
            },
            error:      function(){
            }
        });

}

function showDeadComment(comment_type, comment_id){
    $('[dead_comment='+comment_type+'_'+comment_id+']').hide();
    $('[live_comment='+comment_type+'_'+comment_id+']').show();
}


function showBigImage(joke_id){
    var $img = $('#big_image_'+joke_id + ' img');

    if (typeof $img.attr('js') === 'undefined' ){

        $('#small_image_'+joke_id + ' span[js=gif_pause]').hide();
        $('#small_image_'+joke_id + ' span[js=gif_loading]').css('display', 'block');
        $('#small_image_'+joke_id + ' > a').removeClass('gif_fx_a_hover');

        $('#small_image_'+joke_id + ' img[js=jpg_loading]').show();
        $('#small_image_'+joke_id + ' img[js2=small]').hide();

        $img.bind('load', function(){
            $('#small_image_'+joke_id).hide();
            $('#big_image_'+joke_id).show();
            $('#small_image_'+joke_id + ' span[js=gif_loading]').hide();
            $('#small_image_'+joke_id + ' img[js=jpg_loading]').hide();
            $img.attr('js', 'loaded');
        }).attr('src', $img.attr('data-original'));

    }else{
        $('#small_image_'+joke_id + ' span[js=gif_pause]').hide();
        $('#small_image_'+joke_id).hide();
        $('#big_image_'+joke_id).show();
    }

}

function jumpTo(joke_id){

    var src = $('#big_image_'+joke_id+' img').attr('src');
    if (src.lastIndexOf('.jpeg') == src.length-5 || src.lastIndexOf('.jpg') == src.length-4){
        var href = window.location.href;
        if (href.indexOf('#') === -1){
            window.location.href = href + '#joke_li_' + joke_id;
        }else{
            window.location.href = href.substr(0, href.indexOf('#')) + '#joke_li_' + joke_id;
        };
    }

}

function showSmallImage(joke_id){
    $('#big_image_'+joke_id).hide();
    $('#small_image_'+joke_id).show();

    $('#small_image_'+joke_id + ' span[js=gif_pause]').show();
    $('#small_image_'+joke_id + ' span[js=gif_loading]').css('display', 'none');

    $('#small_image_'+joke_id + ' span[js=jpg_loading]').hide();
    $('#small_image_'+joke_id + ' > a').addClass('gif_fx_a_hover');
    $('#small_image_'+joke_id + ' img[js2=small]').show();

    jumpTo(joke_id);
}

function showWeixinIcon(ac_id){
    $('#ac_'+ ac_id).fadeIn('fast');
}

function hideWeixinIcon(ac_id){
    $('#ac_'+ ac_id).hide();
}

if ($.browser.msie && $.browser.version == "6.0"){
    var _fx = 'fade';
}else{
    var _fx = 'scrollVert';
};

var index_cycle1_var = { // fuck ie6 and your whole family
    fx:         _fx,
    speed:      400,
    timeout:    4000,
    pager:      '#cycle1_pager',
    pagerEvent: 'mouseover',
    before: function(currSlideElement, nextSlideElement, options, forwardFlag){
        try{
            $('#cycle1_pager a').addClass('sw').removeClass('sw_selected');
            $('#cycle1_pager a[idx='+options.nextSlide+']').addClass('sw_selected').removeClass('sw');
        }catch(err){};
    },
    pagerAnchorBuilder: function(idx, slide){
        if (idx === 0){
            var _class = 'sw_selected';
        }else{
            var _class = 'sw';
        }
        return '<a class="'+_class+'" idx="'+idx+'" href="javascript:void(0);" onclick="$(this).blur();return false;"></a>';
    }
}

var index_cycle2_var = {
    fx:         'scrollHorz',
    speed:      400,
    timeout:    4000,
    pagerEvent: 'click',
    next: '#cycle_right',
    prev: '#cycle_left'
}

function indexIe6pngCycle1(){
    if ($.browser.msie && $.browser.version == "6.0"){
        $('#cycle1_pager a').each(function(){
            try{
                DD_belatedPNG.fixPng(this);
            }catch(err){}
        });
    };
};

(function(){
$(function(){
    // 顶部导航选中样式
    var pathname = window.location.pathname;
    if (pathname.indexOf('/judge') === 0){
        $('#main_nav a[href*=judge]').addClass('selected');
    }else if(pathname.indexOf('/popular') === 0){ 
        $('#main_nav a[href*=popular]').addClass('selected');
    }else if(pathname.indexOf('/week') === 0 || pathname.indexOf('/day') === 0 || pathname.indexOf('/month') === 0){ 
        $('#main_nav a[href*=week]').addClass('selected');
    }else if(pathname.indexOf('/shenhuifu') === 0){ 
        $('#main_nav a[href*=shenhuifu]').addClass('selected');
    }else if(pathname.indexOf('/random') === 0){ 
        $('#main_nav a[href*=random]').addClass('selected');
    }else if(pathname.indexOf('/duanzi') === 0){ 
        $('#main_nav a[href*=duanzi]').addClass('selected');
    }else if(pathname.indexOf('/daily') === 0){ 
        $('#main_nav a[href*=daily]').addClass('selected');
    }else{
        $('.zhuye').addClass('selected');
    };

    // 类别和时间选择样式
    var href = window.location.href;
    if ( href.indexOf('text') !== -1 ){
        $('#chose_type_div [href$=text]').addClass('selected');
    }else if( href.indexOf('image') !== -1 ){
        $('#chose_type_div [href$=image]').addClass('selected');
    }else if( href.indexOf('video') !== -1 ){
        $('#chose_type_div [href$=video]').addClass('selected');
    }else{
        $('#chose_type_div a:first').addClass('selected');
    }
    if ( href.indexOf('day') !== -1 ){
        $('#chose_interval_div [href*=day]').addClass('selected');
    }else if( href.indexOf('week') !== -1 ){
        $('#chose_interval_div [href*=week]').addClass('selected');
    }else if( href.indexOf('month') !== -1 ){
        $('#chose_interval_div [href*=month]').addClass('selected');
    }else{
        $('#chose_interval_div [href="/"]').addClass('selected');
        $('#chose_interval_div [href="/text"]').addClass('selected');
        $('#chose_interval_div [href="/image"]').addClass('selected');
        $('#chose_interval_div [href="/video"]').addClass('selected');
    }
    // 显示发表成功的提示
    if (window.location.href.indexOf('post_success') !== -1){
        $('#post_joke_success').show();
    }

    // 绑定举报按钮事件
    $('a[report]').one('click', function(){
        reportJoke(this);
    });

    // 绑定分享按钮事件
    $('[js=share_btn]').click(function(){

        var $this=$(this);
        var joke_id = parseInt($this.attr('joke_id'));

        if ($('#share_box_'+joke_id).css('display') === 'none'){
            $('#share_box_'+joke_id).css({opacity:1}).show();
            var handle = window.setTimeout(function(){
                var joke_id = parseInt($this.attr('joke_id'));
                /* 抖动效果
                $('#share_box_'+joke_id).animate({opacity:0},100,'easeInOutBack', function(){
                   $(this).hide(); 
                });
                */
                $('#share_box_'+joke_id).css({opacity:0}).hide();
            }, 5000);
            $.data(this, 'hide_handle', handle);
        }else{
            var handle = $.data(this, 'hide_handle');
            if (typeof handle !== 'undefine'){
                window.clearTimeout(handle);
            };
            var joke_id = parseInt($(this).attr('joke_id'));
            /* 抖动效果
            $('#share_box_'+joke_id).animate({opacity:0},100,'easeInOutBack', function(){
               $(this).hide(); 
            });
            */
            $('#share_box_'+joke_id).css({opacity:0}).hide();
        };

    });

    $('div[id^=share_box_]').css('opacity', 0);
    $('[js=share_btn]').each(function(){
        $.data(this, 'click_count', 0);
    });


    if ($.browser.msie && ($.browser.version == "6.0") && (!$.support.style)) {
        ie6Hack();

        $('a.gif_fx_a').mouseover(function(){
            if ($('span[js=gif_loading]', $(this)).css('display') === 'none'){
                $('span[js=gif_pause]', $(this)).hide();    
                $('span[js=gif_play]', $(this)).show();    
            }
        }).mouseout(function(){
            if ($('span[js=gif_loading]', $(this)).css('display') === 'none'){
                $('span[js=gif_pause]', $(this)).show();    
                $('span[js=gif_play]', $(this)).hide();    
            }
        }).toggle(function(){
            $('span[js=gif_play]', $(this)).hide();    
        }, function(){
            $('span[js=gif_play]', $(this)).show();    
        });

    };

    var lazyload_event;
    if ( $('#lazyload_event').length > 0 ) {
        lazyload_event = $('#lazyload_event').val();
    }else{
        lazyload_event = 'scroll';
    };
    $('img[js=lazyload]').lazyload({
        skip_invisible: false,
        effect:     'show',
        event:     lazyload_event
    });

    var index_cycle1_var = {
        fx:         'scrollVert',
        speed:      400,
        timeout:    4000,
        pager:      '#cycle1_pager',
        pagerEvent: 'click',
        before: function(currSlideElement, nextSlideElement, options, forwardFlag){
            if ($('#cycle1_pager a:nth-of-type('+(options.nextSlide+1)+')').length > 0){
                $('#cycle1_pager a').addClass('sw').removeClass('sw_selected');
                $('#cycle1_pager a:nth-of-type('+(options.nextSlide+1)+')').addClass('sw_selected').removeClass('sw');
            }
        },
        pagerAnchorBuilder: function(idx, slide){
            return '<a class="sw" href="' + $(slide).attr('href') + '" target="_blank" fx="ie6png"></a>';
        }
    }



});
})();
