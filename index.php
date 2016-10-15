<?php
/**
 * Created by PhpStorm.
 * User: burned
 * Date: 15.10.16
 * Time: 10:27
 */

require_once 'RadioStreamInterface.php';
require_once 'MetalOnly.php';
require_once 'TechnoBase.php';

$metalOnly = new Burned\NPRadio\MetalOnly();
var_dump($metalOnly->getInfo());

$technoBase = new Burned\NPRadio\TechnoBase('TechnoBase');
var_dump($technoBase->getInfo());

$houseTime = new Burned\NPRadio\TechnoBase('HouseTime');
var_dump($houseTime->getInfo());