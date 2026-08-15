<?php

namespace App\Parsers;

use App\Contexts\AbstractContext;
use App\Contexts\MethodCall;
use App\Contexts\ObjectValue;

class ArgumentExpressionListParser extends AbstractParser
{
    /**
     * @var MethodCall
     */
    protected AbstractContext $context;

    public function parse($node)
    {
        // Temporary fix. I don't know why foreach directive has autocompleting true as default
        $this->context->autocompleting = false;

        if ($this->context instanceof MethodCall || $this->context instanceof ObjectValue) {
            return $this->context->arguments;
        }

        return $this->context;
    }
}
