<?php

namespace Burned\NPRadio;

require_once 'MetalOnly.php';
require_once 'TechnoBase.php';
require_once 'RauteMusik.php';


$metalOnly = new MetalOnly();
var_dump($metalOnly->getInfo());

$technoBase = new TechnoBase(TechnoBase::TECHNOBASE);
var_dump($technoBase->getInfo());

$houseTime = new TechnoBase(TechnoBase::HOUSETIME);
var_dump($houseTime->getInfo());

$rautemusikMain = new RauteMusik(RauteMusik::MAIN);
var_dump($rautemusikMain->getInfo());