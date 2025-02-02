<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;


$rules = [
    'array_syntax' => ['syntax' => 'short'],
    'array_indentation' => true,
    'align_multiline_comment' => ['comment_type' => 'all_multiline'], //phpdocs_like
    'backtick_to_shell_exec' => true,
    'binary_operator_spaces' => [
        'default' => 'single_space',
        'operators' => ['=>' => 'single_space'], //align_single_space_minimal
    ],
    'global_namespace_import' => [
        'import_classes' => true,
        'import_constants' => true,
        'import_functions' => true,
    ],
    'no_multiline_whitespace_around_double_arrow' => true,
    'compact_nullable_typehint' => true,
    'declare_strict_types' => true,
    'dir_constant' => true,
    'echo_tag_syntax' => [
        'format' => 'short',
    ],
    '@PSR2' => true,
    'ereg_to_preg' => true,
    'fopen_flag_order' => true,
    'fopen_flags' => true,
    'fully_qualified_strict_types' => true,
    'no_binary_string' => true,
    'no_homoglyph_names' => true,
    'no_php4_constructor' => true,
    'no_superfluous_elseif' => true,
    'no_unset_cast' => true,
    'no_unset_on_property' => false,
    'nullable_type_declaration_for_default_null_value' => true,
    'ordered_class_elements' => true,
    'ordered_imports' => true,
    'phpdoc_order_by_value' => true,
    'phpdoc_to_comment' => false,
    'php_unit_construct' => true,
    'php_unit_method_casing' => [
        'case' => 'snake_case',
    ],
    'php_unit_set_up_tear_down_visibility' => true,
    'php_unit_test_case_static_method_calls' => [
        'call_type' => 'self',
    ],
    'blank_line_after_namespace' => true,
    'blank_line_after_opening_tag' => true,
    // 'blank_line_before_statement' => [
    //     'statements' => ['return'],
    // ],
    'trailing_comma_in_multiline' => [
        'elements' => ['arrays', 'arguments', 'parameters'],
    ],
    'braces' => [
        'allow_single_line_closure' => true,
        'position_after_functions_and_oop_constructs' => 'next',
        'position_after_anonymous_constructs' => 'same',
    ],
    'cast_spaces' => true,
    'class_attributes_separation' => [
        'elements' => ['method' => 'one'],
    ],
    'class_definition' => true,
    'concat_space' => [
        'spacing' => 'one',
    ],
    'declare_equal_normalize' => [
        'space' => 'none',
    ],
    'elseif' => true,
    'encoding' => true,
    'full_opening_tag' => true,
    'fully_qualified_strict_types' => true,
    'function_declaration' => true,
    'function_typehint_space' => true,
    'attribute_empty_parentheses' => true,
    'heredoc_to_nowdoc' => true,
    'include' => true,
    'increment_style' => ['style' => 'post'],
    'indentation_type' => true,
    'method_chaining_indentation' => true,
    'backtick_to_shell_exec' => true,
    'linebreak_after_opening_tag' => true,
    'line_ending' => false,
    'lowercase_cast' => true,
    'constant_case' => true,
    'lowercase_keywords' => true,
    'lowercase_static_reference' => true,
    'magic_method_casing' => true,
    'magic_constant_casing' => true,
    'method_argument_space' => [//ensure_single_line//ensure_fully_multiline
        'on_multiline' => 'ensure_fully_multiline',
    ],
    'native_function_casing' => true,
    'no_alias_functions' => true,
    'no_extra_blank_lines' => [
        'tokens' => [
            'extra',
            'throw',
            'use',
            'use_trait',
        ],
    ],
    'no_blank_lines_after_class_opening' => true,
    'no_blank_lines_after_phpdoc' => true,
    'no_closing_tag' => true,
    'no_empty_phpdoc' => true,
    'no_empty_statement' => true,
    'no_leading_import_slash' => true,
    'no_leading_namespace_whitespace' => true,
    'no_mixed_echo_print' => [
        'use' => 'echo',
    ],
    'multiline_whitespace_before_semicolons' => [
        'strategy' => 'no_multi_line',
    ],
    'no_short_bool_cast' => true,
    'no_singleline_whitespace_before_semicolons' => true,
    'no_spaces_after_function_name' => true,
    'no_spaces_around_offset' => true,
    'no_spaces_inside_parenthesis' => true,
    'no_trailing_comma_in_list_call' => true,
    'no_trailing_comma_in_singleline_array' => true,
    'no_trailing_whitespace' => true,
    'no_trailing_whitespace_in_comment' => true,
    // 'no_trailing_comma_in_singleline_function_call' => true,
    'no_unneeded_control_parentheses' => true,
    'no_unreachable_default_argument_value' => true,
    'no_useless_return' => true,
    'return_assignment' => true,
    'visibility_required' => true,
    'no_whitespace_before_comma_in_array' => true,
    'no_whitespace_in_blank_line' => true,
    'normalize_index_brace' => true,
    'return_to_yield_from' => false,
    'not_operator_with_successor_space' => true,
    'object_operator_without_whitespace' => true,
    'ordered_imports' => ['sort_algorithm' => 'alpha'],
    'phpdoc_indent' => true,
    'general_phpdoc_tag_rename' => true,
    'phpdoc_inline_tag_normalizer' => true,
    'phpdoc_tag_type' => true,
    'phpdoc_no_access' => true,
    'phpdoc_no_package' => true,
    'phpdoc_no_useless_inheritdoc' => true,
    'phpdoc_scalar' => true,
    'phpdoc_single_line_var_spacing' => true,
    'phpdoc_summary' => true,
    'phpdoc_to_comment' => true,
    'phpdoc_trim' => true,
    'phpdoc_types' => true,
    'phpdoc_var_without_name' => true,
    'psr_autoloading' => true,
    'self_accessor' => true,
    'short_scalar_cast' => true,
    'simplified_null_return' => false,
    'single_blank_line_at_eof' => true,
    'single_blank_line_before_namespace' => true,
    'single_class_element_per_statement' => true,

    'single_import_per_statement' => true,
    'single_line_after_imports' => true,
    'single_line_comment_style' => [
        'comment_types' => ['hash'],
    ],
    'single_quote' => true,
    'space_after_semicolon' => true,
    'standardize_not_equals' => true,
    'switch_case_semicolon_to_colon' => true,
    'switch_case_space' => true,
    'ternary_operator_spaces' => true,
    'trailing_comma_in_multiline' => true,
    'trim_array_spaces' => true,
    'unary_operator_spaces' => true,
    'visibility_required' => [
        'elements' => ['method', 'property'],
    ],
    'whitespace_after_comma_in_array' => true,
    'no_unused_imports' => true,
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

return $config->setFinder($finder)
    ->setRules($rules)
    ->setRiskyAllowed(true)
    ->setUsingCache(true);