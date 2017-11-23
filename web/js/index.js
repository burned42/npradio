function get(url) {
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

class RadioStream {
    constructor(radioName, streamName) {
        this.streamInfoUrl = '/api/radios/' + radioName + '/streams/' + streamName;
        this.requestRunning = false;

        this.domElement = document.createElement('div');
        this.domElement.className = 'card invisible';
        this.domElementInitialized = false;
        this.cardBody = '';
        this.cardFooter = '';
        this.cardFooterAppended = false;

        document.getElementById('stream_infos').appendChild(this.domElement);

        this.update = this.update.bind(this);
        this.updateStreamInfoDom = this.updateStreamInfoDom.bind(this);
    }

    async update() {
        if (this.requestRunning === false) {
            this.requestRunning = true;
            let result = await get(this.streamInfoUrl);
            this.updateStreamInfoDom(result);
            this.requestRunning = false;
        }
    };

    updateStreamInfoDom(streamInfo) {
        if (streamInfo.artist === null) {
            streamInfo.artist = 'n/a';
        }
        if (streamInfo.track === null) {
            streamInfo.track = 'n/a';
        }

        let bodyContent = '<strong>' + streamInfo.artist + '</strong>' +
            '<span class="text-muted px-2 px-sm-2 px-md-2 px-lg-2 px-xl-2">-</span>' +
            '<strong>' + streamInfo.track + '</strong>';

        let footerContent = false;
        if (
            streamInfo.show.name !== null
            && streamInfo.show.moderator !== null
        ) {

            footerContent = '<strong>' + streamInfo.show.name + '</strong>';
            if (streamInfo.show.genre !== null) {
                footerContent += ' (' + streamInfo.show.genre + ')';
            }

            footerContent += '<hr>mit <strong>' + streamInfo.show.moderator + '</strong>';
            if (streamInfo.show.start_time !== null && streamInfo.show.end_time !== null) {
                footerContent += ' (' + streamInfo.show.start_time + ' - ' + streamInfo.show.end_time + ')';
            }
        }

        if (this.domElementInitialized === false) {
            // add header
            let header = document.createElement('h5');
            header.className = 'card-header text-nowrap d-flex justify-content-between';

            let headerLink = document.createElement('a');
            headerLink.className = 'my-auto';
            headerLink.setAttribute('href', streamInfo.homepage);
            headerLink.setAttribute('target', '_blank');
            let streamTitle = streamInfo.radio_name + ': ' + streamInfo.stream_name;
            headerLink.innerHTML = streamTitle;
            header.appendChild(headerLink);

            let headerButton = document.createElement('button');
            headerButton.className = 'btn btn-secondary';
            headerButton.name = 'play_stream';
            headerButton.onclick = function () {
                playStream(this, streamInfo.stream_url, streamTitle);
            };
            headerButton.innerHTML = '&#x25b6';
            header.appendChild(headerButton);

            this.domElement.appendChild(header);


            // add body
            this.cardBody = document.createElement('div');
            this.cardBody.className = 'card-body';
            this.domElement.appendChild(this.cardBody);

            // add footer
            this.cardFooter = document.createElement('div');
            this.cardFooter.className = 'card-footer alert alert-danger mb-0';

            this.domElement.className = 'card';
            this.domElementInitialized = true;
        }

        this.cardBody.innerHTML = bodyContent;

        if (footerContent) {
            this.cardFooter.innerHTML = footerContent;
            if (this.cardFooterAppended === false) {
                this.domElement.appendChild(this.cardFooter);
                this.cardFooterAppended = true;
            }
        } else if (this.cardFooterAppended) {
            this.domElement.removeChild(this.cardFooter);
            this.cardFooterAppended = false;
        }
    };
}

function initializePlayButtonsToPaused() {
    let playButtons = document.getElementsByName('play_stream');
    for (let i = 0; i < playButtons.length; i++) {
        playButtons[i].className = 'btn btn-secondary';
        playButtons[i].innerHTML = '&#x25b6;';
    }
}

function playStream(e, streamUrl, streamTitle) {
    initializePlayButtonsToPaused();

    let streamPlayer = document.getElementById('stream_player');
    let wasPaused = streamPlayer.paused;
    streamPlayer.pause();

    if (streamPlayer.getAttribute('src') !== streamUrl || wasPaused === true) {
        streamPlayer.setAttribute('src', streamUrl);
        streamPlayer.play();

        e.className = 'btn btn-success';
        e.innerHTML = '&#x23f8;';

        document.title = 'NPRadio | ' + streamTitle;
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

function updateData() {
    if (getNumberOfRunningRequests() < 3) {
        radioStreams.map(function (radioStream) {
            radioStream.update();
        });

        let lastUpdated = document.getElementById('last_updated');
        let currentDate = new Date();
        lastUpdated.innerHTML = '&#x21bb; ' + currentDate.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
    }
}


let streams = [
    ['RauteMusik', 'Main'],
    ['RauteMusik', 'Club'],
    ['TechnoBase', 'TechnoBase'],
    ['RauteMusik', 'House'],
    ['TechnoBase', 'HouseTime'],
    ['TechnoBase', 'ClubTime'],
    ['RauteMusik', 'Rock'],
    ['RauteMusik', 'WackenRadio'],
    ['MetalOnly', 'MetalOnly']
];

let radioStreams = [];
streams.map(function (stream) {
    radioStreams.push(new RadioStream(stream[0], stream[1]));
});
updateData();

setInterval(function () {
    updateData();
}, 60 * 1000);
