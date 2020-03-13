$('body').on('change', '#ftsub-mainobjects', function(){
    var id=$(this).val();
    if(id == 0){
        $('.cnt-main-object').show();
    }else{
        $('.cnt-main-object').hide();
        $('.cnt-main-object-'+id).show();
    }
});

$('body').on('change', '#ftsub-mainocats', function(){
    var id=$(this).val();
    if(id == 0){
        $('.m-cat').show();
    }else{
        $('.m-cat').hide();
        $('.m-cat-'+id).show();
    }
});

$('body').on('click', '#ftsub-resetobject', function (){
    $('#formobjectselect input[type=checkbox]').each(function () {
        this.checked = false;
    })
});

$('body').on('click', '#ftsub-selectallobject', function (){
    $('#formobjectselect input[type=checkbox]').each(function () {
        this.checked = true;
    })
});

$('body').on('click', '#ftsub-resetcategory', function (){
    $('#formcategoryselect input[type=checkbox]').each(function () {
        this.checked = false;
    })
});

$('body').on('click', '#ftsub-selectallcategory', function (){
    $('#formcategoryselect input[type=checkbox]').each(function () {
        this.checked = true;
    })
});

$('body').on('click', '.button-select-object', function (){
    var send=$('#ch-obj:checked').map(function() {
        return $(this).next('span').text();
    }).get();
    $('#ft-select-objects').val(send);
});

$('body').on('click', '.button-select-category', function (){
    var send=$('#cat:checked').map(function() {
        return $(this).next('span').text();
    }).get();
    $('#ft-select-categories').val(send);
});

$('body').on('click', '#ch-main-obj', function (){
    if($(this).is(':checked')){
        $(this).parent().parent().find('.cnt-sub-object input[type=checkbox]').each(function () {
            this.checked = true;
        })
    }else{
        $(this).parent().parent().find('.cnt-sub-object input[type=checkbox]').each(function () {
            this.checked = false;
        })
    }
});

$('body').on('click', '#ch-obj', function (){
    var ischecked = $(this).parent().parent().find('input:checked').length > 0;
    if(ischecked){
        $(this).parent().parent().parent().find('#ch-main-obj').prop('checked', true);
    }else{
        $(this).parent().parent().parent().find('#ch-main-obj').prop('checked', false);
    }
});

$('body').on('keyup', '#ftsub-objects', function(){
    if($(this).val()!='') {
        $('.ch-obj-el').parent().hide();
        $('.cnt-main-sub-object').hide();
        $('.cnt-main-object').hide();
        $('.ch-obj-el').filter(function () {
            var text = $('#ftsub-objects').val();
            var val = $(this).find('span').text();
            return val.toUpperCase().indexOf(text.toUpperCase()) != -1;
        }).parent().show().parent().parent().show().find('.cnt-main-sub-object').show();
    }else{
        $('.cnt-main-sub-object').show();
        $('.cnt-main-object').show();
        $('.ch-obj-el').parent().show();
    }
});

// function clearFilter(id)
// {
//     $.ajax({
//         type: 'POST',
//         url: '/qrcode/print?&action=clearfilter&id=' + id,
//         contentType: 'application/json; charset=utf-8',
//     });
// }

$('body').on('change', '#code-size', function(){
    var e = document.getElementById('code-size');

    if(e.options[e.selectedIndex].value != 0){
        e.classList.remove('btn-danger');
        e.classList.add('btn-success');
    }
    else e.classList.add('btn-danger');
});
