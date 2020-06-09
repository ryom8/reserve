
$(function(){
    $('.Rnum').prop("disabled",true);
    $('.Rname').prop("disabled",true);
    $('.conpile').prop("disabled",true);
    $('#csend').prop("disabled",true);

    $('#conpile').change(function(){
        if($('#conpile').prop('checked')){
            $('#orderConpile').prop("disabled",false);
        }else{
            $('#orderConpile').prop("disabled",true);
        }

        var changeable = $('input:checked').length;

        if(changeable > 0){
            $('#csend').prop("disabled",false);
        }else{
            $('#csend').prop("disabled",true);
        }
    });

    $('.changeOK').change(function(){
        var target = $(this).attr('value');
        var n1 = '#' + target + '-num';
        var n2 = '#' + target + '-name';
        var n3 = '#' + target + '-can';

        if($(this).prop('checked')){
            $(n1).prop('disabled',false);
            $(n2).prop('disabled',false);
            $(n3).prop('disabled',true);
        }else{
            $(n1).prop('disabled',true);
            $(n2).prop('disabled',true);
            $(n3).prop('disabled',false);
        }

        var changeable = $('input:checked').length;

        if(changeable > 0){
            $('#csend').prop("disabled",false);
        }else{
            $('#csend').prop("disabled",true);
        }
    });

    $('.Rcancel').change(function(){
        var target = $(this).attr('value');
        var n4 = '.' + target;

        if($(this).prop('checked')){
            $(n4).prop('disabled',true);
        }else{
            $(n4).prop('disabled',false);
        }

        var changeable = $('input:checked').length;

        if(changeable > 0){
            $('#csend').prop("disabled",false);
        }else{
            $('#csend').prop("disabled",true);
        }

    });

    $('#wback').click(function(){
        history.back();
    });


    $('#csend').click(function(){
        $('input[name="change[]"]:checked').each(function(){

        });
    });


    $('#wclose').click(function(){
        open('about:blank','_self').close();
    });


});

