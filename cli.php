<?php
require 'vendor/autoload.php';
require_once("config.php");
  
$facebook = new Facebook($FBconfig);


foreach ($appConfig['pages'] as $pageId) {
	$dl = new Downloader($pageId, $facebook, $appConfig['fields']);
	$dl->start();
}


class Downloader {

	public $metaData = array();
	public $rootFilePath;
	public $imageFilePath;
	public $path;
	public $facebook;

	function __construct($pageId, $facebook, $fields) {
		$this->path          = sprintf('/%s/posts/', $pageId);
		$this->fields        = $fields;
		$this->pageId        = $pageId;
		$this->facebook      = $facebook;
		$this->rootFilePath  = sprintf('output/%s', $pageId);
		$this->imageFilePath = sprintf('%s/images', $this->rootFilePath);

		if (is_dir($this->rootFilePath) === false) {
			mkdir($this->rootFilePath);
		}

		if (is_dir($this->imageFilePath) === false) {
			mkdir($this->imageFilePath);
		} 
	}

	/**
	 * Main loop
	 */
	public function start() {
		$params = array("fields" => $this->fields);
		$method = "GET";
		$limit = 50;
		$offset = 0 - $limit;

		$this->log("### Starting main loop for " . $this->pageId);

		$ret = array();

		do {
		    $offset += $limit;
			
			$this->log("## Calling API for offset " . $offset);

		    $ret = $this->facebook->api(sprintf("%s?limit=$limit&offset=$offset", $this->path), $method, $params);
		    
		    if (isset($ret['paging']) && !empty($ret['paging']['next'])) {
		    	$this->log("## Not the last page");
		    }
		    // make sure we do not merge with an empty array
		    if (count($ret["data"]) > 0){
		    	$this->log("## Data found");
		       	
		       	$this->getData($ret["data"]);

		    } else {
		        // if the data entry is empty, we have reached the end, exit the while loop
		        $this->log("## NO MORE DATA");
		        break;
		    }
		} while(isset($ret['paging']) && !empty($ret['paging']['next']));

		$this->finish();
	}

	/**
	 * Parses set of posts by constructing the metadata array and downloading pictures
	 * @param  array $data 
	 * @return null
	 */
	public function getData($data) {

		foreach ($data as $key => $post) {
			$this->log(sprintf("# [%s] Processing post: %s", $key, $post['id']));

			$postData = $post;

			if (isset($post['picture'])) {
				$postData['picture'] =  str_replace('_s.jpg', '_n.jpg', $post['picture']);

				$this->log("# Retriving picture");
				$pic = file_get_contents($postData['picture']);
				$byteCountOrFalse = file_put_contents(sprintf('%s/%s.jpg', $this->imageFilePath, $post['id']), $pic);
				unset($pic);
				
				if ($byteCountOrFalse === false) {
					$this->log(sprintf("# -!-!-!- Failed to get picture with ID: %s and story: %s", $post['id'], $postData['story']));
				} else {
					$this->log("# Picture saved");
				}

				sleep(1);
			}
			
			$this->metaData[$post['id']] = $postData;
			unset($postData);
		}

	}

	/**
	 * Finishes by saving the metaData array
	 * @return null
	 */
	public function finish() {
		$this->log("####### FINISHING #######" . PHP_EOL);
		file_put_contents($this->rootFilePath . '/metaData.json', json_encode($this->metaData));
	}

	public function log($msg) {
		echo $msg . PHP_EOL;
	}

}

