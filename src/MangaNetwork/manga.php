<?php

class MnManga {

public $id;
public $title;
public $page_nb;
public $page;
public $source_API;
public $source_URL;
public $source_ID;
public $update_date;
public $release_date;
public $completed;
public $description;

	/**
	 * Manga network manga constructor
	 */
		function __construct($id, $title, $page_nb, $page, $source_API, $source_URL, $source_ID, $update_date, $release_date, $completed, $description)
		{
			$this->id=$id;
			$this->title=$title;
			$this->page_nb=$page_nb;
			$this->page=$page_nb;
			$this->source_API=$source_API;
			$this->source_URL=$source_URL;
			$this->source_ID=$source_ID;
			$this->update_date=$update_date;
			$this->release_date=$release_date;
			$this->completed=$completed;
            $this->description=$description;
		}
} 









?>