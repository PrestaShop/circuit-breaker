<?php

$finder = PhpCsFixer\Finder::create()->in([
    __DIR__.'/src',
    __DIR__.'/tests',
]);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        'concat_space' => [
            'spacing' => 'one',
        ],
        'cast_spaces' => [
            'space' => 'single',
        ],
        'error_suppression' => [
            'mute_deprecation_error' => false,
            'noise_remaining_usages' => false,
            'noise_remaining_usages_exclude' => [],
        ],
        'function_to_constant' => false,
        'no_alias_functions' => false,
        'non_printable_character' => false,
        'phpdoc_summary' => false,
        'phpdoc_align' => [
            'align' => 'left',
        ],
        'protected_to_private' => false,
        'psr_autoloading' => false,
        'self_accessor' => false,
        'yoda_style' => false,
        'non_printable_character' => true,
        'phpdoc_no_empty_return' => false,
    ])
    ->setFinder($finder)
    ->setCacheFile(__DIR__.'/.php_cs.cache')
;
