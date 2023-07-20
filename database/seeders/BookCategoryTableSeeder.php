<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Book;

class BookCategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = Category::all();
        $books = Book::all();
        $minBooksPerCategory = 10;
        foreach ($categories as $category) {
            $categoryBooks = $books->random($minBooksPerCategory);
            foreach ($categoryBooks as $book) {
                $category->books()->attach($book);
            }
        }
    }
}
