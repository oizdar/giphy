
function ajaxRequest(db_method, gif_id) {
    $.ajax({
        url: 'index.php',
        type: 'POST',
        data: {
            method: 'ajax',
            db: db_method,
            id: gif_id
        },
        success:  function(response){
            updateRating(gif_id, response.method, response.value);
        },
    });
}

function updateRating(id, method, value) {
    console.log($('#'+id+'-'+method));
    $('#'+id+'-'+method).text(value);
    $('#'+id+'-'+method).parent('button').addClass('added');
}

$('.like').each(function() {

    $(this).on('click', function(e) {
        e.preventDefault();

        if($(this).hasClass('.added')) {
            var method = 'removeLike';
        } else {
            var method = 'addLike';
        }
        var id = $(this).attr('value');
        ajaxRequest(method, id);
    });
});

$('.dislike').each(function() {

    $(this).on('click', function(e) {
        e.preventDefault();

        if($(this).hasClass('.added')) {
            var method = 'removeDislike';
        } else {
            var method = 'addDislike';
        }
        var id = $(this).attr('value');
        ajaxRequest(method, id);
    });
});


