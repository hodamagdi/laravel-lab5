<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\post;
use App\Models\user;
use App\Events\PostCreated;


class PostController extends Controller
{
    public function index() 
    {
        $posts = Post::with('user')->paginate(5);
        return view('posts.index', compact('posts'));
    }

    public function show($postId)
    {
        $post = post::findOrFail($postId);
        return view('posts/show',[
            'post'=> $post
        ]);
    }

    public function create()
    {
        $users = User::all();
        return view('posts.create', compact('users'));
    }

    public function store(Request $request)
    {
        // Validate the request data
        $request->validate([
            'title' => 'required|string|max:255',
            'body' => 'required|string',
            'image' => 'nullable|image|max:2048', 
        ]);
        
        
      // Initialize fileName variable
    $fileName = null;

    // Store the image in the storage/app/public/posts directory
    if ($request->hasFile('image')) {
        $fileName = $request->file('image')->store('posts', 'public');
    }


        // Get the authenticated user
        $user = auth()->user();
    
        // Create a new Post instance
        $post = new Post();
        $post->title = $request->title;
        $post->slug = $request->slug;
        $post->body = $request->body;
        $post->enabled = $request->has('enabled');
        $post->image = $fileName; // Save the image path

        // Associate the user with the post
        $post->user()->associate($user);
    
        // Save the post to the database
        $post->save();
    
        // Dispatch the PostCreated event after saving the post
        event(new PostCreated($post));

    
        // Redirect the user to the posts index page
        return redirect(route('posts.index'))->with('success', 'Post created successfully.');
    }
    

    public function edit($postId)
    {
        $post = post::findOrFail($postId);
        return view('posts/edit',[
            'post'=> $post
        ]);
    }

    public function update($postId, Request $request)
    {
        // Retrieve the authenticated user
        $user = auth()->user();

        // Find the post by ID
        $post = Post::findOrFail($postId);

        // Check if the authenticated user is the owner of the post
        if ($user->id !== $post->user_id) {
            abort(403, 'Unauthorized action.'); 
        }

        // Update the post attributes
        $post->update([
            'title' => $request->title,
            'slug' => $request->slug,
            'body' => $request->body,
            'enabled' => $request->has('enabled') ? true : false,
        ]);

        // Redirect back to the posts index page
        return redirect(route('posts.index'));
    }

    public function delete ($postId)
    {
        post::findOrFail($postId)->delete();
        return redirect(url('/posts'));

    }
}
