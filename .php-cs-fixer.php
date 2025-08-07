<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$rules = [
    // === Basic formatting and style ===
    'array_syntax' => ['syntax' => 'short'],
    'binary_operator_spaces' => ['default' => 'single_space', 'operators' => ['=>' => 'single_space']],
    'cast_spaces' => true,
    'concat_space' => ['spacing' => 'one'],
    'declare_strict_types' => true,
    'declare_equal_normalize' => ['space' => 'none'],
    'encoding' => true,
    'indentation_type' => true,
    'linebreak_after_opening_tag' => true,
    'lowercase_cast' => true,
    'lowercase_keywords' => true,
    'method_argument_space' => ['on_multiline' => 'ensure_fully_multiline'],
    'native_function_casing' => true,
    'no_extra_blank_lines' => [
        'tokens' => [
            'extra', 'throw', 'use', 'use_trait', 'break', 'case', 'continue',
            'curly_brace_block', 'default', 'parenthesis_brace_block', 'return',
            'square_brace_block', 'switch',
        ],
    ],
    'no_singleline_whitespace_before_semicolons' => true,
    'no_trailing_whitespace' => true,
    'no_trailing_whitespace_in_comment' => true,
    'no_whitespace_before_comma_in_array' => true,
    'no_spaces_after_function_name' => true,
    'no_spaces_around_offset' => true,
    'no_spaces_inside_parenthesis' => true,
    'no_unused_imports' => true,
    'single_blank_line_at_eof' => true,
    'blank_line_after_namespace' => true,            // Use this instead of single_blank_line_before_namespace
    'single_class_element_per_statement' => true,
    'single_import_per_statement' => true,
    'single_line_after_imports' => true,
    'single_line_comment_style' => ['comment_types' => ['hash']],
    'single_quote' => true,
    'standardize_not_equals' => true,
    'switch_case_semicolon_to_colon' => true,
    'switch_case_space' => true,
    'ternary_operator_spaces' => true,
    'trailing_comma_in_multiline' => ['elements' => ['arrays', 'arguments', 'parameters']],
    'trim_array_spaces' => true,
    'unary_operator_spaces' => true,
    'visibility_required' => ['elements' => ['method', 'property']],
    'whitespace_after_comma_in_array' => true,

    // === PHPDoc rules ===
    'phpdoc_order_by_value' => true,
    'phpdoc_indent' => true,
    'phpdoc_scalar' => true,
    'phpdoc_summary' => true,
    'phpdoc_trim' => true,
    'phpdoc_types' => true,
    'phpdoc_var_without_name' => true,
    'phpdoc_no_access' => true,
    'phpdoc_no_package' => true,
    'phpdoc_no_useless_inheritdoc' => true,
    'phpdoc_inline_tag_normalizer' => true,
    'phpdoc_to_comment' => true,

    // === Modern PHP & safety ===
    'compact_nullable_typehint' => true,
    'fully_qualified_strict_types' => true,
    'no_binary_string' => true,
    'no_homoglyph_names' => true,
    'no_php4_constructor' => true,
    'no_superfluous_elseif' => true,
    'no_unset_cast' => true,
    'nullable_type_declaration_for_default_null_value' => true,
    'ordered_class_elements' => true,
    'class_attributes_separation' => ['elements' => ['method' => 'one']],
    'class_definition' => true,
    'function_declaration' => true,
    'function_typehint_space' => true,
    'heredoc_to_nowdoc' => true,
    'include' => true,
    'increment_style' => ['style' => 'post'],
    'magic_constant_casing' => true,
    'magic_method_casing' => true,
    'object_operator_without_whitespace' => true,
    'self_accessor' => true,
    'short_scalar_cast' => true,

    // === Cleanup & legacy ===
    'no_blank_lines_after_class_opening' => true,
    'no_blank_lines_after_phpdoc' => true,
    'no_closing_tag' => true,
    'no_empty_phpdoc' => true,
    'no_empty_statement' => true,
    'no_leading_import_slash' => true,
    'no_leading_namespace_whitespace' => true,
    'no_mixed_echo_print' => ['use' => 'echo'],
    'no_short_bool_cast' => true,
    'no_trailing_comma_in_singleline_array' => true,
    'no_unneeded_control_parentheses' => true,
    'no_unreachable_default_argument_value' => true,
    'no_useless_return' => true,
    'return_assignment' => true,
    'return_to_yield_from' => false,

    // === Misc ===
    'backtick_to_shell_exec' => true,
    'dir_constant' => true,
    'echo_tag_syntax' => ['format' => 'short'],
    'blank_line_after_opening_tag' => true,
    'braces' => [
        'allow_single_line_closure' => true,
        'position_after_functions_and_oop_constructs' => 'next',
        'position_after_anonymous_constructs' => 'same',
    ],

    // PHP 8.3+ specific enhancements
    'modernize_types_casting' => true,
    'modernize_strpos' => true,
    'ordered_attributes' => true,
    'no_useless_nullsafe_operator' => true,

    // Allow risky rules for better modern code (adjust if needed)
    '@PSR12' => true,
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
    ->setIndent('    ')  // 4 spaces indent
    ->setLineEnding("\n");