<?php

class SitemapController extends Controller{


	public function __call($name , $arguments ){

		$SitemapModel = new SitemapModel();
		$SitemapModel::renderSitemap();

	}


}
