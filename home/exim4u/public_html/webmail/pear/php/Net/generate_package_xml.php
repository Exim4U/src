<?php

require_once 'PEAR/PackageFileManager.php';

$pkg = new PEAR_PackageFileManager();

$build = (isset($argv[1]) && strcmp($argv[1], 'build')!==false) ? true : false;

/**
 * directory settings
 */
$cvsdir  = dirname(__FILE__);
$packagedir = &$cvsdir;

/**
 * package configuration
 */
$category = 'Net';
$package = 'Net_DNS';
$version = '1.0.0';
$state = 'stable';

$summary = 'Resolver library used to communicate with a DNS server.';

$description = <<<EOT
A resolver library used to communicate with a name server to perform DNS queries, zone transfers, dynamic DNS updates, etc.
Creates an object hierarchy from a DNS server response, which allows you to view all of the information given by the DNS server. It bypasses the system resolver library and communicates directly with the server.
EOT;

$notes = <<<EOT
some minor bugfixes and a security fix.
\$phpdns_basedir was removed an require_once statements
related to this variable are now hardcoded.
Bugfix #9162
EOT;

$e = $pkg->setOptions(array(
            'simpleoutput'      => true,
            'baseinstalldir'    => $category,
            'summary'           => $summary,
            'description'       => $description,
            'version'           => $version,
            'license'           => 'PHP License 3.01',
            'packagedirectory'  => $packagedir,
            'pathtopackagefile' => $packagedir,
            'state'             => $state,
            'filelistgenerator' => 'cvs',
            'notes'             => $notes,
            'package'           => $package,
            'dir_roles'         => array(
                                    'docs' => 'doc'
                                    ),
            'ignore'            => array(
                                    '*.xml',
                                    '*.tgz',
                                    'generate_package*',
                                    ),
            ));

if (PEAR::isError($e)) {
    echo $e->getMessage();
    exit;
}

$e = $pkg->addMaintainer('bate', 'lead', 'Marco Kaiser', 'bate@php.net');
$e = $pkg->addMaintainer('fa', 'developer', 'Florian Anderiasch', 'fa@php.net');
//$e = $pkg->addMaintainer('ekilfoil', 'lead', 'Eric Kilfoil', 'eric@ypass.net', 'no');

if (PEAR::isError($e)) {
    echo $e->getMessage();
    exit;
}

$e = $pkg->addDependency('php', '4.2', 'ge', 'php');

$e = $pkg->addGlobalReplacement('package-info', '@package_version@', 'version');
$e = $pkg->addGlobalReplacement('pear-config', '@data_dir@', 'data_dir');

if (PEAR::isError($e)) {
    echo $e->getMessage();
    exit;
}


if ($build) {
    $e = $pkg->writePackageFile();
} else {
    $e = $pkg->debugPackageFile();
}
