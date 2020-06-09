
$(function(){
    $('#wback').click(function(){
        history.back();
    });

    $('#wclose').click(function(){
        open('about:blank','_self').close();
    });


});

