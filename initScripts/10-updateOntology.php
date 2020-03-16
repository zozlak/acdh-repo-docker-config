#!/usr/bin/php
<?php
/*
 * Checks if the ontology is present in the repo and up to date.
 * If not, imports it.
 */

require_once '/home/www-data/docroot/vendor/autoload.php';
use acdhOeaw\acdhRepoLib\Repo;
use acdhOeaw\acdhRepoLib\exception\NotFound;

$cfgFile      = __DIR__ . '/config.yaml';
$ontologyFile = '/home/www-data/docroot/vendor/acdh-oeaw/arche-schema/acdh-schema.owl';

$cfg                     = yaml_parse_file($cfgFile);
$cfg['composerLocation'] = '/home/www-data/docroot/vendor/autoload.php';
yaml_emit_file($cfgFile, $cfg);

$repo    = Repo::factory($cfgFile);
$import  = true;
try {
    $res    = $repo->getResourceById(preg_replace('/#$/', '', $cfg['schema']['namespaces']['ontology']));
    $hash   = (string) $res->getGraph()->getLiteral($cfg['schema']['hash']);
    $md5    = 'md5:' . md5_file($ontologyFile);
    $sha1   = 'sha1:' . sha1_file($ontologyFile);
    $import = !in_array($hash, [$md5, $sha1]);
} catch (NotFound $e) {

}

if ($import) {
    echo "Importing ontology\n";
    system("php -f /home/www-data/docroot/vendor/acdh-oeaw/arche-schema/importOntologyRdbms.php $cfgFile $ontologyFile");
} else {
    echo "Ontology up to date\n";
}
