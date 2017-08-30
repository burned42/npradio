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
            reject(Error('Network Error'));
        };

        req.send();
    });
}

function RadioStream(radioName, streamName) {
    this.streamInfoUrl = '/api/radios/' + radioName + '/streams/' + streamName;
    this.requestRunning = false;

    this.domElement = document.createElement('div');
    this.domElement.className = 'card invisible';
    document.getElementById('stream_infos').appendChild(this.domElement);

    let self = this;


    this.update = async function () {
        if (self.requestRunning === false) {
            let div = self.domElement;
            self.requestRunning = true;
            let result = await get(self.streamInfoUrl);
            div.innerHTML = self.formatStreamInfo(result);
            div.className = 'card';
            self.requestRunning = false;
        }
    };

    this.formatStreamInfo = function (streamInfo) {
        let html = '';

        html += '<h5 class="card-header text-nowrap">' +
            '<a href="' + streamInfo.homepage + '"> ' + streamInfo.radio_name + ': ' + streamInfo.stream_name + '</a>' +
            '</h5>';

        if (streamInfo.artist === null) {
            streamInfo.artist = 'n/a';
        }
        if (streamInfo.track === null) {
            streamInfo.track = 'n/a';
        }
        html += '<div class="card-body"><strong>' +
            streamInfo.artist + ' </strong>-<strong> ' + streamInfo.track +
            '</strong></div>';

        if (
            streamInfo.show.name !== null
            && streamInfo.show.moderator !== null
        ) {
            html += '<div class="card-footer alert alert-danger mb-0 mb-lg-0 mb-md-0 mb-sm-0 mb-xl-0">';

            html += '<strong>' + streamInfo.show.name + '</strong>';
            if (streamInfo.show.genre !== null) {
                html += ' (' + streamInfo.show.genre + ')';
            }

            html += '<hr>mit <strong>' + streamInfo.show.moderator + '</strong>';
            if (streamInfo.show.start_time !== null && streamInfo.show.end_time !== null) {
                html += ' (' + streamInfo.show.start_time + ' - ' + streamInfo.show.end_time + ')';
            }

            html += '</div>';
        }

        return html;
    };
}

function updateRefreshState() {
    let refresh = document.getElementById('auto_refresh');
    let refresh_label = document.getElementById('auto_refresh_label');

    if (refresh.checked) {
        refresh_label.className = 'btn btn-success';
        updateData();
    } else {
        refresh_label.className = 'btn btn-secondary disabled';
    }
}

function updateData(force = false) {
    let refresh = document.getElementById('auto_refresh');
    if (refresh.checked || force) {
        let requestsRunning = getNumberOfRunningRequests();
        if (requestsRunning >= 3) {
            setTimeout(function () {
                updateData();
            }, 5 * 1000);

            return;
        }

        radioStreams.map(function (radioStream) {
            radioStream.update();
        });

        setTimeout(function () {
            updateData();
        }, 30 * 1000);
    }
}

function getNumberOfRunningRequests() {
    let requestsRunning = 0;
    radioStreams.map(function (radioStream) {
        if (radioStream.requestRunning) {
            requestsRunning++;
        }
    });

    return requestsRunning;
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

updateData(true);
