function initializePlayButtonsToPaused()
{
    const playButtons = document.getElementsByName('play_stream');
    for (let i = 0; i < playButtons.length; i++) {
        playButtons[i].className = 'btn btn-dark';
        playButtons[i].innerHTML = '&#x25b6;';
    }
    nowPlayingRadioStream = null;
}

function playStream(e, streamUrl, radioName, streamName)
{
    initializePlayButtonsToPaused();

    const streamPlayer = document.getElementById('stream_player');
    const wasPaused = streamPlayer.paused;
    streamPlayer.pause();

    if (streamPlayer.getAttribute('src') !== streamUrl || wasPaused === true) {
        streamPlayer.setAttribute('src', streamUrl);
        streamPlayer.onpause = () => {
            e.className = 'btn btn-dark';
            e.innerHTML = '&#x25b6;';
        };
        streamPlayer.onplaying = () => {
            e.className = 'btn btn-npradio';
            e.innerHTML = '&#x23f8;';
        };

        e.className = 'btn btn-npradio';
        document.getElementById('npradio_title').innerText = streamName;
        streamPlayer.play()
            .then(() => nowPlayingRadioStream = [radioName, streamName])
            .catch(() => {
                e.className = 'btn btn-warning';
                nowPlayingRadioStream = null;
            });
    }
}

function getNumberOfRunningRequests()
{
    let requestsRunning = 0;
    radioStreams.map(radioStream => {
        if (radioStream.requestRunning) {
            requestsRunning++;
        }
    });

    return requestsRunning;
}

function updateData()
{
    if (getNumberOfRunningRequests() < 3) {
        radioStreams.map((radioStream) => radioStream.update());

        const lastUpdated = document.getElementById('last_updated');
        const currentDate = new Date();
        lastUpdated.innerHTML = '&#x21bb; '
            + currentDate.toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit',
                hour12: false
            });
    }
}

function resetLocalStreamSelection()
{
    localStreamSelection = defaultStreams;
    localStorage.streamSelection = JSON.stringify(defaultStreams);

    showStreamInfo();
}

function showSettings()
{
    document.getElementById('stream_infos').innerHTML = '';
    clearInterval(updateInterval);

    const settings = document.getElementById('settings');

    const preselectStreams = [];
    const otherStreams = availableStreams.slice();
    for (let i = 0; i < localStreamSelection.length; i++) {
        const localStreamData = localStreamSelection[i];
        for (let j = otherStreams.length - 1; j >= 0; j--) {
            let streamData = otherStreams[j];
            if (localStreamData[0] === streamData[0] && localStreamData[1] === streamData[1]) {
                preselectStreams.push(localStreamData);
                otherStreams.splice(j, 1);
                break;
            }
        }
    }

    const allStreams = preselectStreams.concat(otherStreams);

    let text = '<form>' +
        '<div class="col">' +
        '    <div class="card">' +
        '        <div class="card-header">' +
        '            <button class="btn btn-npradio" type="button" onclick="saveSettings()">&#10003;</button>' +
        '            &nbsp;<button class="btn btn-secondary" type="button" onclick="showStreamInfo()">&#x2715;</button>' +
        '            &nbsp;<button class="btn btn-danger float-end" type="button" onclick="resetLocalStreamSelection()">&#x21bb;</button>' +
        '        </div>' +
        '        <div class="card-body">' +
        '            <div class="list-group" id="stream_selection">';
    allStreams.map(streamData => {
        let checked = '';
        preselectStreams.map(selectedData => {
            if (selectedData[0] === streamData[0] && selectedData[1] === streamData[1]) {
                checked = ' checked="checked"';
            }
        });

        text += '<div class="selectable_stream list-group-item text-light">' +
            '    <div class="form-check">' +
            '        <input class="form-check-input" type="checkbox" name="stream_setting_selection" value="' + streamData[0] + '_' + streamData[1] + '" ' + checked + '>' +
            '        ' + streamData[0] + ': <b>' + streamData[1] + '</b>' +
            '        <a href="#" class="float-end">&#x2630;</a>' +
            '    </div>' +
            '</div>';
    });
    text += '</div>' +
        '            </div>' +
        '        </div>' +
        '    </div>' +
        '</form>';
    settings.innerHTML = text;

    const streamSelection = document.getElementById('stream_selection');
    new Sortable(streamSelection, {
        delay: 100,
        delayOnTouchOnly: true,
        ghostClass: 'dragging',
        chosenClass: 'dragging',
        dragClass: 'dragging'
    });

    settings.classList.remove('invisible');
}

function saveSettings()
{
    const streamSettings = document.getElementsByName('stream_setting_selection');

    const selectedStreams = [];
    for (let i = 0; i < streamSettings.length; i++) {
        if (streamSettings[i].checked) {
            selectedStreams.push(streamSettings[i].value.split('_'));
        }
    }

    localStreamSelection = selectedStreams;
    localStorage.streamSelection = JSON.stringify(selectedStreams);

    showStreamInfo();
}

function showStreamInfo()
{
    document.getElementById('settings').classList.add('invisible');
    document.getElementById('stream_infos').innerHTML = '';
    radioStreams = [];

    localStreamSelection.map(stream => {
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

    setUpdateInterval();
}

function setAvailableRadioStreams()
{
    fetch('/api/radios')
        .then(data => data.json())
        .then(radios => {
            let allStreams = [];
            radios.map(async radio => {
                fetch('/api/radios/' + radio + '/streams')
                    .then(data => data.json())
                    .then(streams => {
                        streams.map(async stream => {
                            allStreams.push([radio, stream]);
                        });
                    });
            });

            availableStreams = allStreams;
        });
}

function setUpdateInterval()
{
    if (!document.getElementById('settings').classList.contains('invisible')) {
        return;
    }

    updateData();

    clearInterval(updateInterval)
    updateInterval = setInterval(updateData, 30 * 1000);
}

const defaultStreams = [
    ['RauteMusik', 'RauteMusik Main'],
    ['Radio Galaxy', 'Radio Galaxy Mittelfranken'],
    ['RauteMusik', 'RauteMusik Club'],
    ['TechnoBase.FM', 'TechnoBase.FM'],
    ['RauteMusik', 'RauteMusik House'],
    ['TechnoBase.FM', 'HouseTime.FM'],
    ['STAR FM', 'STAR FM Nürnberg'],
    ['RauteMusik', 'RauteMusik Rock'],
    ['RauteMusik', 'Wacken Radio'],
    ['Metal Only', 'Metal Only']
];
let nowPlayingRadioStream = null;

let localStreamSelection = defaultStreams;
if (localStorage.streamSelection) {
    localStreamSelection = JSON.parse(localStorage.streamSelection);
}

let availableStreams = localStreamSelection.slice();
let radioStreams = [];

let updateInterval = null;

window.addEventListener('load', () => {
    setAvailableRadioStreams();
    showStreamInfo();

    window.addEventListener('focus', setUpdateInterval);
    window.addEventListener('online', setUpdateInterval);
    window.addEventListener('offline', () => clearInterval(updateInterval));
});
