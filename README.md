# Note Online - Store and share notes
Note Online is a web application designed to simplify the process of taking, storing, and sharing notes.


### Link
Check out the live website **[Note Online](https://note.imranpollob.com)**


## Features
- Save a note (autosaved)
- Every note has a unique link
- Possible to add password protection for a particular note
- Supports rich text format
- If logged in, you can see all of your created notes
- Logged-in users can delete their notes
- Easily copy the sharing link


![Note Online](/screenshot.png)



## Installation
```bash
npm install
composer install
# modify .env file
php artisan key:generate
# create a database as defined in .env
php artisan migrate
php artisan serve
```