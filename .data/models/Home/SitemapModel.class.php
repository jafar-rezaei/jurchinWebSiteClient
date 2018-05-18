<?php

class SitemapModel extends Model {

	protected static $db;
	public function __construct() {

		self::$db = parent::dataBase();

	}

	public static function renderSitemap() {


    // get cats list
    $CategoriesModel = new CategoriesModel();
		$cats = $CategoriesModel->getCatList(0 , false , array("slug") );



    // get tags list
    $postModal = new PostModel();
		$tags = $postModal->getTagsList("" , array("tag"));


    // get post list
		$posts = $postModal->getPostList("", "", array("slug") );


    $SitemapXML = MAINROOT . "sitemap.xml";
    chmod($SitemapXML , 0755);
    $fileHandle = fopen($SitemapXML , 'w+') or die("No permistion to file !");
    fwrite($fileHandle, "");


		$start = "<?xml version='1.0' encoding='UTF-8'?>
<urlset xmlns=\"http://www.sitemaps.org/schemas/sitemap/0.9\">";
		$stringData = $start;
		fwrite($fileHandle, $stringData);



		 // posts
		foreach($posts as $post){
			$slug = $post['slug'];
			$link = "
			<url><loc>" . SITEURLWWW . "Post" . DS . $slug . "</loc></url>";
			$stringData = $link;
			fwrite($fileHandle, $stringData);
		}

		// tags
		foreach($tags as $tag){
			$slug = $tag['tag'];
			$link = "
			<url><loc>" . SITEURLWWW . "Tag" . DS . $slug . "</loc></url>";
			$stringData = $link;
			fwrite($fileHandle, $stringData);
		}

		// cats
		foreach($cats as $cat){
			$slug = $cat['slug'];
			$link = "
			<url><loc>" . SITEURLWWW ."Category" . DS . $slug . "</loc></url>";
			$stringData = $link;
			fwrite($fileHandle, $stringData);
		}

		$end = "
</urlset>";
		fwrite($fileHandle, $end);
		fclose($fileHandle);
		echo "Sitemap genereted successfuly!";
	}


}
