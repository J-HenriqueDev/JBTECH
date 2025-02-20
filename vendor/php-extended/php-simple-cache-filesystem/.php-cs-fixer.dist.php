<?php declare(strict_types=1);

/*
 * This file is part of the dirname(__FILE__) library.
 * 
 * (c) Anastaszor
 * This source file is subject to the MIT license that
 * is bundled with this source code in the file LICENSE.
 */

namespace Composer\Semver;

// remove IDE's non existant class errors on composer installs
// because of the use of composer.phar
if(!\class_exists('Composer\Semver\VersionParser'))
{
	class VersionParser {}
}

namespace PhpCsFixer\Runner\Parallel;

if(!class_exists('PhpCsFixer\Runner\Parallel\ParallelConfigFactory'))
{
	class ParallelConfigFactory {}
}

namespace PhpCsFixer;

// remove IDE's non existant class errors
// because of the use of php-cs-fixer.phar
if(!\class_exists('PhpCsFixer\Finder'))
{
	class Finder {}
}

if(!\class_exists('PhpCsFixer\Config'))
{
	class Config {}
}

$finder = \PhpCsFixer\Finder::create();

$directories = [
	'src', // libraries
	'tests', // tests
];

if(is_dir(__DIR__.DIRECTORY_SEPARATOR.'models'))
{
	// yii2 directory structure
	$directories += ['assets', 'commands', 'config', 'mail', 'models', 'views', 'widgets'];
}

if(is_dir(__DIR__.DIRECTORY_SEPARATOR.'migrations'))
{
	// symfony directory structure
	$directories += ['migrations', 'translations'];
}

if(is_dir(__DIR__.DIRECTORY_SEPARATOR.'app'))
{
	// laravel directory structure
	$directories += ['app', 'database', 'routes'];
}

foreach($directories as $curDir)
{
	$fullPath = __DIR__.DIRECTORY_SEPARATOR.$curDir;
	if(is_dir($fullPath))
	{
		$finder->in($fullPath);
	}
}

$composer_file = file_get_contents(__DIR__.DIRECTORY_SEPARATOR.'composer.json');
$composer_json = json_decode($composer_file, true);

$libname = $composer_json['name'];
$header = <<<EOF
This file is part of the $libname library

(c) Anastaszor
This source file is subject to the MIT license that
is bundled with this source code in the file LICENSE.
EOF;

$doctrine_ignored_tags = [
	'abstract', 'access', 'after', 'afterClass', 'api', 'author',
	'backupGlobals', 'backupStaticAttributes', 'before', 'beforeClass',
	'category', 'copyright', 'code', 'covers', 'coversDefaultClass', 'coversNothing', 'codeCoverageIgnore', 'codeCoverageIgnoreStart', 'codeCoverageIgnoreEnd',
	'deprec', 'deprecated', 'dataProvider', 'depends',
	'encode', 'enduml', 'exception', 'example', 'expectedException', 'expectedExceptionCode', 'expectedExceptionMessage', 'expectedExceptionMessageRegExp', 'extends',
	'final', 'filesource', 'fix', 'FIXME', 'fixme',
	'group', 'global',
	'ingroup', 'implements', 'ignore', 'internal', 'inheritdoc', 'inheritDoc',
	'large', 'license', 'link',
	'magic', 'method', 'medium',
	'name', 'noinspection',
	'override',
	'package', 'package_version', 'param', 'preserveGlobalState', 'private', 'property', 'property-read', 'property-write',
	'return', 'requires', 'runTestsInSeparateProcesses', 'runInSeparateProcess',
	'see', 'since', 'small', 'source', 'static', 'staticvar', 'staticVar', 'startuml', 'subpackage', 'SuppressWarnings',
	'test', 'testdox', 'ticket', 'throw', 'throws', 'toc', 'todo', 'TODO',  'tutorial',
	'uses', 'usedBy',
	'var', 'version',
];

