<?php

require_once 'MetalOnly.php';
require_once 'TechnoBase.php';

$metalOnly = new Burned\NPRadio\MetalOnly();
var_dump($metalOnly->getInfo());

$technoBase = new Burned\NPRadio\TechnoBase('TechnoBase');
var_dump($technoBase->getInfo());

$houseTime = new Burned\NPRadio\TechnoBase('HouseTime');
var_dump($houseTime->getInfo());