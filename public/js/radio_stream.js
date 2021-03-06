class RadioStream {
    constructor(radioName, streamName, initializePlaying)
    {
        this.streamInfoUrl = '/api/radios/' + radioName + '/streams/' + streamName;
        this.requestRunning = false;
        this.initializePlaying = initializePlaying;

        this.domElement = document.createElement('div');
        this.domElement.className = 'card invisible';
        this.domElementInitialized = false;
        this.cardBody = '';
        this.cardFooter = '';
        this.cardFooterAppended = false;

        let colDiv = document.createElement('div');
        colDiv.className = 'col';
        colDiv.appendChild(this.domElement);

        document.getElementById('stream_infos').appendChild(colDiv);

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
        if (this.domElementInitialized === false) {
            // add header
            let header = document.createElement('h5');
            header.className = 'card-header text-nowrap d-flex justify-content-between';

            let headerLink = document.createElement('a');
            headerLink.className = 'my-auto';
            headerLink.setAttribute('href', streamInfo.homepage);
            headerLink.setAttribute('target', '_blank');
            headerLink.setAttribute('rel', 'noreferrer');
            headerLink.innerHTML = streamInfo.stream_name;
            header.appendChild(headerLink);

            let headerButton = document.createElement('button');
            if (this.initializePlaying) {
                headerButton.className = 'btn btn-npradio';
                headerButton.innerHTML = '&#x23f8;';
            } else {
                headerButton.className = 'btn btn-dark';
                headerButton.innerHTML = '&#x25b6';
            }
            headerButton.name = 'play_stream';
            headerButton.onclick = () => playStream(
                headerButton,
                streamInfo.stream_url,
                streamInfo.radio_name,
                streamInfo.stream_name
            );
            header.appendChild(headerButton);

            this.domElement.appendChild(header);

            // add body
            this.cardBody = document.createElement('div');
            this.cardBody.className = 'card-body';
            this.domElement.appendChild(this.cardBody);

            // add footer
            this.cardFooter = document.createElement('div');
            this.cardFooter.className = 'card-footer mb-0';

            this.domElement.className = 'card h-100';
            this.domElementInitialized = true;
        }

        if (streamInfo.artist === null || typeof streamInfo.artist === 'undefined') {
            streamInfo.artist = 'n/a';
        }
        if (streamInfo.track === null || typeof streamInfo.track === 'undefined') {
            streamInfo.track = 'n/a';
        }

        this.cardBody.innerHTML = '<strong>' + streamInfo.artist + '</strong>' +
            '<span class="text-muted px-2 px-sm-2 px-md-2 px-lg-2 px-xl-2">-</span>' +
            '<strong>' + streamInfo.track + '</strong>';

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
