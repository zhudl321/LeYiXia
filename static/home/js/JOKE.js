//global script
(function(){

    var namespace ="JOKE";
    if (!window[namespace]) window[namespace] = {};
    var JOKE = window[namespace];
    JOKE.current_report_joke_id = null;
    JOKE.current_report_comment_id = null;
    JOKE.current_report_btn = null;
    JOKE.current_report_type = null;

    JOKE.openPostJokeBox = function(b_id, success_function_name){
        ZarkF.login(function(){
            //$('#post_joke_content').fadeIn();
            $('#post_joke_box').fadeIn();
            $('#post_joke_success').hide();
            $('#post_tip').html('');
        });
    };

    JOKE.closePostBox= function(){
        $('#post_joke_box').fadeOut();
    };

    JOKE.postJoke = function(){
        var data = {
            title:      '',
            content:    $.trim($('#post_content_text textarea[name=content]').val()),
            action:     'post_joke'
        };

        if(!data.content){
            $('#post_tip').html('一点都不好笑,来点内容嘛!').show();
            return false;
        };

        if(data.content.length < 10){
            $('#post_tip').html('内容太少了,冷不起来嘛!').show();
            return false;
        };

        if(data.content.length > 2000){
            $('#post_tip').html('内容太长了,看完就真的冷冻了!').show();
            return false;
        };

        if(data.title === '请输入笑话标题'){
            data.title = '';
        };

        $.ajax({
            data:       data,
            dataType:   'json',
            type:       'POST',
            url:        '/lengxiaohuaapi/joke',
            success:    function(data){
                if (data.success) {
                    $('#post_tip').html('');
                    //$('#post_joke_content').fadeOut('fast');
                    $('#post_joke_box').fadeOut('fast');
                    $('#post_joke_success').fadeIn();
                    $('#post_content_text input[name=title]').val('请输入笑话标题');
                    $('#post_content_text textarea[name=content]').val('');
                }else{
                    if ((typeof data.msg !== 'undefined') && data.msg==='exists'){
                        if (data.exists_joke_id !== 0){
                            $('#post_tip').html('这个笑话我们已经有了   <a href="/joke/' + data.exists_joke_id + '" target="_blank" >查看已有笑话</a>').show();
                        }else{
                            $('#post_tip').html('这个笑话我们已经有了, 正在审核中').show();
                        }
                    }else{
                        $('#post_tip').html('发表失败').show();
                    }
                };
            },
            error:      function(){
            }
        });

    };

    JOKE.postVideoJoke = function(){
        var data = {
            title:      '',
            content:    $.trim($('#post_content_video textarea[name=content]').val()),
            action:     'post_joke',
            video_uri:  $.trim($('#post_content_video input[name=video_uri]').val()),
            type:       'video'
        };

        if(!data.content || data.content === '写一点关于此视频的介绍吧'){
            $('#post_video_error').html('请输入视频介绍').show();
            return false;
        };

        if(data.content.length < 10){
            $('#post_video_error').html('内容太少了,冷不起来嘛!').show();
            return false;
        };

        if(data.content === data.video_uri ){
            $('#post_video_error').html('你太懒了,拜托就写一点嘛 :(').show();
            return false;
        };

        if(data.video_uri.indexOf(data.content) !== -1 ){
            $('#post_video_error').html('小样，你以为你删几个字母就可以混过关啊!').show();
            return false;
        };

        if(data.content.length > 300){
            $('#post_video_error').html('内容太长了,看完就真的冷冻了!').show();
            return false;
        };

        if(data.title === '请输入笑话标题'){
            data.title = '';
        };

        $.ajax({
            data:       data,
            dataType:   'json',
            type:       'POST',
            url:        '/lengxiaohuaapi/joke',
            success:    function(data){
                if (data.success) {
                    $('#post_video_error').html('');
                    $('#post_joke_box').fadeOut('fast');
                    $('#post_joke_success').fadeIn();
                    $('#post_content_text input[name=title]').val('请输入笑话标题');
                    $('#post_content_text textarea[name=content]').val('');
                }else{
                    if ((typeof data.msg !== 'undefined') && data.msg==='exists'){
                        if (data.exists_joke_id !== 0){
                            $('#post_video_error').html('这个视频我们已经有了   <a href="/joke/' + data.exists_joke_id + '" target="_blank" >查看已有视频</a>').show();
                        }else{
                            $('#post_video_error').html('这个视频我们已经有了, 正在审核中').show();
                        }
                    }else{
                        $('#post_video_error').html('发表失败').show();
                    }
                };
            },
            error:      function(){
            }
        });

    };

    JOKE.openReportJokeBox = function(obj, joke_id, type, comment_id){
        ZarkF.login(function(){
            ZarkAPI.isReported({
                item_type:  type,
                item_id:    type === 'report_joke' ? joke_id : comment_id,
                callback:   function(data){
                    if (data.status === false){
                        $('#report_joke_box').fadeIn();
                        $('#overlayer').fadeIn();
                        JOKE.current_report_joke_id = joke_id;
                        JOKE.current_report_comment_id = comment_id;
                        JOKE.current_report_btn = obj;
                        JOKE.current_report_type = type;
                    }else{
                        $(obj).html('举报过').css('display', 'block').css('color', '#999').unbind('click');
                    };
                }
            
            });
        });
    };

    JOKE.closeReportJokeBox = function(){
        $('#report_joke_box').hide();
        $('#overlayer').hide();
        JOKE.current_report_joke_id = null;
        JOKE.current_report_comment_id = null;
        JOKE.current_report_btn = null;
        JOKE.current_report_type = null;
    };

    JOKE.reportJoke = function(){
        var content = $('#report_joke_box [name=content]').val();
        if (content === '请填写您的举报理由...') content = '';

        var data = {
            item_type:   JOKE.current_report_type,
            joke_id:     JOKE.current_report_joke_id,
            comment_id:  JOKE.current_report_comment_id,
            content:     content
        };

        $.ajax({
            data:       data,
            dataType:   'json',
            type:       'POST',
            url:        '/lengxiaohuaapi/log',
            success:    function(response_data){
                var $this = $(JOKE.current_report_btn);
                if (response_data.success) {
                    $this.html('收到');
                }else{
                    $this.html('举报过');
                };
                $this.css('display', 'block').css('color', '#999');
                JOKE.closeReportJokeBox();
                $this.unbind('click');
            },
            error:      function(){
            }
        });

    };


})()

