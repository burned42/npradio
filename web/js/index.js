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
    this.domElement.className = 'card invisible';
    document.getElementById('stream_infos').appendChild(this.domElement);

    let self = this;
    let requestRunning = false;

    this.update = async function () {
        if (requestRunning === false) {
            let div = self.domElement;
            requestRunning = true;
            let result = await get(self.streamInfoUrl);
            div.innerHTML = self.formatStreamInfo(result);
            div.className = 'card';
            requestRunning = false;
        }
    };

    this.formatStreamInfo = function (streamInfo) {
        let html = "";

        html += "<h5 class='card-header text-nowrap'>" +
            "<a href='" + streamInfo.homepage + "'> " + streamInfo.radio_name + "</a>: " + streamInfo.stream_name +
            "</h5>";

        if (streamInfo.artist === null) {
            streamInfo.artist = 'n/a';
        }
        if (streamInfo.track === null) {
            streamInfo.track = 'n/a';
        }
        html += "<div class='card-body'><strong>" +
            streamInfo.artist + " </strong>-<strong> " + streamInfo.track +
            "</strong></div>";

        if (
            streamInfo.show.name !== null
            && streamInfo.show.moderator !== null
        ) {
            html += "<div class='card-footer alert alert-danger mb-0 mb-lg-0 mb-md-0 mb-sm-0 mb-xl-0'>";

            html += "<strong>" + streamInfo.show.name + "</strong>";
            if (streamInfo.show.genre !== null) {
                html += " (" + streamInfo.show.genre + ")";
            }

            html += "<hr>mit <strong>" + streamInfo.show.moderator + "</strong>";
            if (streamInfo.show.start_time !== null && streamInfo.show.end_time !== null) {
                html += " (" + streamInfo.show.start_time + " - " + streamInfo.show.end_time + ")";
            }

            html += "</div>";
        }

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
