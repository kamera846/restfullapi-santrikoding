<?php

namespace App\Http\Controllers\Api;

// Import model Post
use App\Models\Post;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

// Import Resource PostResource
use App\Http\Resources\PostResource;

// Import Facade Storage
use Illuminate\Support\Facades\Storage;

// Import Facade Validator
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    public function index() {
        $posts = Post::whereNull('deleted_at')->latest()->paginate(5);

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
        $post = Post::whereNull('deleted_at')->find($id);

        if (!$post) return new PostResource(true, 'Data tidak ditemukan!', null);

        // Return single post as a resource
        return new PostResource(true, 'Detail data!', $post);
    }

    public function update(Request $request, $id) {

        // Define validation rules
        $validator = Validator::make($request->all(), [
            'title' => 'required',
            'content' => 'required',
        ]);

        // Check if validation fails
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        // Find post by ID
        $post = Post::find($id);

        // Check if image is not empty
        if ($request->hasFile('image')) {
            // Reload image
            $image = $request->file('image');
            $image->storeAs('public/posts', $image->hashName());

            // Delete old image
            $post->update([
                'image' => $image->hashName(),
                'title' => $request->title,
                'content' => $request->content
            ]);
        } else {
            // Update post without image
            $post->update([
                'title' => $request->title,
                'content' => $request->content
            ]);
        }
        
        // Return response
        return new PostResource(true, 'Data berhasil diubah!', $post);
    }

    public function destroy(Request $request, $id) {

        // Find post by ID
        $post = Post::find($id);

        if (!$post) return new PostResource(true, 'Data tidak ditemukan!', null);


        // Soft Delete
        if ($request->method() === 'POST') {

            // Update post without image
            $post->update([
                'deleted_at' => date('Y-m-d H:i:s')
            ]);

        // Permanently Delete
        } else {

            // Delete image
            Storage::delete('public/posts/' . basename($post->image));

            // Delete post
            $post->delete();

        }

        // Return response
        return new PostResource(true, 'Data berhasil dihapus!', null);
    }

    public function restore($id) {

        // Find post by ID
        $post = Post::whereNotNull('deleted_at')->find($id);

        if (!$post) return new PostResource(true, 'Data tidak ditemukan!', null);

        // Update post without image
        $post->update([
            'deleted_at' => null
        ]);

        // Return response
        return new PostResource(true, 'Data berhasil dikembalikan!', null);
    }

}
