<?php
/**
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Smile ElasticSuite to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\Elasticsuite
 * @author    Aurelien FOUCRET <aurelien.foucret@smile.fr>
 * @copyright 2021 Smile
 * @license   Licensed to Smile-SA. All rights reserved. No warranty, explicit or implicit, provided.
 *            Unauthorized copying of this file, via any medium, is strictly prohibited.
 */

$autoloadFilePaths = [
    '../../../vendor/autoload.php',
    'vendor/autoload.php',
];

$autoloadFilePath = 'vendor/autoload.php';

if (file_exists('../../../vendor/autoload.php')) {
    $autoloadFilePath = '../../../vendor/autoload.php';
}

require_once($autoloadFilePath);
