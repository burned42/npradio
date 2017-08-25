function get (url) {
    return new Promise(function (resolve, reject) {
        let req = new XMLHttpRequest();
        req.open('GET', url);

        req.onload = function () {
            if (req.status === 200) {
                resolve(JSON.parse(req.responseText));
            }
            else {
                reject(Error(req.statusText));
            }
        };

        req.onerror = function () {
            reject(Error("Network Error"));
        };

        req.send();
    });
}

function RadioStream(radioName, streamName) {
    this.streamInfoUrl = "/api/radios/" + radioName + '/streams/' + streamName;

    this.domElement = document.createElement('div');
    this.domElement.className = 'stream_info';
    document.getElementById('stream_infos').appendChild(this.domElement);

    let self = this;

    this.update = async function () {
        let div = self.domElement;
        let result = await get(self.streamInfoUrl);
        div.innerHTML = self.formatStreamInfo(result);
    };

    this.formatStreamInfo = function (streamInfo) {
        let html = "<table>";

        html += "<tr>" +
            "<td class='label'><a href='" + streamInfo.homepage + "'> " + streamInfo.radio_name + "</a></td>" +
            "<td>" + streamInfo.stream_name + "</td>" +
            "</tr>";

        if (streamInfo.show.name !== null) {
            html += "<tr><td>Show</td><td>" + streamInfo.show.name + "</td></tr>"
        }
        if (streamInfo.show.genre !== null) {
            html += "<tr><td>Genre</td><td>" + streamInfo.show.genre + "</td></tr>";
        }
        if (streamInfo.show.moderator !== null) {
            html += "<tr><td>Moderator</td><td>" + streamInfo.show.moderator + "</td></tr>";
        }
        if (streamInfo.show.start_time !== null && streamInfo.show.end_time !== null) {
            html += "<tr><td>Showtime</td><td>" + streamInfo.show.start_time + " - " + streamInfo.show.end_time + "</td></tr>";
        }

        if (streamInfo.artist === null) {
            streamInfo.artist = 'n/a';
        }
        if (streamInfo.track === null) {
            streamInfo.track = 'n/a';
        }
        html += "<tr><td>Track</td><td>" + streamInfo.artist + " - " + streamInfo.track + "</td></tr>";

        html += "</table>";

        return html;
    };
}

let streams = [
    ['RauteMusik', 'Main'],
    ['RauteMusik', 'Club'],
    ['TechnoBase', 'TechnoBase'],
    ['RauteMusik', 'House'],
    ['TechnoBase', 'HouseTime'],
    ['RauteMusik', 'Rock'],
    ['RauteMusik', 'WackenRadio'],
    ['MetalOnly', 'MetalOnly']
];

let radioStreams = [];
streams.map(function (stream) {
    radioStreams.push(new RadioStream(stream[0], stream[1]));
});

function update() {
    radioStreams.map(function (radioStream) {
        radioStream.update();
    });

    setTimeout(function () {
        update();
    }, 30 * 1000);
}

update();
