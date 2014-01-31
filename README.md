FB Page scraper
===============

Use Graph API to download all posts (and download images) for selected pages.

I wrote this to to fulfill a one time request by a page owner. I garantie nothing.

How To Setup
----------

1. Clone the repo
2. Run composer install `php composer.phar install`
3. Create the config.php file `cp config.php.dist config.php`
4. Edit the config.php file
5. Create folder 'output' `mkdir output`
6. Set permissions on 'output' so that the user runing the script can write in the folder

How to run
----------

`php cli.php`

Your content can be found in the output/ folder. Each page in its own subdirecory. Data is compiled into a JSON. Images are linked to the JSON data via post ID.


Notes
-----

Note that Facebook is known for many things, API stability is not one of them.

There is a sleep(1) in cli.php getData() function to slow down image collection. It can probably be removed.
