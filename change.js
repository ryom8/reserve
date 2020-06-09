
$(function(){
    $('.check').click(function(){
        var date = $(this).attr('value');
        var chID = $(this).attr('id');
        
        window.open('check.php?date=' + date + '&orderID=' + chID,'_blank','width=400,height=500,toolbar=0,location=0,menubar=0,scrollbars=0,resizable=0');

    });

    $('.change').click(function(){
        var date = $(this).attr('value');
        var chID = $(this).attr('id');

        window.location.href = 'reserve_change.php?date=' + date + '&chID=' + chID;

    });
    
    $('.cancel').click(function(){
        var price = $(this).attr('value');
        var pr = price;
        if(price == 0){
            pr = '無料';
        }else{
            pr = '\\' + price;
        }

        var cancelID = $(this).attr('id');
        cancelID = cancelID.substr(0,8);
        if(confirm('予約のキャンセルを行います。\n本当によろしいですか？\n\n注文番号：' + cancelID + '\nキャンセル料金：' + pr)){
            var postData = { 'id':cancelID, 'price':price };

            $.ajax({
                url:'cancel.php',
                type:'POST',
                data:postData,
                success:function(data){alert(data);}
            });
        }

    });


    $('#wclose').click(function(){
        open('about:blank','_self').close();
    });


});

