<?php

namespace App\Rules;

use App\Helpers\YoutubeUrl;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class YoutubeUrlRule implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (!YoutubeUrl::toEmbedUrl((string) $value)) {
            $fail(__('app.video url must be a valid youtube link'));
        }
    }
}
