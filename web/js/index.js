function initializePlayButtonsToPaused() {
    let playButtons = document.getElementsByName('play_stream');
    for (let i = 0; i < playButtons.length; i++) {
        playButtons[i].className = 'btn btn-secondary';
        playButtons[i].innerHTML = '&#x25b6;';
    }
    nowPlayingRadioStream = null;
}

function playStream(e, streamUrl, radioName, streamName) {
    initializePlayButtonsToPaused();

    let streamPlayer = document.getElementById('stream_player');
    let wasPaused = streamPlayer.paused;
    streamPlayer.pause();

    if (streamPlayer.getAttribute('src') !== streamUrl || wasPaused === true) {
        streamPlayer.setAttribute('src', streamUrl);
        e.className = 'btn btn-primary';
        streamPlayer.play().then(function () {
            e.innerHTML = '&#x23f8;';

            document.title = 'NPRadio | ' + radioName + ': ' + streamName;
            nowPlayingRadioStream = [radioName, streamName];
        }).catch(function () {
            e.className = 'btn btn-warning';
            nowPlayingRadioStream = null;
        });
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
        lastUpdated.innerHTML = '&#x21bb; '
            + currentDate.toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });
    }
}

function resetLocalStreamSelection() {
    localStreamSelection = defaultStreams;
    localStorage.streamSelection = JSON.stringify(defaultStreams);

    showStreamInfo();
}

function showSettings() {
    document.getElementById('stream_infos').innerHTML = '';

    let settings = document.getElementById('settings');

    let preselectStreams = [];
    let otherStreams = availableStreams.slice();
    for (let i = 0; i < localStreamSelection.length; i++) {
        let localStreamData = localStreamSelection[i];
        for (let j = otherStreams.length - 1; j >= 0; j--) {
            let streamData = otherStreams[j];
            if (localStreamData[0] === streamData[0] && localStreamData[1] === streamData[1]) {
                preselectStreams.push(localStreamData);
                otherStreams.splice(j, 1);
                break;
            }
        }
    }

    let allStreams = preselectStreams.concat(otherStreams);

    let text = '<form>' +
        '<div class="card">' +
        '    <div class="card-body">' +
        '        <div class="list-group" id="stream_selection">';
    allStreams.map(function (streamData) {
        let checked = '';
        preselectStreams.map(function (selectedData) {
            if (selectedData[0] === streamData[0] && selectedData[1] === streamData[1]) {
                checked = ' checked="checked"';
            }
        });

        text += '<div class="selectable_stream list-group-item list-group-item-action">' +
            '    <div class="form-check">' +
            '        <input class="form-check-input" type="checkbox" name="stream_setting_selection" value="' + streamData[0] + '_' + streamData[1] + '" ' + checked + '>' +
            '        ' + streamData[0] + ': ' + streamData[1] +
            '        <a href="#" class="float-right">&#x2630;</a>' +
            '    </div>' +
            '</div>';
    });
    text += '</div>' +
        '        </div>' +
        '        <div class="card-footer">' +
        '            <button class="btn btn-primary" type="button" onclick="saveSettings()">&#10003;</button>' +
        '            &nbsp;<button class="btn btn-secondary" type="button" onclick="showStreamInfo()">&#x2715;</button>' +
        '            &nbsp;<button class="btn btn-danger float-right" type="button" onclick="resetLocalStreamSelection()">&#x21bb;</button>' +
        '        </div>' +
        '    </div>' +
        '</form>';
    settings.innerHTML = text;

    const settingsForm = document.getElementById('stream_selection');
    new DragonDrop(settingsForm, {
        item: '#selectable_stream',
        handle: false,
    });

    settings.style.display = 'block';
}

function saveSettings() {
    let streamSettings = document.getElementsByName('stream_setting_selection');

    let selectedStreams = [];
    for (let i = 0; i < streamSettings.length; i++) {
        if (streamSettings[i].checked) {
            selectedStreams.push(streamSettings[i].value.split('_'));
        }
    }

    localStreamSelection = selectedStreams;
    localStorage.streamSelection = JSON.stringify(selectedStreams);

    showStreamInfo();
}

function showStreamInfo() {
    document.getElementById('settings').style.display = 'none';
    document.getElementById('stream_infos').innerHTML = '';
    radioStreams = [];

    localStreamSelection.map(function (stream) {
        let playing = false;
        if (
            nowPlayingRadioStream !== null
            && nowPlayingRadioStream[0] === stream[0]
            && nowPlayingRadioStream[1] === stream[1]
        ) {
            playing = true;
        }
        radioStreams.push(new RadioStream(stream[0], stream[1], playing));
    });
    updateData();
}

function setAvailableRadioStreams() {
    fetch('/api/radios')
        .then(data => data.json())
        .then(radios => {
            radios.map(async radio => {
                fetch('/api/radios/' + radio + '/streams')
                    .then(data => data.json())
                    .then(streams => {
                        streams.map(async stream => {
                            let found = availableStreams.find(element => {
                                return element[0] === radio && element[1] === stream;
                            });
                            if (typeof found === 'undefined') {
                                availableStreams.push([radio, stream]);
                            }
                        });
                    });
            });
        });
}

let defaultStreams = [
    ['RauteMusik', 'Main'],
    ['Radio Galaxy', 'Mittelfranken'],
    ['RauteMusik', 'Club'],
    ['TechnoBase', 'TechnoBase'],
    ['RauteMusik', 'House'],
    ['TechnoBase', 'HouseTime'],
    ['STAR FM', 'Nürnberg'],
    ['RauteMusik', 'Rock'],
    ['RauteMusik', 'WackenRadio'],
    ['Metal Only', 'Metal Only']
];
let nowPlayingRadioStream = null;

let localStreamSelection = defaultStreams;
if (localStorage.streamSelection) {
    localStreamSelection = JSON.parse(localStorage.streamSelection);
}

let availableStreams = localStreamSelection.slice();
setAvailableRadioStreams();

let radioStreams = [];
showStreamInfo();

setInterval(function () {
    try {
        updateData();
    } catch (e) {
    }
}, 30 * 1000);

