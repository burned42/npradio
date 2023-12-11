/*
 * Welcome to your app's main JavaScript file!
 *
 * This file will be included onto the page via the importmap() Twig function,
 * which should already be in your base.html.twig.
 */
import './styles/app.css'
import 'bootstrap/dist/css/bootstrap.min.css';

import RadioStream from "./radio_stream.js";
import {Sortable} from "./vendor/sortablejs/sortablejs.index.js";

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

    const settingsTemplate = document.getElementById('settings_template');
    const settingsElement = document.importNode(settingsTemplate.content, true);

    const streamSettingList = settingsElement.getElementById('stream_selection');
    const streamSettingTemplate = document.getElementById('setting_stream_template');
    const allStreams = preselectStreams.concat(otherStreams);
    allStreams.map(streamData => {
        const streamElement = document.importNode(streamSettingTemplate.content, true);
        streamElement.getElementById('radio_name').textContent = streamData[0];
        streamElement.getElementById('stream_name').textContent = streamData[1];
        const checkbox = streamElement.querySelector('input[name="stream_setting_selection"]');
        checkbox.value = streamData[0] + '_' + streamData[1];
        preselectStreams.map(selectedData => {
            if (selectedData[0] === streamData[0] && selectedData[1] === streamData[1]) {
                checkbox.checked = true;
            }
        });

        streamSettingList.appendChild(streamElement);
    });

    const settings = document.getElementById('settings');
    settings.appendChild(settingsElement);

    document.getElementById('button-settings-save').onclick = saveSettings;
    document.getElementById('button-settings-back').onclick = showStreamInfo;
    document.getElementById('button-settings-reset').onclick = resetLocalStreamSelection;

    new Sortable(streamSettingList, {
        delay: 100,
        delayOnTouchOnly: true,
        ghostClass: 'dragging',
        chosenClass: 'dragging',
        dragClass: 'dragging'
    });
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
    document.getElementById('settings').innerHTML = '';
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
        radioStreams.push(new RadioStream(stream[0], stream[1], playing, playStream));
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
    if (document.getElementById('settings').innerHTML !== '') {
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
    ['RauteMusik', 'RauteMusik Metal'],
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

document.getElementById('npradio-logo').onclick = showStreamInfo;
document.getElementById('button-settings').onclick = showSettings;
