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

function RadioStream(radioName, streamName) {
    this.streamInfoUrl = '/api/radios/' + radioName + '/streams/' + streamName;
    this.requestRunning = false;

    this.domElement = document.createElement('div');
    this.domElement.className = 'card invisible';
    this.domElementInitialized = false;
    let cardBody;
    let cardFooter;
    let cardFooterAppended = false;

    document.getElementById('stream_infos').appendChild(this.domElement);

    let self = this;

    this.update = async function () {
        if (self.requestRunning === false) {
            self.requestRunning = true;
            let result = await get(self.streamInfoUrl);
            self.updateStreamInfoDom(result);
            self.requestRunning = false;
        }
    };

    this.updateStreamInfoDom = function (streamInfo) {
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

        if (self.domElementInitialized === false) {
            // add header
            let header = document.createElement('h5');
            header.className = 'card-header text-nowrap d-flex justify-content-between';

            let headerLink = document.createElement('a');
            headerLink.className = 'align-self-center';
            headerLink.setAttribute('href', streamInfo.homepage);
            headerLink.setAttribute('target', '_blank');
            headerLink.innerHTML = streamInfo.radio_name + ': ' + streamInfo.stream_name;
            header.appendChild(headerLink);

            let headerButton = document.createElement('button');
            headerButton.className = 'btn btn-secondary';
            headerButton.name = 'play_stream';
            headerButton.onclick = function () {
                playStream(this, streamInfo.stream_url);
            };
            headerButton.innerHTML = '&#x25b6';
            header.appendChild(headerButton);

            self.domElement.appendChild(header);


            // add body
            cardBody = document.createElement('div');
            cardBody.className = 'card-body';
            self.domElement.appendChild(cardBody);

            // add footer
            cardFooter = document.createElement('div');
            cardFooter.className = 'card-footer alert alert-danger mb-0';

            self.domElement.className = 'card';
            self.domElementInitialized = true;
        }

        cardBody.innerHTML = bodyContent;

        if (footerContent) {
            cardFooter.innerHTML = footerContent;
            if (cardFooterAppended === false) {
                self.domElement.appendChild(cardFooter);
                cardFooterAppended = true;
            }
        } else if (cardFooterAppended) {
            self.domElement.removeChild(cardFooter);
            cardFooterAppended = false;
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

function playStream(e, streamUrl) {
    initializePlayButtonsToPaused();

    let streamPlayer = document.getElementById('stream_player');
    let wasPaused = streamPlayer.paused;
    streamPlayer.pause();

    if (streamPlayer.getAttribute('src') !== streamUrl || wasPaused === true) {
        streamPlayer.setAttribute('src', streamUrl);
        streamPlayer.play();

        e.className = 'btn btn-success';
        e.innerHTML = '&#x23f8;';
    }
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
        }, 60 * 1000);
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
