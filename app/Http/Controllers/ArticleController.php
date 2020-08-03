<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Article;
use App\Http\Resources\Article as ArticleResource;
use App\Http\Resources\ArticleCollection ;
use Illuminate\Support\Facades\Storage;

class ArticleController extends Controller
{

    private static $messages = [
        'required' => 'El campo :attribute es obligatorio.',
        'body.required' => 'El body no es válido.',
    ];

    public function index()
    {
        $this->authorize('viewAny', Article::class);
        return new ArticleCollection(Article::paginate(25));
    }

    public function show(Article $article)
    {
        $this->authorize('view', $article);
        return response()->json(new ArticleResource($article), 200);
    }

    public function image(Article $article) {
        return response()->download(public_path(Storage::url($article->image)), $article->title);
    }

    public function store(Request $request)
    {


        $request->validate([
            'title' => 'required|string|unique:articles|max:255',
            'body' => 'required',
            'category_id' => 'required|exists:categories,id',
            'image' => 'required|image|dimensions:min_width=200,min_height=200',
        ], self:: $messages);

        $article = new Article($request->all());
        $path = $request->image->store('public/articles');

        $article->image = $path;
        $article->save();

        return Article::create($request->all());
        return response()->json($article,201);
    }

    public function update(Request $request, Article $article)
    {
        $article->update($request->all());
        return response()->json($article,200) ;
    }

    public function delete(Article $article)
    {
        $article->delete();

        return response()->json(null,204);
    }
}
