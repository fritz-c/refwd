"use strict";

var restartIntervalId = 0;
var pingIntervalId = 0;
var socket;
var wasConnected = false;

$(document).ready(function(){
    start(socketUri);
    goToChatBottom();

    $('#comment-form').submit(function() {
        var userId = $('#form_author').val();
        var message = $.trim($('#form_body').val());

        if(userId === "") {
            alert("Please select a name.");
            $('#form_author').stop().fadeIn(100).fadeOut(100).fadeIn(100).fadeOut(100).fadeIn(100);
            $('#form_author').focus();
            return false;
        }

        // Reject empty messages
        if (message.length < 1) {
            $('#form_body').val('');
            return false;
        }

        // Prepare data to send to socket
        var msg = {
            u: userId, // User id
            m: message // message
        };

        // Send to server
        socket.emit('chat', roomId, msg);

        displayComment(msg);
        $('#chat-box').children('div').last().children('div').first().addClass('loading');

        //reset text
        $('#form_body').val('');
        return false;
    });

    // Handle the author selector being changed
    $('#form_author').data('oldVal',  $('#form_author').val());
    $('#form_author').change(function () {
        var oldValue = $(this).data('oldVal');
        var oldYouLike = $('li#like_id_' + oldValue);

        // Replace oldVal with the current value, as its job is done
        $(this).data('oldVal',  $(this).val());

        // Replace the 'You' used in the list (as we are changing contexts to a different user) with the original name
        if (oldYouLike.length) {
            oldYouLike.text(getUsernameById(oldValue));
        }

        updateLikeList();
        updateLikeButton();

        // Remove any parameters from the url in the history, and in the address bar, without reloading the page
        // https://developer.mozilla.org/en-US/docs/Web/Guide/API/DOM/Manipulating_the_browser_history
        history.replaceState(document.html, document.title, window.location.href.replace(/(\?.*)?$/g, ''));
    });

    if (jQuery.hotkeys) {
        $('#form_body').on('keydown', null, 'shift+return', function(event){
            $('#form_submit').click();
            event.preventDefault(); // Keep return key from inserting a new line on keyup
        });   
    }
});

function start(socketUri)
{
    socket = new io(socketUri);

    // Connection opened callback
    socket.on('connect', function() {
        wasConnected = true;
        socket.emit('room', roomId);
        // $('#chat-box').append("<div class=\"system_msg\">Connected!</div>");
        // goToChatBottom();
    });

    // Message received callback
    socket.on('chat', function(data) {
        $('#chat-box .loading').removeClass('loading');
        if (data.u !== $("#form_author").val()) {
            displayComment(data);
        }
    });

    // Relish received callback
    socket.on('relish', function(data) {
        // If post is to be liked by user, add them to the list, or remove them if not
        if (data.b) {
            var name = getUsernameById(data.u);
            addToLikeList(data.u, name);
        } else {
            removeFromLikeList(data.u);
        }
        updateLikeButton();
    });

    socket.on('connect_error', function(err) {
        if (wasConnected) {
            // $('#chat-box').append("<div class=\"system_error\">An error occurred... please wait to reconnect.</div>");
            // goToChatBottom();
            wasConnected = false;
        }
    });
}

// String format
if (!String.prototype.format) {
  String.prototype.format = function() {
    var args = arguments;
    return this.replace(/{(\d+)}/g, function(match, number) {
      return typeof args[number] != 'undefined'
        ? args[number]
        : match
      ;
    });
  };
}

function getUsernameById(userId)
{
    var name = '';
    if (peopleInfo[userId] !== undefined) {
        name = peopleInfo[userId]['name'] ? peopleInfo[userId]['name'] : peopleInfo[userId]['email'];
    }
    return name;
}

function displayComment(msg)
{
    var name  = msg.u in peopleInfo ? peopleInfo[msg.u]['name'] : '';
    var email = msg.u in peopleInfo ? peopleInfo[msg.u]['email'] : '';
    var body  = cleanText(msg.m).replace(/(?:\r\n|\r|\n)/g, '<br />');
    name = name !== null && name.length > 0 ? name : email;

    var currentDate = new Date();
    var time = zeropad(currentDate.getHours()) + ":" + zeropad(currentDate.getMinutes()) + ":" + zeropad(currentDate.getSeconds());

    $('#chat-box').append("<div class=\"row\"><div class=\"two columns alpha user-name\" title=\"" + email + "\">" + name + "</div><div class=\"ten columns omega user-message\">" + body + "</div>" + /* "<span style=\"float:right\">(" + time + ")</span>" + */ "</div>");
    goToChatBottom();
}

function zeropad(n) { return ("0" + n).slice(-2); }
function goToChatBottom() { $('#chat-box').scrollTop($('#chat-box')[0].scrollHeight); }
function cleanText(unsafe) { return $('<span/>').text(unsafe).html(); }
