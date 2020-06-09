
$(function(){

    $('#l_top').click(function(){
        window.location.href = 'index.php';
    });

    $('#passcheck').keyup(function(){
        var pass = $('#passck1').val;
        if($(this).val() == ""){
            $('#idck').val(pass);	
            }else{
            $(this).css("background-color", "#FaFEFF");
            }
    });

    $('#regbutton').click(function(){
        var add1 = $('#ad1').val;
        var add2 = $('#ad2').val;
        if(add1 != add2){
            alert('メールアドレスが一致しません！');
        }

    });

});

