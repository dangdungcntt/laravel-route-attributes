<?php

namespace Spatie\RouteAttributes\Tests\TestClasses\Controllers;

use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Middleware;
use Spatie\RouteAttributes\Attributes\Name;
use Spatie\RouteAttributes\Attributes\Prefix;
use Spatie\RouteAttributes\Attributes\Put;
use Spatie\RouteAttributes\Attributes\Resource;

#[Resource('posts'), Name('admin.'), Prefix('admin'), Middleware('auth')]
class MixResourceTestController
{
    public function index()
    {
    }

    #[Get('posts/create-new-post', name: 'posts.create-new-post', middleware: ['editor'])]
    public function create()
    {
    }

    public function store()
    {
    }

    public function show()
    {
    }

    public function edit()
    {
    }

    public function update()
    {
    }

    public function destroy()
    {
    }

    #[Put('posts/{post}/update-status', name: 'posts.update-status', middleware: 'admin')]
    public function updateStatus()
    {
    }
}
