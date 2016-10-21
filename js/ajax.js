
function ajaxRequest(db_action, gif_id) {
    $.ajax({
        url: 'index.php',
        type: 'POST',
        data: {
            method: 'ajax',
            action: db_action,
            id: gif_id
        },
        success:  function(response){
            console.log(response);
            updateRating(gif_id, response);
        },
    });
}

function updateRating(id, response) {
    $('#'+id+'-'+response.method).text(response.value);
    $('#'+id+'-'+response.method).parent('button').attr('disabled', true);
    console.log($('#'+id+'-'+response.method).parent('button'));
    if (response.removed !== null) {
        $('#'+id+'-'+response.removed.method).text(response.removed.value);
        $('#'+id+'-'+response.removed.method).parent('button').removeAttr('disabled');
    }
}

$('.like').each(function() {

    $(this).on('click', function(e) {
        e.preventDefault();
        var method = 'like';
        var id = $(this).attr('value');
        ajaxRequest(method, id);
    });
});

$('.dislike').each(function() {

    $(this).on('click', function(e) {
        e.preventDefault();
        var method = 'dislike';
        var id = $(this).attr('value');
        ajaxRequest(method, id);
    });
});


