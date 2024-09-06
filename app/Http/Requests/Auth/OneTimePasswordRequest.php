<?php

declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;

abstract class OneTimePasswordRequest extends FormRequest
{
    /**
     * The user attempting the two factor challenge.
     */
    protected ?User $challengedUser;

    /**
     * Determine if the user is authoirzed to make this request.
     */
    abstract public function authorize(): bool;

    /**
     * Determine if there is a challenged user
     */
    abstract public function hasChallengedUser(): bool;

    /**
     * Get the user that is attempting the two factor challenge.
     */
    abstract public function challengedUser(): User;

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'code' => 'required|string|max:6',
        ];
    }

    /**
     * Determine if the request has a valid one time password.
     */
    public function hasValidCode(): bool
    {
        return $this->challengedUser()->verifyOneTimePassword($this->code);
    }
}
