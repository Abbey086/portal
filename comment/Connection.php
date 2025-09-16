<?php
session_start();
if(!is_dir(__DIR__.'/db'))
mkdir(__DIR__.'/db');
if(!defined('db_file')) define('db_file',__DIR__.'./db/voting_db.db');
function my_udf_md5($string) {
    return md5($string);
}
Class DBConnection extends SQLite3{
    protected $db;
    function __construct(){
        $this->open(db_file);
        $this->createFunction('md5', 'my_udf_md5');
        $this->exec("PRAGMA foreign_keys = ON;");

        $this->exec("CREATE TABLE IF NOT EXISTS `comments` (
            `comment_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
            `sender` TEXT NOT NULL,
            `comment` TEXT NOT NULL,
            `date_created` TIMESTAMP DEFAULT (datetime('now','localtime'))
        )"); 
       
       $this->exec("CREATE TABLE IF NOT EXISTS `replies` (
            `reply_id` INTEGER NOT NULL PRIMARY KEY AUTOINCREMENT,
            `comment_id` INTEGER NOT NULL,
            `sender` TEXT NOT NULL,
            `reply` TEXT NOT NULL,
            `date_created` TIMESTAMP DEFAULT (datetime('now','localtime')),
            FOREIGN KEY (`comment_id`) References `comments`(comment_id) ON DELETE CASCADE
        )");

    }
    function __destruct(){
         $this->close();
    }
}

$conn = new DBConnection();