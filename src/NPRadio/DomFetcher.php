<?php

namespace NPRadio;

interface DomFetcher
{
    public function getXmlDom($url): \DOMDocument;

    public function getHtmlDom($url): \DOMDocument;
}