# PHPMongo
Some files demonstrating model creation from a Mongo Database

Working on a project that would benefit from using a document database, rather than the traditional SQL style the absence of a PHP equivalent for Mongoose became apparent, so I had to roll my own, it is based on the central Model class which other models extend as required.  Lots to do, and some potential discussions over whether find should be or should not be static and where using string or class forms of objectID should be default etc. etc. but works well.

So to get a document from the database:

$document = Document::find(["_id"=>"{_id string}"]) // String method

$document = Document::find(["_id"=>$object_id], false) // Object method

$document = Document::find([], false/true, true) // returns an array of (all as it happens) objects


Also included is some javascript which is used to create a dynamic SVG orgnisation chart using the D3 library.  Albeit this is a rather pale imitation of the totally dynamic dashboard I created for Massive Analytic.

Gary Brooks
19/06/2018
