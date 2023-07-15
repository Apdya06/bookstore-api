<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Book;
use App\Http\Resources\Book as BookResourceCollection;
use Illuminate\Support\Facades\Auth;

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
        // $book = DB::select('select * from books where id = :id', ['id' => $id]);
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
    // public function example()
    // {
    //     $user = Auth::user();
    //     $id = Auth::id();
    //     if (Auth::check()) {
    //         // The user is logged in
    //     }
    // }
}
