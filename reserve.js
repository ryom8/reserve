
$(function(){
    $('#submit').prop("disabled",true);
    $('.noEmpty').prop("disabled",true);

    $('.Empty').click(function(){
        var room = $(this).attr('id');
        var buttonid = 'button#' + room;
        var nameid = '0' + room;
        var formInfo = '<div class="Rroom"><p class="Rform">人数：<select name="' + room + '" class="Rnum"><option value="1">1</option><option value="2">2</option></select>名様<br>';
        formInfo += '代表者(カタカナ)<br><input type="text" name="' + nameid + '" class="Rname" pattern="(?=.*?[\u30A1-\u30FC])[\u30A1-\u30FC\s]*" required>様</p></div>';
        $(buttonid).replaceWith(formInfo);
        $('#submit').prop("disabled",false);
    });

    $('#wclose').click(function(){
        open('about:blank','_self').close();
    });


});

