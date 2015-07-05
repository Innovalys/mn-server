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
	private $chapters;
	public $user_info;

	/**
	 * Manga network manga constructor
	 */
	function __construct($id, $title, $chapter_nb, $source_API, $source_URL, $source_ID, $update_date,
		                 $release_date, $completed, $description, $cover, $authors, $genres, $chapters, $user_info) {
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
        $this->chapters = $chapters;
        $this->user_info = $user_info;

        if($this->user_info) {
        	$this->user_info['chapter_cur'] = 0 + $this->user_info['chapter_cur'];
        	$this->user_info['page_cur'] = 0 + $this->user_info['page_cur'];

        	if($this->user_info['note'] != NULL)
        		$this->user_info['note'] = 0 + $this->user_info['note'];
        }
	}

	/**
	 * Init a new manga from an associative array
	 * @param  mixed[] Associative array used
	 * @return MnManga The newly created manga
	 */
	static function initFrom($data) {
		if(!isset($data['user_info']))
			$data['user_info'] = NULL;

		return new MnManga($data['id'], $data['title'], $data['chapter_nb'], $data['source_API'],
			               $data['source_URL'], $data['source_ID'], $data['update_date'],
			               $data['release_date'], $data['completed'], $data['description'], 
			               $data['cover'], $data['authors'], $data['genres'], $data['chapters'], $data['user_info']);
	}

	function getChapters() {
		return $this->chapters;
	}

	function setChapterId($i, $id) {
		$this->chapters[$i]['id'] = $id;
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
		return new MnMangaPage($data['page_nb'] + 0, $data['link']);
	}
} 

/**
 * Manga chapter
 */
class MnMangaChapter {

	public $id;
	public $title;
	public $page_nb;
	public $source_ID;
	public $pages;
	private $loaded;

	function __construct($id, $title, $page_nb, $loaded, $source_ID) {
		$this->id = $id;
		$this->title = $title;
		$this->page_nb = $page_nb;
		$this->loaded = $loaded;
		$this->source_ID = $source_ID;
		$this->pages = [];
	}

	/**
	 * Init a new manga chapter from an associative array
	 * @param  mixed[] Associative array used
	 * @return MnMangaPage The newly created manga chapter
	 */
	static function initFrom($data) {
		return new MnMangaChapter($data['id'], $data['title'], $data['page_nb'] + 0, $data['loaded'], $data['source_ID']);
	}

	function isLoaded() {
		return $this->loaded;
	}
}

?>