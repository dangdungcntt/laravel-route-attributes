<?php

namespace Spatie\RouteAttributes\Tests\AttributeTests;

use Spatie\RouteAttributes\Tests\TestCase;
use Spatie\RouteAttributes\Tests\TestClasses\Controllers\MixResourceTestController;
use Spatie\RouteAttributes\Tests\TestClasses\Controllers\ResourceTestController;

class ResourceAttributeTest extends TestCase
{
    /** @test */
    public function it_can_apply_resource_for_class()
    {
        $this->routeRegistrar->registerClass(ResourceTestController::class);

        $this
            ->assertRegisteredRoutesCount(7)
            ->assertRouteRegistered(
                ResourceTestController::class,
                controllerMethod: 'index',
                uri: 'posts',
                name: 'posts.index',
            )
            ->assertRouteRegistered(
                ResourceTestController::class,
                controllerMethod: 'create',
                uri: 'posts/create',
                name: 'posts.create',
            )
            ->assertRouteRegistered(
                ResourceTestController::class,
                controllerMethod: 'store',
                httpMethod: 'post',
                uri: 'posts',
                name: 'posts.store',
            )
            ->assertRouteRegistered(
                ResourceTestController::class,
                controllerMethod: 'show',
                uri: 'posts/{post}',
                name: 'posts.show',
            )
            ->assertRouteRegistered(
                ResourceTestController::class,
                controllerMethod: 'edit',
                uri: 'posts/{post}/edit',
                name: 'posts.edit',
            )
            ->assertRouteRegistered(
                ResourceTestController::class,
                controllerMethod: 'update',
                httpMethod: 'put',
                uri: 'posts/{post}',
                name: 'posts.update',
            )
            ->assertRouteRegistered(
                ResourceTestController::class,
                controllerMethod: 'destroy',
                httpMethod: 'delete',
                uri: 'posts/{post}',
                name: 'posts.destroy',
            );
    }

    /** @test */
    public function it_can_apply_resource_together_with_other_attributes_for_class()
    {
        $this->routeRegistrar->registerClass(MixResourceTestController::class);

        $this
            ->assertRegisteredRoutesCount(8)
            ->assertRouteRegistered(
                MixResourceTestController::class,
                controllerMethod: 'index',
                uri: 'admin/posts',
                middleware: ['auth'],
                name: 'admin.posts.index'
            )
            ->assertRouteRegistered(
                MixResourceTestController::class,
                controllerMethod: 'create',
                uri: 'admin/posts/create-new-post',
                middleware: ['auth', 'editor'],
                name: 'admin.posts.create-new-post',
            )
            ->assertRouteRegistered(
                MixResourceTestController::class,
                controllerMethod: 'store',
                httpMethod: 'post',
                uri: 'admin/posts',
                middleware: ['auth'],
                name: 'admin.posts.store',
            )
            ->assertRouteRegistered(
                MixResourceTestController::class,
                controllerMethod: 'show',
                uri: 'admin/posts/{post}',
                middleware: ['auth'],
                name: 'admin.posts.show',
            )
            ->assertRouteRegistered(
                MixResourceTestController::class,
                controllerMethod: 'edit',
                uri: 'admin/posts/{post}/edit',
                middleware: ['auth'],
                name: 'admin.posts.edit',
            )
            ->assertRouteRegistered(
                MixResourceTestController::class,
                controllerMethod: 'update',
                httpMethod: 'put',
                uri: 'admin/posts/{post}',
                middleware: ['auth'],
                name: 'admin.posts.update',
            )
            ->assertRouteRegistered(
                MixResourceTestController::class,
                controllerMethod: 'destroy',
                httpMethod: 'delete',
                uri: 'admin/posts/{post}',
                middleware: ['auth'],
                name: 'admin.posts.destroy',
            )
            ->assertRouteRegistered(
                MixResourceTestController::class,
                controllerMethod: 'updateStatus',
                httpMethod: 'put',
                uri: 'admin/posts/{post}/update-status',
                middleware: ['auth', 'admin'],
                name: 'admin.posts.update-status',
            );
    }
}
