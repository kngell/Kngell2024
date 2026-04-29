<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$rules = [
    // === Basic rulesets ===
    '@PSR12' => true,

    // === Basic formatting and style ===
    'array_syntax' => ['syntax' => 'short'],
    'array_indentation' => true,
    'binary_operator_spaces' => [
        'default' => 'single_space',
        'operators' => [
            '=>' => 'single_space',
            '=' => 'single_space',
        ],
    ],
    'blank_line_after_namespace' => true,
    'blank_line_after_opening_tag' => true,
    'cast_spaces' => true,
    'concat_space' => ['spacing' => 'one'],
    'declare_equal_normalize' => ['space' => 'none'],
    'function_typehint_space' => true,
    'heredoc_to_nowdoc' => true,
    'include' => true,
    'increment_style' => ['style' => 'post'],
    'indentation_type' => true,
    'linebreak_after_opening_tag' => true,
    // REMOVED: 'whitespace_around_symbol_in_multiline_array' => true, // No longer exists
    // REMOVED: 'no_empty_array_short_syntax' => true, // This doesn't exist either
    'lowercase_cast' => true,
    'lowercase_keywords' => true,
    'magic_constant_casing' => true,
    'magic_method_casing' => true,
    'method_argument_space' => [
        'on_multiline' => 'ensure_fully_multiline',
        'keep_multiple_spaces_after_comma' => false,
    ],
    'native_function_casing' => true,
    'new_with_braces' => true,
    'no_blank_lines_after_class_opening' => true,
    'no_blank_lines_after_phpdoc' => true,
    'no_closing_tag' => true,
    'no_empty_phpdoc' => true,
    'no_empty_statement' => true,
    'no_extra_blank_lines' => [
        'tokens' => [
            'extra',
            'throw',
            'use',
            'use_trait',
            'break',
            'case',
            'continue',
            'curly_brace_block',
            'default',
            'parenthesis_brace_block',
            'return',
            'square_brace_block',
            'switch',
            'attribute',
        ],
    ],
    'no_leading_import_slash' => true,
    'no_leading_namespace_whitespace' => true,
    'no_mixed_echo_print' => ['use' => 'echo'],
    'no_short_bool_cast' => true,
    'no_singleline_whitespace_before_semicolons' => true,
    'no_spaces_after_function_name' => true,
    'no_spaces_around_offset' => true,
    'no_spaces_inside_parenthesis' => true,
    'no_trailing_comma_in_singleline' => true,
    'no_trailing_whitespace' => true,
    'no_trailing_whitespace_in_comment' => true,
    'no_unneeded_control_parentheses' => true,
    'no_unreachable_default_argument_value' => true,
    'no_unused_imports' => true,
    'no_useless_return' => true,
    'no_whitespace_before_comma_in_array' => true,
    'object_operator_without_whitespace' => true,
    'ordered_imports' => [
        'sort_algorithm' => 'alpha',
        'imports_order' => ['class', 'function', 'const'],
    ],
    'return_assignment' => true,
    'return_type_declaration' => ['space_before' => 'none'],
    'short_scalar_cast' => true,
    'single_blank_line_at_eof' => true,
    'single_class_element_per_statement' => true,
    'single_import_per_statement' => true,
    'single_line_after_imports' => true,
    'single_line_comment_style' => ['comment_types' => ['hash']],
    'single_quote' => true,
    'space_after_semicolon' => true,
    'standardize_not_equals' => true,
    'switch_case_semicolon_to_colon' => true,
    'switch_case_space' => true,
    'ternary_operator_spaces' => true,
    'trailing_comma_in_multiline' => [
        'elements' => ['arrays', 'arguments', 'parameters'],
    ],
    'trim_array_spaces' => true,
    'unary_operator_spaces' => true,
    'visibility_required' => [
        'elements' => ['method', 'property'],
    ],
    'whitespace_after_comma_in_array' => true,

    // === PHPDoc rules ===
    'phpdoc_indent' => true,
    'phpdoc_inline_tag_normalizer' => true,
    'phpdoc_no_access' => true,
    'phpdoc_no_package' => true,
    'phpdoc_no_useless_inheritdoc' => true,
    'phpdoc_order' => true,
    'phpdoc_order_by_value' => [
        'annotations' => [
            'author',
            'covers',
            'coversNothing',
            'dataProvider',
            'depends',
            'group',
            'internal',
            'method',
            'property',
            'property-read',
            'property-write',
            'requires',
            'throws',
            'uses',
        ],
    ],
    'phpdoc_scalar' => true,
    'phpdoc_separation' => true,
    'phpdoc_single_line_var_spacing' => true,
    'phpdoc_summary' => true,
    'phpdoc_to_comment' => true,
    'phpdoc_trim' => true,
    'phpdoc_types' => true,
    'phpdoc_var_without_name' => true,

    // === Modern PHP & safety ===
    'compact_nullable_typehint' => true,
    'fully_qualified_strict_types' => true,
    'declare_strict_types' => true,
    'no_binary_string' => true,
    'no_homoglyph_names' => true,
    'no_php4_constructor' => true,
    'no_superfluous_elseif' => true,
    'no_unset_cast' => true,
    'nullable_type_declaration_for_default_null_value' => true,

    // === Class elements ordering ===
    'ordered_class_elements' => [
        'order' => [
            'use_trait',
            'constant_public',
            'constant_protected',
            'constant_private',
            'property_public',
            'property_protected',
            'property_private',
            'property_public_static',
            'property_protected_static',
            'property_private_static',
            'construct',
            'destruct',
            'magic',
            'phpunit',
            'method_public',
            'method_protected',
            'method_private',
            'method_public_static',
            'method_protected_static',
            'method_private_static',
        ],
        'sort_algorithm' => 'none',
    ],

    'class_attributes_separation' => [
        'elements' => [
            'method' => 'one',
            'property' => 'none',
        ],
    ],

    // === Modern PHP 8+ features ===
    'modernize_types_casting' => true,
    'no_useless_nullsafe_operator' => true,

    // === Attributes ===
    'ordered_attributes' => [
        'order' => [
            'AllowDynamicProperties',
            'ReturnTypeWillChange',
            'Deprecated',
            'SuppressWarnings',
            'Override',
        ],
        'sort_algorithm' => 'alpha',
    ],
];

$finder = Finder::create()
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/App',
    ])
    ->name('*.php')
    ->notName('*.blade.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

$config = new Config();

return $config
    ->setFinder($finder)
    ->setRules($rules)
    ->setRiskyAllowed(true)
    ->setUsingCache(true)
    ->setIndent('    ')
    ->setLineEnding("\n");