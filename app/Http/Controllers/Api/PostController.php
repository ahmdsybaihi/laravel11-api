<?php

namespace App\Http\Controllers\Api;

use App\Models\Post;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    /**
     * index
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // Mendapatkan semua postingan dan paginasi
        $posts = Post::latest()->paginate(5);

        // Mengembalikan koleksi postingan sebagai resource
        return new PostResource(true, 'List Data Posts', $posts);
    }

    /**
     * store
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Definisikan aturan validasi
        $validator = Validator::make($request->all(), [
            'image'     => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'title'     => 'required|string|max:255',
            'content'   => 'required|string',
        ]);

        // Periksa apakah validasi gagal
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi Error',
                'data'    => $validator->errors(),
            ], 422);
        }

        // Upload gambar
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imagePath = $image->store('public/posts');
            $imageName = basename($imagePath);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Gambar tidak ditemukan',
            ], 400);
        }

        // Membuat postingan
        $post = Post::create([
            'image'     => $imageName,
            'title'     => $request->title,
            'content'   => $request->content,
        ]);

        // Mengembalikan response
        return new PostResource(true, 'Data Post Berhasil Ditambahkan!', $post);
    }
}
