<?php

declare(strict_types=1);

namespace App\GraphQL\Directives;

use Illuminate\Support\Arr;
use Illuminate\Container\Container;
use Nuwave\Lighthouse\Validation\RulesDirective as BaseRulesDirective;

class RulesDirective extends BaseRulesDirective
{
    public static function definition(): string
    {
        return /** @lang GraphQL */ <<<'GRAPHQL'
"""
Validate an argument using [Laravel validation](https://laravel.com/docs/validation).
"""
directive @rules(
  """
  Specify the validation rules to apply to the field.
  This can either be a reference to [Laravel's built-in validation rules](https://laravel.com/docs/validation#available-validation-rules),
  or the fully qualified class name of a custom validation rule.

  Rules that mutate the incoming arguments, such as `exclude_if`, are not supported
  by Lighthouse. Use ArgTransformerDirectives or FieldMiddlewareDirectives instead.
  """
  apply: [String!]!

  """
  Specify a custom attribute name to use in your validation message.
  """
  attribute: String

  """
  Specify the messages to return if the validators fail.
  """
  messages: [RulesMessage!]

  """
  Specify a prefix to use to fetch messages from the custom validation translations
  """
  customPrefix: String
) repeatable on ARGUMENT_DEFINITION | INPUT_FIELD_DEFINITION

"""
Input for the `messages` argument of `@rules`.
"""
input RulesMessage {
    """
    Name of the rule, e.g. `"email"`.
    """
    rule: String!

    """
    Message to display if the rule fails, e.g. `"Must be a valid email"`.
    """
    message: String!
}

GRAPHQL;
    }

    public function rules(): array
    {
        $rules = $this->directiveArgValue('apply');

        foreach ($rules as $key => $rule) {
            if (class_exists($rule)) {
                // The reason for this condition is as a result of the
                // following facts:
                // 1. Class instantiation is case insensitive in PHP (e.g. new Image is the same as new image)
                // 2. class_exists checks if a class has already been instantiated before checking the autoloaders
                // 3. The autoloaders are case sensitive, but if the class has already been instantiated,
                //    the autoloader is not checked.
                // So if the intervention Image class is loaded (which is aliases as Image), then the condition
                // class_exists('image') will return true!!!
                if ($rule !== 'image' && $rule !== 'file') {
                    $rules[$key] = Container::getInstance()->make($rule);
                }
            }
        }

        return $rules;
    }

    public function attribute(): ?string
    {
        $attribute = $this->directiveArgValue('attribute', $this->nodeName());
        $translationKey = 'validation.attributes.'.$attribute;
        $translation = trans($translationKey);

        return $translation === $translationKey ? $attribute : $translation;
    }

    public function messages(): array
    {
        $messages = parent::messages();

        if ($this->directiveHasArgument('customPrefix')) {
            /** @var array|null $customMessages */
            $customMessages = trans('validation.custom.'.$this->directiveArgValue('customPrefix').'.'.$this->nodeName());
            $customMessages = Arr::dot($customMessages ?: []);

            foreach ($customMessages as $key => $message) {
                $messages[$key] = $message;
            }
        }

        return $messages;
    }
}
