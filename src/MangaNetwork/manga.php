<?php

class MnManga {

	public $id;
	public $title;
	public $chapter_nb;
	public $source_API;
	public $source_URL;
	public $source_ID;
	public $update_date;
	public $release_date;
	public $completed;
	public $description;
	public $cover;
	public $authors;
	public $genres;

	/**
	 * Manga network manga constructor
	 */
	function __construct($id, $title, $chapter_nb, $source_API, $source_URL, $source_ID, $update_date,
		                 $release_date, $completed, $description, $cover, $authors, $genres) {
		$this->id = $id;
		$this->title = $title;
		$this->chapter_nb = $chapter_nb;
		$this->source_API = $source_API;
		$this->source_URL = $source_URL;
		$this->source_ID = $source_ID;
		$this->update_date = $update_date;
		$this->release_date = $release_date;
		$this->completed = $completed;
        $this->description = $description;
        $this->cover = $cover;
        $this->authors = $authors;
        $this->genres = $genres;
	}

	/**
	 * Init a new manga from an associative array
	 * @param  mixed[] Associative array used
	 * @return MnManga The newly created manga
	 */
	static function initFrom($data) {
		return new MnManga($data['id'], $data['title'], $data['chapter_nb'], $data['source_API'],
			               $data['source_URL'], $data['source_ID'], $data['update_date'],
			               $data['release_date'], $data['completed'], $data['description'], 
			               $data['cover'], $data['authors'], $data['genres']);
	}
}

/**
 * Manga page
 */
class MnMangaPage {

	public $page_nb;
	public $link;

	function __construct($page_nb, $link) {
		$this->page_nb = $page_nb;
		$this->link = $link;
	}

	/**
	 * Init a new manga page from an associative array
	 * @param  mixed[] Associative array used
	 * @return MnMangaPage The newly created manga page
	 */
	static function initFrom($data) {
		return new MnMangaPage($data['page_nb'], $data['link']);
	}
} 

/**
 * Manga chapter
 */
class MnMangaChapter {

	public $id;
	public $title;
	public $page_start;
	public $page_nb;

	function __construct($id, $title, $page_start, $page_nb) {
		$this->id = $id;
		$this->title = $title;
		$this->page_start = $page_start;
		$this->page_nb = $page_nb;
	}

	/**
	 * Init a new manga chapter from an associative array
	 * @param  mixed[] Associative array used
	 * @return MnMangaPage The newly created manga chapter
	 */
	static function initFrom($data) {
		return new MnMangaChapter($data['id'], $data['title'], $data['page_start'], $data['page_nb']);
	}
}

?>