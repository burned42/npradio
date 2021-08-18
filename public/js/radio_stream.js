class RadioStream {
    template = document.querySelector('#card_template');

    constructor(radioName, streamName, initializePlaying)
    {
        this.streamInfoUrl = '/api/radios/' + radioName + '/streams/' + streamName;
        this.requestRunning = false;

        const cardContainer = document.importNode(this.template.content, true);
        this.streamCard = cardContainer.querySelector('#stream_card');
        this.streamCardInitialized = false;

        if (initializePlaying) {
            const streamButton = this.streamCard.querySelector('#stream_button');
            streamButton.classList.remove('btn-dark');
            streamButton.classList.add('btn-npradio');
            streamButton.textContent = '&#x23f8;';
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
            streamButton.onclick = () => playStream(
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
                footer.querySelector('#show_genre_container').classList.remove('invisible');
            } else {
                footer.querySelector('#show_genre_container').classList.add('invisible');
            }

            footer.querySelector('#show_moderator').textContent = streamInfo.show.moderator;
            if (streamInfo.show.start_time !== null && streamInfo.show.end_time !== null) {
                footer.querySelector('#show_start_time').textContent = streamInfo.show.start_time;
                footer.querySelector('#show_end_time').textContent = streamInfo.show.end_time;
                footer.querySelector('#show_time_container').classList.remove('invisible');
            } else {
                footer.querySelector('#show_time_container').classList.remove('invisible');
            }

            if (footer.classList.contains('invisible')) {
                footer.classList.remove('invisible');
            }
        } else if (!footer.classList.contains('invisible')) {
            footer.classList.add('invisible');
        }
    };
}
