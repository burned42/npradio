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
        artistElement.innerHTML = streamInfo.artist;
        trackElement.innerHTML = streamInfo.track;

        if (this.streamCardInitialized === false) {
            const streamLink = this.streamCard.querySelector('#stream_link');
            streamLink.setAttribute('href', streamInfo.homepage);
            streamLink.innerHTML = streamInfo.stream_name;
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
            let footerContent = '<strong>' + streamInfo.show.name + '</strong>';
            if (streamInfo.show.genre !== null) {
                footerContent += ' (' + streamInfo.show.genre + ')';

            }
            footerContent += '<hr>mit <strong>' + streamInfo.show.moderator + '</strong>';
            if (streamInfo.show.start_time !== null && streamInfo.show.end_time !== null) {
                footerContent += ' (' + streamInfo.show.start_time + ' - ' + streamInfo.show.end_time + ')';
            }
            footer.innerHTML = footerContent;

            if (footer.classList.contains('invisible')) {
                footer.classList.remove('invisible');
            }
        } else if (!footer.classList.contains('invisible')) {
            footer.classList.add('invisible');
        }
    };
}
