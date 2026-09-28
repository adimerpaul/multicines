const express = require('express');
const app = express();
require("dotenv").config();
const httpServer = require("http").createServer(app);
const io = require("socket.io")(httpServer, {
    cors: {
        origin: "*",
    },
});
app.get('/', (req, res) => {
    res.sendFile(__dirname + '/index.html');
});
io.on("connection", (socket) => {
    socket.on('order', (msg) => {
        io.emit('order', msg);
    });
    // Boleteria: una caja reservo, libero o vendio butacas. Se avisa a las
    // demas cajas que funciones cambiaron para que recarguen sus butacas.
    socket.on('butacas', (data) => {
        const ids = Array.isArray(data && data.programa_ids)
            ? data.programa_ids.map(Number).filter(id => Number.isInteger(id) && id > 0).slice(0, 100)
            : [];
        if (ids.length) {
            socket.broadcast.emit('butacas', { programa_ids: ids });
        }
    });
});

const PORT = process.env.PORT || 3000;

httpServer.listen(PORT, () =>
    console.log(`server listening at http://localhost:${PORT}`)
);
