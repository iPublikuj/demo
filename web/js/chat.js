var socketsConnection;
var socketSession;

$(function () {

    $('form[name="join"]').submit(function (e) {
        e.preventDefault();

        var nick = $(this).find('input[name="nick"]').val();
            nick = $.trim(nick);

        if (nick == '') {
            UIkit.notify('Enter your nickname', 'warning');

            return false;
        }

        Cookies.set('nickname', nick);

        $('#join-box').addClass('uk-hidden');
        $('#chat-box').removeClass('uk-hidden');

        socketsConnection = IPub.WebSockets.WAMP.initialize('wss://demo.ipublikuj.eu/wss/');

        socketsConnection.on('socket/connect',
            function(session){
                console.log('Connection to server was established');

                UIkit.notify('Connection to server was established');

                socketSession = session;

                $('.rooms a.room').unbind('click').click(function (e) {
                    e.preventDefault();

                    var $roomButton = $(this);

                    $roomButton.parent('ul').find('li').removeClass('uk-active');
                    $roomButton.parent('li')
                        .addClass('uk-active')
                        .find('a.leave-room')
                            .removeClass('uk-hidden');

                    if (typeof $roomButton.data('subscribed') == 'undefined') {
                        $roomButton.data('subscribed', true);

                        if (!$('div.messages div#room-' + $roomButton.data('roomId')).length) {
                            $('div.messages').append($('<div class="room-window" id="room-' + $roomButton.data('roomId') + '" />'));
                        }

                        $('div.messages .room-window').hide();
                        $('div.messages div#room-' + $roomButton.data('roomId')).show();

                        socketSession.subscribe($roomButton.data('roomTopic'), function (topic, event) {
                            if (typeof event == 'string') {
                                event = $.parseJSON(event);
                            }

                            if (event.type == 'system') {
                                var $systemMessage = $('<div class="comment uk-alert uk-alert-success" />');
                                    $systemMessage
                                        .append($('<h2 />').html('System Bot<br />').append($('<time datetime=' + event.time + ' />')))
                                        .append($('<p />').html(event.content));

                                $('div.messages div#room-' + $roomButton.data('roomId')).prepend($systemMessage);

                            } else if (event.type == 'message') {
                                var memberName = event.from;

                                if (event.isMe == true) {
                                    memberName = 'Me';
                                }

                                var $message = $('<div class="comment uk-alert" />');
                                    $message
                                        .append($('<h2 />').html(memberName + '<br />').append($('<time datetime=' + event.time + ' />')))
                                        .append($('<p />').html(event.content));

                                if (event.isMe == true) {
                                    $message.addClass('uk-alert-warning');
                                }

                                $('div.messages div#room-' + $roomButton.data('roomId')).prepend($message);

                            } else if (event.type == 'members') {
                                var $memberList = $roomButton.parent('li').find('ul');
                                    $memberList.empty();

                                if (event.content instanceof Array) {
                                    $.each(event.content, function () {
                                        $memberList.append($('<li />').html('<i class="uk-icon-circle uk-text-primary"></i> ' + this.name));
                                    })
                                }
                            }

                            $('h2 time').timeago();
                        });
                    }

                    $('input[name="message"]').prop('disabled', false);
                    $('button[name="send"]').prop('disabled', false);
                    $('form[name="new-message"]').removeClass('uk-hidden');
                });

                $('.rooms a.leave-room').click(function (e) {
                    e.preventDefault();

                    var $roomButton = $(this).parent('li').find('a.room');

                    if (typeof $roomButton.data('subscribed') != 'undefined') {
                        socketSession.unsubscribe($roomButton.data('roomTopic'));

                        $roomButton
                            .removeData('subscribed')
                            .parent('li')
                            .removeClass('uk-active')
                            .find('ul').empty();

                        $(this).addClass('uk-hidden');
                    }

                    $('div.messages div#room-' + $roomButton.data('roomId')).remove();

                    $('input[name="message"]').prop('disabled', 'disabled');
                    $('button[name="send"]').prop('disabled', 'disabled');
                    $('form[name="new-message"]').addClass('uk-hidden');
                });

                $('form[name="new-message"]').submit(function (e) {
                    e.preventDefault();

                    var message = $(this).find('input[name="message"]').val();
                        message = $.trim(message);

                    if (message != '') {
                        var data = {
                            'message': message
                        };

                        socketSession.publish($('.rooms li.uk-active a.room').data('roomTopic'), data);

                        $('input[name="message"]').val('');
                    }
                })
            },
            function (code, reason, detail) {
                console.log('Connection could not be established');

                UIkit.notify('Connection could not be established', 'warning');
            }
        );

        socketsConnection.on('socket/disconnect', function(error){
            console.log('Disconnected for ' + error.reason + ' with code ' + error.code);

            UIkit.notify('Connection lost');

            $('.rooms a.room').each(function () {
                var $roomButton = $(this);

                $roomButton
                    .removeData('subscribed')
                    .parent('li')
                        .removeClass('uk-active')
                        .find('ul').empty();

                $roomButton.unbind('click').click(function (e) {
                    e.preventDefault();
                });

                if (typeof $roomButton.data('subscribed') != 'undefined') {
                    socketSession.unsubscribe($roomButton.data('roomTopic'));
                }

                $('div.messages div#room-' + $roomButton.data('roomId')).hide();
            });

            $('.rooms a.leave-room').addClass('uk-hidden');

            $('input[name="message"]').prop('disabled', 'disabled');
            $('button[name="send"]').prop('disabled', 'disabled');
            $('form[name="new-message"]').addClass('uk-hidden');
        });
    })
});
