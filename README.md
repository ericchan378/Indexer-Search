# Indexer-Search
Search engine and indexer
There are three tables in the MySQL database: page, page_word, and word tables.

The 'page' table will have the following columns:
  pageId  int (auto-increment)
  url  varchar
  title  varchar
  description  varchar

The 'page_word' table will have the following columns:
  pageWordId  int (auto-increment)
  pageId  int
  wordId  int
  freq  int

The 'word' table will have the following columns:
  wordId  int (auto-increment)
  wordName  varchar

  crawl.php will be run first to populate the database.
    The variable $startUrl can be changed to populate the database with a different section of the internet.

  start.php is presented to the user to provide the simple textbox for the search terms and the search button to start the search after the database is populated.

  Testing is done using XAMPP.
  Files are placed in C:\xampp\htdocs\...
  Files SiteResultsProvider.php and DomDocumentParser.php are placed in a classes folder in htdocs
