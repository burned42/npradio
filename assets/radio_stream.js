export default class RadioStream {
    template = document.getElementById('card_template');

    constructor(radioName, streamName, initializePlaying, playStreamFn)
    {
        this.playStreamFn = playStreamFn;
        this.streamInfoUrl = '/api/radios/' + radioName + '/streams/' + streamName;
        this.requestRunning = false;

        const cardContainer = document.importNode(this.template.content, true);
        this.streamCard = cardContainer.getElementById('stream_card');
        this.streamCardInitialized = false;

        if (initializePlaying) {
            const streamButton = this.streamCard.querySelector('#stream_button');
            streamButton.classList.remove('btn-dark');
            streamButton.classList.add('btn-npradio');
            streamButton.innerHTML = '&#x23f8;';
        }

        document.getElementById('stream_infos').appendChild(cardContainer);

        this.update = this.update.bind(this);
        this.updateStreamInfoDom = this.updateStreamInfoDom.bind(this);
    }

    async update()
    {
        if (this.requestRunning === false) {
            this.requestRunning = true;
            fetch(this.streamInfoUrl)
                .then(response => response.json())
                .then(data => this.updateStreamInfoDom(data))
                .catch(() => {})
                .finally(() => this.requestRunning = false);
        }
    };

    /**
     * @param {Object} streamInfo
     * @param {string} streamInfo.radio_name
     * @param {string} streamInfo.stream_name
     * @param {string} streamInfo.homepage
     * @param {string} streamInfo.stream_url
     * @param {Object} streamInfo.show
     * @param {string|null} streamInfo.show.name
     * @param {string|null} streamInfo.show.genre
     * @param {string|null} streamInfo.show.moderator
     * @param {string|null} streamInfo.show.start_time
     * @param {string|null} streamInfo.show.end_time
     * @param {string|null} streamInfo.artist
     * @param {string|null} streamInfo.track
     */
    updateStreamInfoDom(streamInfo)
    {
        if (streamInfo.artist === null || typeof streamInfo.artist === 'undefined') {
            streamInfo.artist = 'n/a';
        }
        if (streamInfo.track === null || typeof streamInfo.track === 'undefined') {
            streamInfo.track = 'n/a';
        }

        const artistElement = this.streamCard.querySelector('#artist');
        const trackElement = this.streamCard.querySelector('#track');
        artistElement.textContent = streamInfo.artist;
        trackElement.textContent = streamInfo.track;

        if (this.streamCardInitialized === false) {
            const streamLink = this.streamCard.querySelector('#stream_link');
            streamLink.setAttribute('href', streamInfo.homepage);
            streamLink.textContent = streamInfo.stream_name;
            streamLink.classList.remove('placeholder', 'col-3');

            const streamButton = this.streamCard.querySelector('#stream_button');
            streamButton.onclick = () => this.playStreamFn(
                streamButton,
                streamInfo.stream_url,
                streamInfo.radio_name,
                streamInfo.stream_name
            );
            streamButton.attributes.removeNamedItem('disabled');

            artistElement.classList.remove('placeholder', 'col-2');
            trackElement.classList.remove('placeholder', 'col-2');

            this.streamCard.classList.remove('placeholder-glow');

            this.streamCardInitialized = true;
        }

        const footer = this.streamCard.querySelector('#show_info');
        if (
            streamInfo.show.name !== null
            && streamInfo.show.moderator !== null
        ) {
            footer.querySelector('#show_name').textContent = streamInfo.show.name;
            if (streamInfo.show.genre !== null) {
                footer.querySelector('#show_genre').textContent = streamInfo.show.genre;
                footer.querySelector('#show_genre_container').classList.remove('d-none');
            } else {
                footer.querySelector('#show_genre_container').classList.add('d-none');
            }

            footer.querySelector('#show_moderator').textContent = streamInfo.show.moderator;
            if (streamInfo.show.start_time !== null && streamInfo.show.end_time !== null) {
                footer.querySelector('#show_start_time').textContent = streamInfo.show.start_time;
                footer.querySelector('#show_end_time').textContent = streamInfo.show.end_time;
                footer.querySelector('#show_time_container').classList.remove('d-none');
            } else {
                footer.querySelector('#show_time_container').classList.add('d-none');
            }

            footer.classList.remove('d-none');
        } else {
            footer.classList.add('d-none');
        }
    };
}
