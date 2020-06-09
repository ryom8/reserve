
$(function(){

    $('#l_new').click(function(){
        window.open('register.php','_blank','width=400,height=400,toolbar=0,location=0,menubar=0,scrollbars=0,resizable=0');
    });

    $('#l_login').click(function(){
        window.open('login.php','_blank','width=400,height=400,toolbar=0,location=0,menubar=0,scrollbars=0,resizable=0');
    });

    $('#l_logout').click(function(){
        window.open('logout.php','_blank','width=400,height=400,toolbar=0,location=0,menubar=0,scrollbars=0,resizable=0');
    });

    $('#l_howto').click(function(){
        window.open('info.html','_blank','width=450,height=600,toolbar=0,location=0,menubar=0,scrollbars=0,resizable=0');
    });

    $('#prevMonth').click(function(){
        var prev = $('#prevMonth').val();
        var pYear = prev.substr(0,4);
        var pMonth = prev.substr(4,2);
        window.location.href = 'index.php?year=' + pYear + '&month=' + pMonth;
    });

    $('#nextMonth').click(function(){
        var next = $('#nextMonth').val();
        var nYear = next.substr(0,4);
        var nMonth = next.substr(4,2);
        window.location.href = 'index.php?year=' + nYear + '&month=' + nMonth;
    });

    $('.nLogin').click(function(){
        var roomno = $(this).attr('id');
        var stayurl = 'reserve.php?stay=' + roomno;
        window.open(stayurl,'_blank','width=600,height=700,toolbar=0,location=0,menubar=0,scrollbars=0,resizable=0');
    });

    $('#l_change').click(function(){
        window.open('change.php','_blank','width=600,height=700,toolbar=0,location=0,menubar=0,scrollbars=0,resizable=0');
    });

    $('.nLogoff').click(function(){
        window.open('login.php','_blank','width=400,height=400,toolbar=0,location=0,menubar=0,scrollbars=0,resizable=0');
    });

    $('#windowclose').click(function(){
        open('about:blank','_self').close();
    });


});

