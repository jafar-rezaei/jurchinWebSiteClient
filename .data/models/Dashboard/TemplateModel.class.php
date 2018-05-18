<?php

class TemplateModel extends Model {

	protected static $dbh;
	public function __construct() {

		self::$dbh = parent::dataBase();

	}



	public function useTemplate($name) {
	    
	    $return = "";
        
        $downloaded = PUBLIC_PATH . "templates/Tmpfile.zip";
        $data = fopen("http://www.jurchin.com/Webservice/Rest/getTemplate/temp:".$name, 'r');
		if(trim($data) !== ""){
		    file_put_contents($downloaded, $data);
		    $return .= "donwloading \t\t... .. . okf ".$name."\n";
		}else{
		    $return .= "download problem \t\t... .. . bug! \n";
		    return $return;
		}
    
    
    
        $zip = new ZipHelper();
        $zip->extract($downloaded , PUBLIC_PATH . "templates/");
        
        if($GLOBALS['zipResult'] == "1"){
            $return .= "unziping \t\t... .. . ok \n";
        }else{
            $return .= "error unziping : " . $GLOBALS['zipResult'] . " \t\t... .. . bug ! \n";
            return $return;
        }
        
        
        
        
        // delete downloaded file tmp
        if(@unlink($downloaded)){
            $return .= "delete downloaded file \t\t... .. . ok \n";
        }else{
            $return .= "can NOT delete downloaded file \t\t... .. . bug! \n";
        }
        
        


        $return .= $this->moveToViews($name);
        $return .= $this->changeTemplate($name);
        
        return $return;
	}

	

    private function moveToViews($name){
        
        $tempViewFolder = VIEW_PATH . "Home/" . $name;
        $tempFilesFolder = PUBLIC_PATH . "templates/" . $name;
        
        $return = "";
        
        
        // create a folder with template name in veiws 
        @mkdir($tempViewFolder , 0755);
        
        foreach (glob($tempFilesFolder . "/page_*.html") as $page) {
             // Simple copy for a file
            $pageName = basename($page);
    	    if (!copy($page, $tempViewFolder."/".$pageName )) {
                $return .= "failed to copy ".$pageName." \t\t... .. . bug! \n";
            }else{
                $return .= $pageName." successfuly copied \t\t... .. . ok \n";
                unlink($page);
            }
        }
        return $return;
    }  


	public function changeTemplate($name) {
		$changeTemp = self::$dbh->update(
			'jor_settings',
			array(
				"value" =>  $name
			),
			array(
			    "parameter" =>"template"
			),
			'=',
			''
		);
		if(count($changeTemp) > 0){
		    return "set template as current template \t\t... .. . ok";
		}else{
		    return "NOT set template as current template \t\t... .. . bug !";
		}

	}




	public function DeletePost($sid , $pid ) {

		$delete = self::$dbh->delete(
			'jor_posts',
			array(
				"pid" 		=> $pid
			),
			"=",
			""
		);


		if($delete) {
			echo json_encode(
				array(
					"message"	=> "ok"
				)
			);
		}
	}



}
?>