$config = (new \PhpCsFixer\Config())
	->setParallelConfig(\PhpCsFixer\Runner\Parallel\ParallelConfigFactory::detect())
	->setUsingCache(false)
	->setRiskyAllowed(false)
	->setRules([
		// @see https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/blob/master/doc/list.rst
		// @see https://github.com/PHP-CS-Fixer/PHP-CS-Fixer/tree/master/doc/rules
		
		// Alias Rules
		'array_push' => true, // risky
		'backtick_to_shell_exec' => true,
		'ereg_to_preg' => true,     // risky
		'mb_str_functions' => false, // risky, sometimes i don't want to use the 8-bit encoding
		'modernize_strpos' => false, // should be used on php8.0+
		'no_alias_functions' => ['sets' => ['@all']], // risky
		'no_alias_language_construct_call' => true,
		'no_mixed_echo_print' => ['use' => 'echo'],
		'pow_to_exponentiation' => true, // risky
		'random_api_migration' => [ // risky
			'replacements' => [
				'getrandmax' => 'mt_getrandmax',
				'rand' => 'mt_rand',
				'srand' => 'mt_srand'
			],
		],
		'set_type_to_cast' => true, // risky
		
		
		// Array Notation Rules
		'array_syntax' => ['syntax' => 'short'],
		'no_multiline_whitespace_around_double_arrow' => true,
		'no_whitespace_before_comma_in_array' => ['after_heredoc' => false],
		'normalize_index_brace' => true,
		// 'return_to_yield_from' => true, // EXC rules contain unknown fixers
		'trim_array_spaces' => true,
		'whitespace_after_comma_in_array' => ['ensure_single_space' => true],
		'yield_from_array_to_yields' => true,
		
		
		// Basic Rules
		'braces_position' => [
			'allow_single_line_empty_anonymous_classes' => true,
			'allow_single_line_anonymous_functions' => true,
			'anonymous_classes_opening_brace' => 'next_line_unless_newline_at_signature_end',
			'anonymous_functions_opening_brace' => 'next_line_unless_newline_at_signature_end',
			'classes_opening_brace' => 'next_line_unless_newline_at_signature_end',
			'control_structures_opening_brace' => 'next_line_unless_newline_at_signature_end',
			'functions_opening_brace' => 'next_line_unless_newline_at_signature_end',
		],
		'encoding' => true,
		'no_multiple_statements_per_line' => true,
		'no_trailing_comma_in_singleline' => false,
		'non_printable_character' => [ // risky
			'use_escape_sequences_in_strings' => true
		],
		'octal_notation' => false, // php8.1+
		'psr_autoloading' => ['dir' => null], // risky
		'single_line_empty_body' => true,
		
		
		// Casing Rules
		'class_reference_name_casing' => true,
		'constant_case' => ['case' => 'lower'],
		'integer_literal_case' => true,
		'lowercase_keywords' => true,
		'lowercase_static_reference' => true,
		'magic_constant_casing' => true,
		'magic_method_casing' => true,
		'native_function_casing' => true,
		'native_type_declaration_casing' => true,
		
		
		// Cast Notation Rules
		'cast_spaces' => ['space' => 'single'],
		'lowercase_cast' => true,
		'modernize_types_casting' => true, // risky
		'no_short_bool_cast' => true,
		'no_unset_cast' => true,
		'short_scalar_cast' => true,
		
		
		// Class Notation Rules
		/*'class_attributes_separation' => [ // does not work as i expect, there are trailing tabs everywhere
			'elements' => [
				'const' => 'only_if_meta',
				'method' => 'one',
				'property' => 'only_if_meta',
				'trait_import' => 'none',
				'case' => 'none',
			],
		],*/
		'class_definition' => [
			'multi_line_extends_each_single_line' => false,
			'single_item_single_line' => true,
			'single_line' => true,
			'space_before_parenthesis' => false,
			'inline_constructor_arguments' => true,
		],
		'final_class' => false, // risky
		'final_internal_class' => false, // risky
		'final_public_method_for_abstract_class' => false, // risky
		'no_blank_lines_after_class_opening' => false, // i'd prefer to have exactly one
		'no_null_property_initialization' => false, // php7.4+, useless if all fields are typed
		'no_php4_constructor' => true,
		'no_unneeded_final_method' => true, // risky
		'ordered_class_elements' => [
			'order' => [
				'use_trait', 
				'constant_public', 'constant_protected', 'constant_private',
				'property_public_static', 'property_protected_static', 'property_private_static',
				'method_public_abstract_static', 'method_protected_abstract_static', 'method_private_abstract_static',
				'method_public_static', 'method_protected_static', 'method_private_static',
				'property_public', 'property_protected', 'property_private',
				'construct', 'magic',
				'method_public_abstract', 'method_protected_abstract', 'method_private_abstract',
				'method_public', 'method_protected', 'method_private',
				'phpunit', 'destruct',
			],
			'sort_algorithm' => 'none',
			'case_sensitive' => false,
		],
		'ordered_interfaces' => [
			'direction' => 'ascend',
			'order' => 'alpha',
		],
		'ordered_traits' => true,
		'ordered_types' => [
			'sort_algorithm' => 'none', // i'd like to order class names, but not primitives
			'null_adjustment' => 'always_first',
		],
		'protected_to_private' => false, // i'd prefer the opposite
		'self_accessor' => false, // force use of self to be transformed into static (manually), php8.0+
		'self_static_accessor' => false, // i'd prefer the opposite
		'single_class_element_per_statement' => [
			'elements' => ['const', 'property'],
		],
		'single_trait_insert_per_statement' => false, // i'd prefer the opposite
		'visibility_required' => [
			'elements' => ['property', 'method', 'const'],
		],
		
		
		// Class Usage Rules
		'date_time_immutable' => true, // risky
		
		
		// Comment Rules
		'comment_to_phpdoc' => ['ignored_tags' => [
			'codeCoverageIgnoreStart',
			'codeCoverageIgnoreEnd',
		]], // risky
		'header_comment' => [
			'header' => $header,
			'comment_type' => 'comment',
			'location' => 'after_declare_strict',
			'separate' => 'both',
		],
		'multiline_comment_opening_closing' => true,
		'no_empty_comment' => true,
		'no_trailing_whitespace_in_comment' => false, // sometimes i want some
		'single_line_comment_spacing' => true,
		'single_line_comment_style' => [
			'comment_types' => ['asterisk', 'hash'],
		],
		
		
		// Constant Notation Rules
		'native_constant_invocation' => [ // risky
			'exclude' => ['null', 'false', 'true'],
			'fix_built_in' => true,
			'include' => [],
			'scope' => 'all',
			'strict' => false,
		],
		
		
		// Control Structure Rules
		'control_structure_braces' => true,
		'control_structure_continuation_position' => ['position' => 'next_line'],
		'elseif' => true,
		'empty_loop_body' => ['style' => 'braces'],
		'empty_loop_condition' => ['style' => 'while'],
		'include' => true,
		'no_alternative_syntax' => ['fix_non_monolithic_code' => true],
		'no_break_comment' => ['comment_text' => 'No break, cascades'],
		'no_superfluous_elseif' => true,
		'no_unneeded_control_parentheses' => [
			'statements' => [
				'break', 'clone', 'continue', 'echo_print',
				'negative_instanceof', 
				'return', 'switch_case', 'yield', 'yield_from',
			],
		],
		'no_unneeded_braces' => ['namespaces' => true],
		'no_useless_else' => true,
		'simplified_if_return' => true,
		'switch_case_semicolon_to_colon' => true,
		'switch_case_space' => true,
		'switch_continue_to_break' => true,
		'trailing_comma_in_multiline' => [
			'after_heredoc' => true,
			'elements' => ['arguments', 'arrays', /* 'match', 'parameters' */], // php8.0+
		],
		'yoda_style' => [
			'always_move_variable' => true,
			'equal' => true,
			'identical' => true,
			'less_and_greater' => true,
		],
		
		
		// Doctrine Annotation Rules
		'doctrine_annotation_array_assignment' => [
			'ignored_tags' => $doctrine_ignored_tags,
			'operator' => '=',
		],
		'doctrine_annotation_braces' => [
			'ignored_tags' => $doctrine_ignored_tags,
			// 'syntax' => 'with_braces', // risky, may break things with unknown tags
		],
		'doctrine_annotation_indentation' => [
			'ignored_tags' => $doctrine_ignored_tags,
			'indent_mixed_lines' => true,
		],
		'doctrine_annotation_spaces' => [
			'ignored_tags' => $doctrine_ignored_tags,
			'after_argument_assignments' => false,
			'after_array_assignments_colon' => false,
			'after_array_assignments_equals' => false,
			'around_commas' => true,
			'around_parentheses' => false,
			'before_argument_assignments' => false,
			'before_array_assignments_colon' => false,
			'before_array_assignments_equals' => false,
		],
		
		
		// Function Notation Rules
		'combine_nested_dirname' => true, // risky
		'date_time_create_from_format_call' => true,
		'fopen_flag_order' => true, // risky
		'fopen_flags' => ['b_mode' => false], // risky
		'function_declaration' => [
			'closure_function_spacing' => 'none',
			'closure_fn_spacing' => 'none',
			'trailing_comma_single_line' => false,
		],
		'implode_call' => true, // risky
		'lambda_not_used_import' => true,
		'method_argument_space' => [
			'after_heredoc' => false,
			'keep_multiple_spaces_after_comma' => false,
			'on_multiline' => 'ensure_fully_multiline',
		],
		'native_function_invocation' => [ // risky
			'exclude' => [],
			'include' => ['@all'],
			'scope' => 'all',
			'strict' => false,
		],
		'no_spaces_after_function_name' => true,
		'no_unreachable_default_argument_value' => true, // risky
		'no_useless_sprintf' => true,
		'nullable_type_declaration_for_default_null_value' => true,
		'phpdoc_to_param_type' => false, // risky // experimental
		'phpdoc_to_property_type' => false, // risky // experimental
		'phpdoc_to_return_type' => false, // risky // experimental
		'regular_callable_call' => true, // risky
		'return_type_declaration' => ['space_before' => 'one'],
		'single_line_throw' => false,
		'static_lambda' => false, // risky
		'use_arrow_functions' => false, // risky php7.4+
		'void_return' => true, // risky
		
		
		// Import Rules
		'fully_qualified_strict_types' => true,
		'global_namespace_import' => [
			'import_classes' => true,
			'import_constants' => false,
			'import_functions' => false,
		],
		'group_import' => false,
		'no_leading_import_slash' => true,
		'no_unneeded_import_alias' => true,
		'no_unused_imports' => true,
		'ordered_imports' => [
			'sort_algorithm' => 'alpha',
			'imports_order' => ['class', 'const', 'function'],
		],
		'single_import_per_statement' => true,
		'single_line_after_imports' => true,
		
		
		// Language Construct Rules
		'combine_consecutive_issets' => true,
		'combine_consecutive_unsets' => true,
		'declare_equal_normalize' => ['space' => 'none'],
		'declare_parentheses' => true,
		'dir_constant' => true, // risky
		'error_suppression' => [
			'mute_deprecation_error' => true,
			'noise_remaining_usages' => false,
			'noise_remaining_usages_exclude' => [],
		],
		'explicit_indirect_variable' => true,
		'function_to_constant' => [
			'functions' => [
				'get_called_class', 'get_class', 'get_class_this',
				'php_sapi_name', 'phpversion', 'pi'
			],
		],
		'get_class_to_class_keyword' => false, // php8.0+
		'is_null' => true, // risky
		'no_unset_on_property' => true, // risky
		'nullable_type_declaration' => ['syntax' => 'question_mark'],
		'single_space_around_construct' => [
			'constructs_contain_a_single_space' => [
				'yield_from',
			],
			'constructs_preceded_by_a_single_space' => [
				'as', 'use_lambda',
			],
			'constructs_followed_by_a_single_space' => [
				'abstract', 'as', 'attribute',
				'break',
				'case', 'class', 'clone', 'comment', 'const', 'const_import', 'continue',
				'echo', 'enum', 'extends',
				'final', 'function', 'function_import',
				'global', 'goto',
				'implements', 'include', 'include_once', 'instanceof', 'insteadof', 'interface',
				'named_argument', 'new',
				'open_tag_with_echo',
				'php_doc', 'php_open', 'print', 'private', 'protected', 'public',
				'readonly', 'require', 'require_once', 'return',
				'static',
				'throw', 'trait', 'type_colon',
				'use', 'use_lambda', 'use_trait',
				'var',
				'yield', 'yield_from'
			],
		],
		
		
		// List Notation Rules
		'list_syntax' => ['syntax' => 'short'],
		
		
		// Namespace Notation Rules
		'blank_line_after_namespace' => true,
		'blank_lines_before_namespace' => [
			'min_line_breaks' => 2,
			'max_line_breaks' => 2,
		],
		'clean_namespace' => true,
		'no_leading_namespace_whitespace' => true,
		
		// Naming Rules
		'no_homoglyph_names' => true, // risky
		
		// Operator Rules
		'assign_null_coalescing_to_coalesce_equal' => true,
		'binary_operator_spaces' => [
			'default' => 'single_space',
			// all operators as default, '=' and '=>' included
			// only exception is '.', managed with 'concat_space' rule
		],
		'concat_space' => ['spacing' => 'none'],
		'increment_style' => ['style' => 'post'],
		'logical_operators' => true, // risky
		'new_with_parentheses' => [
			'anonymous_class' => true,
			'named_class' => true,
		],
		'no_space_around_double_colon' => true,
		'no_useless_concat_operator' => ['juggle_simple_strings' => true],
		'no_useless_nullsafe_operator' => true,
		'not_operator_with_space' => false,
		'not_operator_with_successor_space' => false,
		'object_operator_without_whitespace' => true,
		'operator_linebreak' => [
			'only_booleans' => false,
			'position' => 'beginning',
		],
		'standardize_increment' => true,
		'standardize_not_equals' => true,
		'ternary_operator_spaces' => true,
		'ternary_to_elvis_operator' => true, // risky
		'ternary_to_null_coalescing' => true,
		'unary_operator_spaces' => true,
		
		
		// PHP Tag Rules
		'blank_line_after_opening_tag' => false,
		'echo_tag_syntax' => [
			'format' => 'long',
			'long_function' => 'echo',
			'shorten_simple_statements_only' => true,
		],
		'full_opening_tag' => true,
		'linebreak_after_opening_tag' => false,
		'no_closing_tag' => true,
		
		
		// PHPUnit Rules
		'php_unit_construct' => [
			'assertions' => [
				'assertSame', 'assertEquals',
				'assertNotEquals', 'assertNotSame',
			],
		],
		'php_unit_data_provider_name' => ['prefix' => 'provide', 'suffix' => ''],
		'php_unit_data_provider_return_type' => true,
		'php_unit_data_provider_static' => ['force' => true],
		'php_unit_dedicate_assert' => ['target' => 'newest'], // risky
		'php_unit_dedicate_assert_internal_type' => ['target' => 'newest'], // risky
		'php_unit_expectation' => ['target' => 'newest'], // risky
		'php_unit_fqcn_annotation' => true,
		'php_unit_internal_class' => ['types' => ['normal', 'final', 'abstract']],
		'php_unit_method_casing' => ['case' => 'camel_case'],
		'php_unit_mock' => ['target' => 'newest'], // risky
		'php_unit_mock_short_will_return' => true,
		'php_unit_namespaced' => ['target' => 'newest'], // risky
		'php_unit_no_expectation_annotation' => [ // risky
			'target' => 'newest',
			'use_class_const' => true,
		],
		'php_unit_set_up_tear_down_visibility' => true,
		'php_unit_size_class' => ['group' => 'small'], // @small, @medium, @large
		'php_unit_strict' => false,
		'php_unit_test_annotation' => ['style' => 'prefix'], // risky
		'php_unit_test_case_static_method_calls' => [
			'call_type' => 'this',
			'methods' => [],
		],
		'php_unit_test_class_requires_covers' => true,
		
		
		// PHPDoc Rules
		'align_multiline_comment' => ['comment_type' => 'all_multiline'],
		'general_phpdoc_annotation_remove' => [
			'annotations' => ['package', 'subpackage'],
			'case_sensitive' => false,
		],
		'general_phpdoc_tag_rename' => [
			'fix_annotation' => true,
			'fix_inline' => true,
			'replacements' => ['inheritDocs' => 'inheritDoc'],
			'case_sensitive' => false,
		],
		'no_blank_lines_after_phpdoc' => true, // disabled ?
		'no_empty_phpdoc' => true,
		'no_superfluous_phpdoc_tags' => false,
		'phpdoc_add_missing_param_annotation' => ['only_untyped' => false],
		'phpdoc_align' => [
			'align' => 'left',
			'tags' => [
				'param',
				'property', 'property-read', 'property-write', 'phpstan-param',
				'phpstan-property', 'phpstan-property-read', 'phpstan-property-write',
				'phpstan-assert', 'phpstan-assert-if-true', 'phpstan-assert-if-false',
				'psalm-param', 'psalm-param-out', 'psalm-property', 'psalm-property-read',
				'psalm-property-write', 'psalm-assert', 'psalm-assert-if-true',
				'psalm-assert-if-false', 'method', 'phpstan-method', 'psalm-method'
			],
		],
		'phpdoc_annotation_without_dot' => true,
		'phpdoc_indent' => true,
		'phpdoc_inline_tag_normalizer' => [
			'tags' => [
				'example', 'id', 'internal', 'inheritdoc', 'inheritdocs',
				'link', 'source', 'toc', 'tutorial',
			],
		],
		'phpdoc_line_span' => [
			'const' => 'multi',
			'method' => 'multi',
			'property' => 'multi',
		],
		'phpdoc_no_access' => true,
		'phpdoc_no_alias_tag' => [
			'replacements' => [
				'property-read' => 'property', 'property-write' => 'property',
				'type' => 'var', 'link' => 'see'
			],
		],
		'phpdoc_no_empty_return' => true,
		'phpdoc_no_package' => true,
		'phpdoc_no_useless_inheritdoc' => true,
		'phpdoc_order' => ['order' => ['param', 'return', 'throws']],
		'phpdoc_order_by_value' => [
			'annotations' => [
				'author',
				'covers',
				'coversNothing',
				'dataProvider',
				'depends',
				'group',
				'internal',
				// 'method', // model classes have it in specific order
				// 'param', // let the actual order of the method/func params
				// 'property', // model classes have it in specific order
				// 'property-read', // model classes have it in specific order
				// 'property-write', // model classes have it in specific order
				'requires',
				'throws',
				'uses',
			],
		],
		'phpdoc_param_order' => true,
		'phpdoc_return_self_reference' => [
			'replacements' => [
				'this' => 'static', '@this' => 'static',
				'$self' => 'self', '@self' => 'self',
				'$static' => 'static', '@static' => 'static',
			],
		],
		'phpdoc_scalar' => [
			'types' => [
				// cant fix bool to boolean
				// cant fix int to integer
				'callback', 'double', 'real', 'str',
			],
		],
		'phpdoc_separation' => false,
		'phpdoc_single_line_var_spacing' => true,
		'phpdoc_summary' => true,
		'phpdoc_tag_casing' => ['tags' => ['inheritDoc']],
		'phpdoc_tag_type' => [
			'tags' => [
				'api' => 'annotation',
				'author' => 'annotation',
				'copyright' => 'annotation',
				'deprecated' => 'annotation',
				'example' => 'annotation',
				'global' => 'annotation',
				'inheritDoc' => 'inline',
				'internal' => 'annotation',
				'license' => 'annotation',
				'method' => 'annotation',
				'package' => 'annotation',
				// 'param' => 'annotation', // failed to process array{0: string}
				'property' => 'annotation',
				// 'return' => 'annotation', // failed to process array{0: string}
				'see' => 'annotation',
				'since' => 'annotation',
				'throws' => 'annotation',
				'todo' => 'annotation',
				'uses' => 'annotation',
				'var' => 'annotation',
				'version' => 'annotation',
			],
		],
		'phpdoc_to_comment' => false, // @psalm-zzz not recognized as annotations
		'phpdoc_trim_consecutive_blank_line_separation' => true,
		'phpdoc_trim' => true,
		'phpdoc_types' => ['groups' => ['simple', 'alias', 'meta']],
		'phpdoc_types_order' => [
			'sort_algorithm' => 'none',
			'null_adjustment' => 'always_first',
		],
		'phpdoc_var_annotation_correct_order' => true,
		'phpdoc_var_without_name' => true,
		
		
		// Return Notation Rules
		'no_useless_return' => true,
		'return_assignment' => true,
		'simplified_null_return' => false, // incapable to detect when it's a real void return
		
		
		// Semicolon Rules
		'multiline_whitespace_before_semicolons' => [
			'strategy' => 'new_line_for_chained_calls',
		],
		'no_empty_statement' => true,
		'no_singleline_whitespace_before_semicolons' => true,
		'semicolon_after_instruction' => true,
		'space_after_semicolon' => [
			'remove_in_empty_for_expressions' => false,
		],
		
		
		// Strict Rules
		'declare_strict_types' => true, // risky
		'strict_comparison' => true, // risky
		'strict_param' => true, // risky
		
		
		// String Notation Rules
		'string_implicit_backslashes' => [
			'double_quoted' => 'escape',
			'heredoc' => 'escape',
			'single_quoted' => 'escape',
		],
		'explicit_string_variable' => true,
		'heredoc_to_nowdoc' => false,
		'no_binary_string' => true,
		'no_trailing_whitespace_in_string' => false,
		'simple_to_complex_string_variable' => true,
		'single_quote' => [
			'strings_containing_single_quote_chars' => false,
		],
		'string_length_to_empty' => true,
		'string_line_ending' => false, // risky
		
		
		// Whitespace Rules
		'array_indentation' => true,
		'blank_line_before_statement' => [
			'statements' => [
				'case', 'do', 'for', 'foreach', 'return',
				'switch', 'throw', 'try', 'while',
				'yield', 'yield_from',
			],
		],
		'blank_line_between_import_groups' => true,
		'compact_nullable_type_declaration' => true,
		'heredoc_indentation' => false,
		'indentation_type' => true,
		'line_ending' => true,
		'method_chaining_indentation' => true,
		'no_extra_blank_lines' => ['tokens' => ['use']],
		'no_spaces_around_offset' => ['positions' => ['inside', 'outside']],
		'no_trailing_whitespace' => false,
		'no_whitespace_in_blank_line' => false,
		'single_blank_line_at_eof' => true,
		'spaces_inside_parentheses' => ['space' => 'none'],
		// 'statement_indentation' => true, // does not work with final blank space indentation before end of class
		'type_declaration_spaces' => ['elements' => ['function', 'property']],
		'types_spaces' => [
			'space' => 'none',
			'space_multiple_catch' => 'none',
		],
	])
	->setIndent("\t")
	->setLineEnding("\n")
	->setFinder($finder)
;

return $config;
