<?php

namespace Spatie\RouteAttributes;

use Illuminate\Routing\Router;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use ReflectionAttribute;
use ReflectionClass;
use Spatie\RouteAttributes\Attributes\Route;
use Spatie\RouteAttributes\Attributes\RouteAttribute;
use SplFileInfo;
use Symfony\Component\Finder\Finder;
use Throwable;

class RouteRegistrar
{
    private Router $router;

    protected string $basePath;

    private string $rootNamespace;

    static array $resourceRouteByMethods = [
        'index'   => ['get', '{name}', '{name}.index'],
        'create'  => ['get', '{name}/create', '{name}.create'],
        'store'   => ['post', '{name}', '{name}.store'],
        'show'    => ['get', '{name}/{{route_key}}', '{name}.show'],
        'edit'    => ['get', '{name}/{{route_key}}/edit', '{name}.edit'],
        'update'  => ['put', '{name}/{{route_key}}', '{name}.update'],
        'destroy' => ['delete', '{name}/{{route_key}}', '{name}.destroy'],
    ];

    public function __construct(Router $router)
    {
        $this->router = $router;

        $this->basePath = app()->path();
    }

    public function useBasePath(string $basePath): self
    {
        $this->basePath = $basePath;

        return $this;
    }

    public function useRootNamespace(string $rootNamespace): self
    {
        $this->rootNamespace = $rootNamespace;

        return $this;
    }

    public function registerDirectory(string|array $directories): void
    {
        $directories = Arr::wrap($directories);

        $files = (new Finder())->files()->name('*.php')->in($directories);

        collect($files)->each(fn(SplFileInfo $file) => $this->registerFile($file));
    }

    public function registerFile(string|SplFileInfo $path): void
    {
        if (is_string($path)) {
            $path = new SplFileInfo($path);
        }

        $fullyQualifiedClassName = $this->fullQualifiedClassNameFromFile($path);

        $this->processAttributes($fullyQualifiedClassName);
    }

    public function registerClass(string $class)
    {
        $this->processAttributes($class);
    }

    protected function fullQualifiedClassNameFromFile(SplFileInfo $file): string
    {
        $class = trim(Str::replaceFirst($this->basePath, '', $file->getRealPath()), DIRECTORY_SEPARATOR);

        $class = str_replace(
            [DIRECTORY_SEPARATOR, 'App\\'],
            ['\\', app()->getNamespace()],
            ucfirst(Str::replaceLast('.php', '', $class))
        );

        return $this->rootNamespace.$class;
    }

    protected function processAttributes(string $className): void
    {
        if (!class_exists($className)) {
            return;
        }

        $class = new ReflectionClass($className);

        $classRouteAttributes = new ClassRouteAttributes($class);

        $resourceName = $classRouteAttributes->resource();

        foreach ($class->getMethods() as $method) {
            $attributes = $method->getAttributes(RouteAttribute::class, ReflectionAttribute::IS_INSTANCEOF);

            $routeAttributes = collect($attributes)
                ->map(function (ReflectionAttribute $attribute) {
                    try {
                        $attributeClass = $attribute->newInstance();
                        return $attributeClass instanceof Route ? $attributeClass : false;
                    } catch (Throwable) {
                        return false;
                    }
                })
                ->reject(fn($attributeClass) => !$attributeClass);

            if ($routeAttributes->count()) {
                $routeAttributes->each(fn(Route $methodRouteAttribute) => $this->registerRoute($classRouteAttributes,
                    $methodRouteAttribute, $method->getName()));
            } elseif ($resourceName && array_key_exists($method->getName(), static::$resourceRouteByMethods)) {
                $params = collect(static::$resourceRouteByMethods[$method->getName()])
                    ->map(fn($param) => $this->replaceResourceInfo($param, $resourceName));

                $methodRouteAttribute = new Route(...$params);
                $this->registerRoute($classRouteAttributes, $methodRouteAttribute, $method->getName());
            }
        }
    }

    protected function registerRoute(
        ClassRouteAttributes $classRouteAttributes,
        Route $methodRouteAttribute,
        string $methodName
    ) {
        $httpMethod = $methodRouteAttribute->method;

        $action = $methodName === '__invoke'
            ? $classRouteAttributes->class->getName()
            : [$classRouteAttributes->class->getName(), $methodName];

        /** @var \Illuminate\Routing\Route $route */
        $route = $this->router->$httpMethod($methodRouteAttribute->uri, $action);

        if (is_null($namePrefix = $classRouteAttributes->name())) {
            $route->name($methodRouteAttribute->name);
        } else {
            $route
                ->name($namePrefix.$methodRouteAttribute->name);
        }

        if ($uriPrefix = $classRouteAttributes->prefix()) {
            $route->prefix($uriPrefix);
        }

        $methodMiddleware = $methodRouteAttribute->middleware;

        $route->middleware([...$classRouteAttributes->middleware(), ...$methodMiddleware]);
    }

    protected function replaceResourceInfo(string $param, string $resourceName): string
    {
        return strtr($param, [
            '{name}'      => $resourceName,
            '{route_key}' => Str::singular($resourceName)
        ]);
    }
}
