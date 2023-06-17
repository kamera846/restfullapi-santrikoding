<?php

namespace App\Http\Controllers\Api;

// Import model Post
use App\Models\Post;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// Import Resource PostResource
use App\Http\Resources\PostResource;

class PostController extends Controller
{
    public function index() {
        $posts = Post::latest()->paginate(5);

        return new PostResource(true, 'List Data Posts', $posts);
    }
}
