<?php

declare(strict_types=1);

namespace Finder\Builders;

use Elastic\ScoutDriverPlus\QueryParameters\ParameterCollection;
use Elastic\ScoutDriverPlus\QueryParameters\Shared\BoostParameter;
use Elastic\ScoutDriverPlus\QueryParameters\Shared\FieldParameter;
use Elastic\ScoutDriverPlus\QueryParameters\Shared\LenientParameter;
use Elastic\ScoutDriverPlus\QueryParameters\Shared\AnalyzerParameter;
use Elastic\ScoutDriverPlus\QueryParameters\Shared\OperatorParameter;
use Elastic\ScoutDriverPlus\QueryParameters\Shared\FuzzinessParameter;
use Elastic\ScoutDriverPlus\QueryParameters\Validators\AllOfValidator;
use Elastic\ScoutDriverPlus\Builders\AbstractParameterizedQueryBuilder;
use Elastic\ScoutDriverPlus\QueryParameters\Shared\QueryStringParameter;
use Elastic\ScoutDriverPlus\QueryParameters\Shared\FuzzyRewriteParameter;
use Elastic\ScoutDriverPlus\QueryParameters\Shared\PrefixLengthParameter;
use Elastic\ScoutDriverPlus\QueryParameters\Shared\MaxExpansionsParameter;
use Elastic\ScoutDriverPlus\QueryParameters\Shared\ZeroTermsQueryParameter;
use Elastic\ScoutDriverPlus\QueryParameters\Shared\MinimumShouldMatchParameter;
use Elastic\ScoutDriverPlus\QueryParameters\Shared\FuzzyTranspositionsParameter;
use Elastic\ScoutDriverPlus\QueryParameters\Transformers\GroupedArrayTransformer;
use Elastic\ScoutDriverPlus\QueryParameters\Shared\AutoGenerateSynonymsPhraseQueryParameter;

class DisMaxMatchPrefixQueryBuilder extends AbstractParameterizedQueryBuilder
{
    use AnalyzerParameter;
    use AutoGenerateSynonymsPhraseQueryParameter;
    use BoostParameter;
    use FieldParameter;
    use FuzzinessParameter;
    use FuzzyRewriteParameter;
    use FuzzyTranspositionsParameter;
    use LenientParameter;
    use MaxExpansionsParameter;
    use MinimumShouldMatchParameter;
    use OperatorParameter;
    use PrefixLengthParameter;
    use QueryStringParameter;
    use ZeroTermsQueryParameter;

    public function __construct()
    {
        $this->parameters = new ParameterCollection;
        $this->parameterValidator = new AllOfValidator(['field', 'query']);
        $this->parameterTransformer = new GroupedArrayTransformer('field');
    }

    public function buildQuery(): array
    {
        $this->parameterValidator->validate($this->parameters);

        $parameters = $this->parameterTransformer->transform($this->parameters);

        return [
            'dis_max' => [
                'queries' => [
                    ['match_bool_prefix' => $parameters],
                    ['match' => $parameters],
                ],
            ],
        ];
    }
}
