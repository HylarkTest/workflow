<?php

declare(strict_types=1);

namespace LighthouseHelpers\Directives;

use Color\Color;
use Color\ColorFormat;
use GraphQL\Language\Parser;
use Nuwave\Lighthouse\Schema\AST\ASTHelper;
use Nuwave\Lighthouse\Execution\ResolveInfo;
use GraphQL\Language\AST\FieldDefinitionNode;
use Nuwave\Lighthouse\Schema\AST\DocumentAST;
use Nuwave\Lighthouse\Schema\Values\FieldValue;
use GraphQL\Language\AST\ObjectTypeDefinitionNode;
use GraphQL\Language\AST\InterfaceTypeDefinitionNode;
use Nuwave\Lighthouse\Schema\Directives\BaseDirective;
use Nuwave\Lighthouse\Support\Contracts\GraphQLContext;
use Nuwave\Lighthouse\Support\Contracts\FieldMiddleware;
use Nuwave\Lighthouse\Support\Contracts\FieldManipulator;

class ColorDirective extends BaseDirective implements FieldManipulator, FieldMiddleware
{
    public static function definition(): string
    {
        return /* @lang GraphQL */ <<<'SDL'
"""
Add the default arguments for a color field
"""
directive @color on FIELD_DEFINITION
SDL;
    }

    public function manipulateFieldDefinition(
        DocumentAST &$documentAST,
        FieldDefinitionNode &$fieldDefinition,
        ObjectTypeDefinitionNode|InterfaceTypeDefinitionNode &$parentType
    ): void {
        $fieldDefinition->arguments = ASTHelper::mergeUniqueNodeList(
            $fieldDefinition->arguments,
            [
                Parser::inputValueDefinition('format: ColorFormat = HEX'),
                Parser::inputValueDefinition('lighten: Float'),
                Parser::inputValueDefinition('saturate: Float'),
                Parser::inputValueDefinition('relativeHue: Int'),
                Parser::inputValueDefinition('lightness: Int'),
                Parser::inputValueDefinition('saturation: Int'),
                Parser::inputValueDefinition('hue: Int'),
            ],
        );
    }

    public function handleField(FieldValue $fieldValue): void
    {
        $fieldValue->wrapResolver(fn (callable $originalResolver) => function ($root, array $args, GraphQLContext $context, ResolveInfo $resolveInfo) use ($originalResolver) {
            $color = $originalResolver($root, $args, $context, $resolveInfo);

            if (! $color) {
                return null;
            }

            $format = ColorFormat::from($args['format']);

            $color = Color::make($color);

            if (isset($args['hue'])) {
                $color = $color->setHue($args['hue']);
            }
            if (isset($args['saturation'])) {
                $color = $color->setSaturation($args['saturation']);
            }
            if (isset($args['lightness'])) {
                $color = $color->setLightness($args['lightness']);
            }

            if (isset($args['relativeHue']) || isset($args['saturate']) || isset($args['lighten'])) {
                $color = $color->modify(
                    $args['saturate'] ?? 0,
                    $args['lighten'] ?? 0,
                    $args['relativeHue'] ?? 0,
                );
            }

            return $color->format($format);
        });
    }
}
