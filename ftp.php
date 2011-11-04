<?
$obj = json_decode($_POST['data']);
switch ($obj->action) {
    case "listfile":
		$connectionId =  ftpConnect();
		ftpRawList($connectionId,$obj->params->path,$obj->params->recursive,$arrayFiles);
		$response->status = "ok";
		$response->message = "";
		$response->params = $arrayFiles;
		echo json_encode($response);
		break;
	case "deletefile":
		$connectionId =  ftpConnect();
		$response = deleteFile($connectionId,$obj->params->path);
		echo json_encode($response);
		break;
    case "publish":
		$connectionId = ftpConnect();
		$response = publishFile($connectionId,$obj->params->pathOldFile,$obj->params->pathNewFile);
		echo json_encode($response);
		break;
	case "saveDraft":
		$connectionId = ftpConnect();
		$response = saveDraft($connectionId,$obj->params->path,$obj->params->dom);
		echo json_encode($response);
		break;
}





 
 /*
 *  [params] => stdClass Object
        (
  * 		[connection] => STRING
            [directoryPath] => STRING
  * 		[recursive] => BOOLEAN
  * 		[nodeJSON] => reference ARRAY 
        )
 * 
 */
function ftpRawList($connectionId, $directoryPath,$recursive,&$nodeJSON) {
	
	$contents = ftp_rawlist($connectionId,$directoryPath);
	foreach($contents as $entry) {

		$entry = preg_replace('/\s+/', ' ', $entry);
		$entryExplode = explode(" ",$entry);
		
		// check . o ..
		$newFile = null;
		
		if ($entryExplode[0][0] == 'd') 
			$newFile->type = 'dir';
		else 
			$newFile->type = 'file';	
		
		//aggiungi data nome
		$newFile->name = $entryExplode[8];
		
		$nodeJSON[] = $newFile;
		
		if ($newFile->type == 'dir' AND $recursive == true)
			ftpRawList($connectionId, $directoryPath. "/" .$newFile->name,true,$newFile->children);	
	}
}

/*
 *  [params] => stdClass Object
        (
  * 		[connection] => STRING
            [path] => STRING
  *         [dom] => STRING
        )
 * 
 */
function saveDraft($connectionId,$path,$dom){
	$tmpfname = tempnam("/tmp", "FOO");
	$handle = fopen($tmpfname, "w+");
	fwrite($handle, $dom);
	fclose($handle);
	if(@ftp_put($connectionId,$path,$tmpfname,FTP_BINARY)){
		$response->status = true;
	}else{
		$response->status = false;
		$response->message = "cant save draft ftp";
	}
	return $response;
}



 /*
 *  [params] => stdClass Object
        (
  * 		[connection] => STRING
            [pathOldFile] => STRING
  *         [pathNewFile] => STRING
        )
 * 
 */
function publishFile($connectionId,$pathOldFile,$pathNewFile){
	if($pathOldFile != "" AND $pathNewFile != "")
	{
		if(!@ftp_delete($connectionId, $pathNewFile))
		{
			$response->status = false;
			$response->message = "can't delete file".$pathNewFile;
		} elseif (!@ftp_rename($connectionId, $pathOldFile, $pathNewFile)) {
			$response->status = false;
			$response->message = "can't rename file ".$pathOldFile." to new file".$pathNewFile;
		} else 
			$response->status = true;
		
		return $response;
	}
}



 /*
 *  [params] => stdClass Object
        (
  * 		[connection] => STRING
            [path] => STRING
        )
 * 
 */
function deleteFile($connectionId,$path){
	if($path != "")
	{
		$bool = @ftp_delete($connectionId, $path);	
		if($bool === false)
		{
			$response->status = false;
			$response->message = "can't delete file".$path;
		} else 
			$response->status = true;
		
		return $response;
	}
}





function ftpConnect(){
	
	$ftpServer = "www.orangelabs.it";
	$ftpUser = "soaceg68";
	$ftpPass = "YVDGvNLg";
	$ftpDirPath = "";
	// set up a connection or die
	$connectionId = ftp_connect($ftpServer) or die("Couldn't connect to $ftp_server"); 
	ftp_login($connectionId, $ftpUser, $ftpPass);
	ftp_pasv($connectionId, true);
	return $connectionId;
}

?>