[![CI pipeline](https://github.com/burned42/npradio/actions/workflows/main.yml/badge.svg)](https://github.com/burned42/npradio/actions/workflows/main.yml)
[![code coverage](https://codecov.io/gh/burned42/npradio/branch/master/graph/badge.svg)](https://codecov.io/gh/burned42/npradio)

# ![](https://raw.githubusercontent.com/burned42/npradio/master/public/favicon-32x32.png) Now Playing Radio

NPRadio is a webpage that collects information from different radio stations
and displays the current playing songs, if there is a moderator online and
how the show is named etc. and offers the possibility to play these streams
directly in your browser.

A Docker image is available at `ghcr.io/burned42/npradio`.

## Development

Building/Updating the docker image:

    docker compose build --pull

Dependency updates:

    # Update composer packages
    docker compose run --rm app composer update

    # Check for symfony flex recipe updates
    docker compose run --rm app composer recipes --outdated

    # Update symfony asset mapper importmap
    docker compose run --rm app composer update-importmap

Run all the tests:

    docker compose run --rm app composer run-checks
