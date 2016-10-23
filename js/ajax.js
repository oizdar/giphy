
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
            updateRating(gif_id, response);
        },
    });
}

function updateRating(id, response) {
    $('#'+id+'-'+response.method).text(response.value);
    $('#'+id+'-'+response.method).parent('button').attr('disabled', true);
    $('#'+id+'-'+response.method).parent('button').removeClass('spinner');
    if (response.removed !== null) {
        $('#'+id+'-'+response.removed.method).text(response.removed.value);
        $('#'+id+'-'+response.removed.method).parent('button').removeAttr('disabled');
    }
}

function setClickEvent(elem) {
    $('.'+elem).each(function() {
        $(this).on('click', function() {
            event.preventDefault();
            $(this).attr('disabled', true);
            $(this).addClass('spinner');
            var id = $(this).attr('value');
            ajaxRequest(elem, id);
        })
    })
};

setClickEvent('like');
setClickEvent('dislike');


