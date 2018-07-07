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
            try {
                fetch(this.streamInfoUrl)
                    .then(data => data.json())
                    .then(result => this.updateStreamInfoDom(result))
                    .then(() => {
                        this.requestRunning = false;
                        console.log('finished')
                    });
            } catch (e) {
            }
        }
    };

    updateStreamInfoDom(streamInfo) {
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
