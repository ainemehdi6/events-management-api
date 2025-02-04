<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude('vendor')
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@Symfony' => true,
        'ordered_imports' => true,
        'no_unused_imports' => true,
    ])
    ->setFinder($finder)
    ;