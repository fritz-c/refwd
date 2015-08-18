"use strict";

var allLikesDisplayed = false;

function updateLikeList()
{
    var listCount = $("#likes.like-list li").length;

    // Get rid of previously generated text bits
    $("#show-all-likes").remove();
    $("#likes-end").remove();

    // Leave the list blank if no one is in it
    if (listCount === 0) return;

    if (!allLikesDisplayed) {
        if (listCount > 3) {
            $("#likes").toggleClass('abbreviated', true);

            var othersCount = listCount - 2;
            var othersStatement = likeOthersPlural.format(othersCount);
            if (othersCount == 1) {
                othersStatement = likeOthersSingular.format(othersCount);
            }

            $("#likes").append('<a id="show-all-likes" href="javascript:displayAllLikes()">' + othersStatement + '</a>');
        } else {
            $("#likes").toggleClass('abbreviated', false);
        }
    }

    var presentUserId = $("#form_author").val();
    var endStatement = youLikeEndTextPlural;
    if (listCount == 1 && !$('#like_id_' + presentUserId).length ) {
        endStatement = youLikeEndTextSingular;
    }

    $("#likes").append('<span id="likes-end">' + endStatement + '</span>');

    // Put present user at top of the list they see
    if (presentUserId) {
        var existingElement = $("#like_id_" + presentUserId);
        existingElement.text(youLikeText);
        // If the user is in the list but not at the top
        if (existingElement.length > 0 && existingElement.index() > 0) {
            existingElement.remove();
            $("#likes").prepend(existingElement);
        }
    }
}

function addToLikeList(id, name)
{
    $("li#like_id_" + id).remove();
    $("#likes").prepend($('<li>').prop('id', 'like_id_' + id).text(name));
    updateLikeList();
}

function removeFromLikeList(id)
{
    $("li#like_id_" + id).remove();
    updateLikeList();
}

function displayAllLikes()
{
    allLikesDisplayed = true;
    updateLikeList();
    $("#likes").toggleClass('abbreviated', false);
}

function updateLikeButton()
{
    // Remove loading indicator
    $('#like-btn').toggleClass('loading', false);

    var userId = $("#form_author").val();
    if (userId) {
        var existingElement = $("#like_id_" + userId);
        // If the user is in the relish list, post was already liked by the user
        if (existingElement.length > 0) {
            if (!$('#like-btn').hasClass('is-liked')) {
                $('#like-btn').text(unlikeButtonText);
                $('#like-btn').addClass('is-liked');
            }
            return;
        }
    }

    // Post was unliked
    $('#like-btn').text(likeButtonText);
    $('#like-btn').removeClass('is-liked');
}

$(function() {
    $('#like-btn').click(function()
    {
        var userId = $('#form_author').val();
        if(userId === "") {
            alert("Please select a name.");
            $('#form_author').stop().fadeIn(100).fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100);
            $('#form_author').focus();
            return false;
        }

        $('#like-btn').addClass('loading');
        var willLike = ! $('#like-btn').hasClass('is-liked');
        // Prepare data to send to socket
        var msg = {
            u: userId,  // User id
            b: willLike // Activate or deactivate relish
        };

        // Send to server
        socket.emit('relish', roomId, msg);
        return false;
    });

    updateLikeList();
    updateLikeButton();
});
