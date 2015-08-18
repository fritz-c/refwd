var io = require('socket.io')();
var request = require("request");

io.listen(48443);
console.log('Probably listening on *:48443');

function forwardToMainApp (payload) {
    request.post(
        'http://localhost:40080/_socket',
        { form: { data: payload }, followAllRedirects: true },
        function (error, response, body) {
            if (error || response.statusCode != 200) {
                console.log(error);
                console.log(body);
            }
        }
    );
}

io.on('connection', function (socket) {
    socket.on('room', function (room) {
        socket.join(room);
    });

    socket.on('chat', function (room, data) {
        // Forward message to all listening sockets in the room
        io.sockets.to(room).emit('chat', data);

        // Ignore submissions to example page
        if (room == 'gxn5v9spm4dr' || room == 'bu7mx3fvqtzz') return;

        data['t'] = 'c';   // Type = comment
        data['s'] = room;  // Space = room token
        forwardToMainApp(data);
    });

    socket.on('relish', function (room, data) {
        // Forward message to all listening sockets in the room
        io.sockets.to(room).emit('relish', data);

        // Ignore submissions to example page
        if (room == 'gxn5v9spm4dr' || room == 'bu7mx3fvqtzz') return;

        data['t'] = 'r';   // Type = relish
        data['s'] = room;  // Space = room token
        forwardToMainApp(data);
    });
});
