<?php

namespace Spatie\RouteAttributes;

use ReflectionClass;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Name;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Resource;
use Spatie\RouteAttributes\Attributes\RouteAttribute;

class ClassRouteAttributes
{
    public function __construct(public ReflectionClass $class)
    {
    }


    public function prefix(): ?string
    {
        /** @var \Spatie\RouteAttributes\Attributes\Prefix $attribute */
        if (!$attribute = $this->getAttribute(Prefix::class)) {
            return null;
        }

        return $attribute->prefix;
    }

    public function middleware(): array
    {
        /** @var \Spatie\RouteAttributes\Attributes\Middleware $attribute */
        if (!$attribute = $this->getAttribute(Middleware::class)) {
            return [];
        }

        return $attribute->middleware;
    }

    public function name(): ?string
    {
        /** @var \Spatie\RouteAttributes\Attributes\Name $attribute */
        if (!$attribute = $this->getAttribute(Name::class)) {
            return null;
        }

        return $attribute->name;
    }

    public function resource(): ?string
    {
        /** @var \Spatie\RouteAttributes\Attributes\Resource $attribute */
        if (!$attribute = $this->getAttribute(Resource::class)) {
            return null;
        }

        return $attribute->name;
    }

    protected function getAttribute(string $attributeClass): ?RouteAttribute
    {
        $attributes = $this->class->getAttributes($attributeClass);

        if (!count($attributes)) {
            return null;
        }

        return $attributes[0]->newInstance();
    }
}
