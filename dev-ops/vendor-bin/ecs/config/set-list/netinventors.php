<?php

use PhpCsFixer\Fixer\Basic\NoTrailingCommaInSinglelineFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedTypesFixer;
use PhpCsFixer\Fixer\ControlStructure\TrailingCommaInMultilineFixer;
use PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer;
use PhpCsFixer\Fixer\FunctionNotation\MethodArgumentSpaceFixer;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\Operator\BinaryOperatorSpacesFixer;
use PhpCsFixer\Fixer\Operator\NotOperatorWithSuccessorSpaceFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocParamOrderFixer;
use PhpCsFixer\Fixer\Whitespace\TypeDeclarationSpacesFixer;
use PhpCsFixerCustomFixers\Fixer\CommentSurroundedBySpacesFixer;
use PhpCsFixerCustomFixers\Fixer\MultilineCommentOpeningClosingAloneFixer;
use PhpCsFixerCustomFixers\Fixer\MultilinePromotedPropertiesFixer;
use PhpCsFixerCustomFixers\Fixer\NoDoctrineMigrationsGeneratedCommentFixer;
use PhpCsFixerCustomFixers\Fixer\NoImportFromGlobalNamespaceFixer;
use PhpCsFixerCustomFixers\Fixer\NoLeadingSlashInGlobalNamespaceFixer;
use PhpCsFixerCustomFixers\Fixer\NoSuperfluousConcatenationFixer;
use PhpCsFixerCustomFixers\Fixer\NoUselessDirnameCallFixer;
use PhpCsFixerCustomFixers\Fixer\NoUselessDoctrineRepositoryCommentFixer;
use PhpCsFixerCustomFixers\Fixer\NoUselessParenthesisFixer;
use PhpCsFixerCustomFixers\Fixer\NoUselessStrlenFixer;
use PhpCsFixerCustomFixers\Fixer\PhpdocParamTypeFixer;
use PhpCsFixerCustomFixers\Fixer\PhpdocSelfAccessorFixer;
// use PhpCsFixerCustomFixers\Fixer\PhpdocSingleLineVarFixer;
use PhpCsFixerCustomFixers\Fixer\PhpdocTypeListFixer;
use PhpCsFixerCustomFixers\Fixer\PhpdocTypesCommaSpacesFixer;
use PhpCsFixerCustomFixers\Fixer\PhpdocTypesTrimFixer;
use PhpCsFixerCustomFixers\Fixer\StringableInterfaceFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return static function (ECSConfig $config): void {
    $config->sets([
        SetList::PSR_12,
        SetList::NAMESPACES,
        SetList::SPACES,
        SetList::STRICT,
    ]);

    $config->ruleWithConfiguration(
        OrderedImportsFixer::class,
        [
            'imports_order' => [
                OrderedImportsFixer::IMPORT_TYPE_CONST,
                OrderedImportsFixer::IMPORT_TYPE_CLASS,
                OrderedImportsFixer::IMPORT_TYPE_FUNCTION,
            ],
        ],
    );

    $config->rule(OrderedClassElementsFixer::class);
    $config->rule(YodaStyleFixer::class);

    $config->rule(TypeDeclarationSpacesFixer::class);

    $config->ruleWithConfiguration(
        BinaryOperatorSpacesFixer::class,
        [
            'operators' => [
                '='  => BinaryOperatorSpacesFixer::ALIGN_SINGLE_SPACE_MINIMAL,
                '=>' => BinaryOperatorSpacesFixer::ALIGN_SINGLE_SPACE_MINIMAL_BY_SCOPE,
            ],
        ],
    );

    $config->ruleWithConfiguration(
        MethodArgumentSpaceFixer::class,
        [
            'on_multiline'        => 'ignore',
            'attribute_placement' => 'standalone',
        ],
    );

    $config->rule(NoTrailingCommaInSinglelineFixer::class);

    $config->ruleWithConfiguration(
        TrailingCommaInMultilineFixer::class,
        [
            'elements' => [
                TrailingCommaInMultilineFixer::ELEMENTS_ARGUMENTS,
                TrailingCommaInMultilineFixer::ELEMENTS_ARRAYS,
                TrailingCommaInMultilineFixer::ELEMENTS_PARAMETERS,
            ],
        ],
    );

    $config->rule(CommentSurroundedBySpacesFixer::class);
    $config->rule(MultilineCommentOpeningClosingAloneFixer::class);

    $config->ruleWithConfiguration(
        MultilinePromotedPropertiesFixer::class,
        [
            'keep_blank_lines' => true,
        ],
    );

    $config->ruleWithConfiguration(
        OrderedTypesFixer::class,
        [
            'null_adjustment' => 'always_last',
            'sort_algorithm'  => 'none',
        ]
    );

    $config->rule(NoUselessDoctrineRepositoryCommentFixer::class);
    $config->rule(NoDoctrineMigrationsGeneratedCommentFixer::class);

    $config->rule(NoImportFromGlobalNamespaceFixer::class);
    $config->rule(NoLeadingSlashInGlobalNamespaceFixer::class);

    $config->rule(NoSuperfluousConcatenationFixer::class);
    $config->rule(NoUselessDirnameCallFixer::class);
    $config->rule(NoUselessParenthesisFixer::class);
    $config->rule(NoUselessStrlenFixer::class);

    $config->rule(PhpdocParamOrderFixer::class);
    $config->rule(PhpdocParamTypeFixer::class);
    $config->rule(PhpdocSelfAccessorFixer::class);
    // $config->rule(PhpdocSingleLineVarFixer::class);
    $config->rule(PhpdocTypeListFixer::class);
    $config->rule(PhpdocTypesCommaSpacesFixer::class);
    $config->rule(PhpdocTypesTrimFixer::class);

    $config->rule(StringableInterfaceFixer::class);

    $config->skip([
        NotOperatorWithSuccessorSpaceFixer::class,
    ]);
};
