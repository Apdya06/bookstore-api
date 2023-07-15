<?php

$directories = ['/xampp/htdocs/bookstore-api/public/images/categories', '/xampp/htdocs/bookstore-api/public/images/books', '/xampp/htdocs/bookstore-api/public/images/users'];

foreach ($directories as $directory) {
    $files = glob($directory . '/*'); // get all file names
    foreach($files as $file){ // iterate files
        if(is_file($file))
            unlink($file); // delete file
    }
}
