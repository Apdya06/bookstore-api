<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Http\Resources\Books as BookResourceCollection;
use App\Http\Resources\Book as BookResource;

class BookController extends Controller
{
    public function print($title)
    {
        return $title;
    }

    public function index()
    {
        $books = Book::paginate(4);
        return new BookResourceCollection($books);
    }

    public function view($id)
    {
        $book = new BookResourceCollection(Book::find($id));
        return $book;
    }

    public function top($count)
    {
        $criteria = Book::select('*')
        ->orderBy('views', 'DESC')
        ->limit($count)
        ->get();
        return new BookResourceCollection($criteria);
    }

    public function slug($slug)
    {
        $criteria = Book::where('slug', $slug)->first();
        return new BookResource($criteria);
    }

    public function search($keyword)
    {
        $criteria = Book::select('*')
            ->where('title', 'LIKE', "%".$keyword."%")
            ->orderBy('views', 'DESC')
            ->get();
        return new BookResourceCollection($criteria);
    }
}
