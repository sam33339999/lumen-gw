<?php

namespace App\Exceptions;

use JetBrains\PhpStorm\Internal\LanguageLevelTypeAware;

class JsonParseException extends \Exception
{
    public function __construct(string $content = "", int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct("JSON parse error: $content", $code, $previous);
    }
}
