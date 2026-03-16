<?php

declare(strict_types=1);

namespace Fixture\PlainPhp;

final class ExampleClass
{
    private string $suffix = '!';

    public function greet(): string
    {
        $helper = new HelperService();

        return $helper->format('hello') . $this->suffix;
    }

    public static function make(): ExampleClass
    {
        return new ExampleClass();
    }
}