// 匿名评论
var COMMENTS_PAGE_NUM = {},
    BEFOR_ID = {};

function showMoreComments(joke_id){
    if (typeof COMMENTS_PAGE_NUM[joke_id] === 'undefined'){
        COMMENTS_PAGE_NUM[joke_id] = 2;
    }
    var page_num = COMMENTS_PAGE_NUM[joke_id];

    $('#show_more_btn').html('正在努力加载..');

    var post_datas = {
        gather_id: joke_id,
        gather_type: 'GatherJoke',
        page_num:  page_num,
        callback:  function(data){
            if (data.success){
                if (data.comments.length > 0){
                    for (var i in data.comments){
                        var comment = data.comments[i], _c;
                        if (comment.like_count > 0){
                            _c = 'color_red';
                        }else{
                            _c = '';
                        };
                        if (comment.dead === 'on'){
                            var hide_dead = '';
                            var hide_live = 'display:none;';
                        }else{
                            var hide_dead = 'display:none;';
                            var hide_live = '';
                        };
                        $('#comments_' + joke_id).append('<li dead_comment="ano_comment_'+comment.AnonymityJokeCommentid+'" style="'+hide_dead+'" class="comment_li comment_li_wx clearfix" js="comment_li" onmouseover="showCommentBtns(this)" onmouseout="hideCommentBtns(this)" > <div class="user_head"> <img src="/img/page/default_weixin.png" style="width:40px; height:40px;" class="corner4px left"/> </div> <div class="comment_box"> <div class="name_para"> <a class="u_name" href="javascript:void(0);" >****</a> </div> <div class="text_para clearfix"> <span class="comment_para">****此评论评分过低被关闭,请<a href="javascript:void(0);" onclick="showDeadComment(\'ano_comment\', '+comment.AnonymityJokeCommentid+');return false;" > 点击查看 </a>****</span> </div> </div> </li><li style="'+hide_live+'" class="comment_li comment_li_wx clearfix" js="comment_li" onmouseover="showCommentBtns(this)" onmouseout="hideCommentBtns(this)" > <div class="user_head"> <img src="/img/page/default_weixin.png" style="width:40px; height:40px;" class="corner4px left"/> </div> <div class="comment_box"> <div class="name_para"> <a class="u_name" href="javascript:void(0);" >' + comment.user_name + '</a><a class="unlike_btn btn_cmt" href="javascript:void(0)" report="unlike_ano_comment" commentid="'+ comment.AnonymityJokeCommentid + '" onclick="reportJoke(this)" ><span></span></a><a class="like_btn btn_cmt '+_c+'" href="javascript:void(0)" report="like_ano_comment" commentid="'+ comment.AnonymityJokeCommentid + '" onclick="reportJoke(this)" ><span></span>'+comment.like_count+'</a> </div> <div class="text_para"> <span class="comment_para">' + comment.content + '</span><a js="comment_btn" onmouseover="showWeixinIcon(' + comment.AnonymityJokeCommentid + ')" onmouseout="hideWeixinIcon(' + comment.AnonymityJokeCommentid + ')" class="weixin_qr_tog corner2px" style="visibility:hidden;"> <span id="ac_' + comment.AnonymityJokeCommentid + '" class="weixin_qr_ad" ></span> </a> </div> </div> </li>');
                        if (typeof BEFOR_ID[joke_id] === 'undefined'){
                            BEFOR_ID[joke_id] = comment.AnonymityJokeCommentid;
                        };
                    }
                    COMMENTS_PAGE_NUM[joke_id] += 1;
                    $('#show_more_btn').html('继续加载评论(' + $('#all_comment_count').val() + ')');
                }else{
                    alert('没有更多评论了，你也来写点评论？');
                    $('#show_more_btn').html('没有更多评论了');
                }
            }else{
                alert('发表失败, 当前网络不给力?');
                $('#show_more_btn').html('再次尝试加载');
            }
        }
    };

    if (typeof BEFOR_ID[joke_id] !== 'undefined'){
        post_datas.before_id = BEFOR_ID[joke_id];
    };

    ZarkAPI.getAnonymityComments(post_datas);
}

