<?php

namespace App\Http\Controllers\Api;

// Import model Post
use App\Models\Post;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// Import Resource PostResource
use App\Http\Resources\PostResource;

// Import Facade Validator
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function index() {
        $posts = Post::latest()->paginate(5);

        return new PostResource(true, 'List Data Posts', $posts);
    }

    public function store(Request $request) {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title' => 'required',
            'content' => 'required'
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Upload image
        $image = $request->file('image');
        $imageName = $image->hashName();
        $uploadImage = $image->storeAs('public/posts', $imageName);

        if ($uploadImage)  {
            // Create post
            $post = Post::create([
                'image' => $imageName,
                'title' => $request->title,
                'content' => $request->content
            ]);
        }

        // Return response
        return new PostResource(true, 'Data berhasil ditambahkan!', $post);
    }

    public function show($id) {
        // Find post by ID
        $post = Post::find($id);

        if (!$post) return new PostResource(true, 'Data tidak ditemukan!', null);

        // Return single post as a resource
        return new PostResource(true, 'Detail data!', $post);
    }
}
