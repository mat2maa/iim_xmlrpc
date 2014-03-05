<?php
require_once "File/Archive.php";

include("lib/xmlrpc.inc");
include("lib/xmlrpcs.inc");
include("lib/xmlrpc_wrappers.inc");

function valid_app_id($app_id){
	return $app_id == "83u948$%@^po93kdERVsdfWRG932$%@jr3248jr2(K^!D39480^ry32HTEW984";
}

function create_songs_zip($app_id, $playlist, $albums, $tracks, $track_names){
	if (valid_app_id($app_id)){	
		$zipfilename = "/share/Qweb/zip/tracks/" . $playlist . ".zip";
		$temp_folder = "/share/Qweb/zip/tracks/" . $playlist;
	
		if (file_exists($temp_folder)){
			unlinkRecursive($temp_folder, true);
		}
	
		mkdir($temp_folder);
	
		$files = array();
		$i=0;
		foreach ($tracks as $t) {
			copy("/share/MD0_DATA/Qmultimedia/" . $albums[$i] . "/" . $t . ".mp3", $temp_folder . "/" . $track_names[$i] . ".mp3" );
		  array_push($files, $temp_folder . "/" . $track_names[$i] . ".mp3" );   
			$i++;
		}	
	
	
		File_Archive::setOption("zipCompressionLevel", 0); 

		File_Archive::extract(
		    $files,
		    File_Archive::toArchive($zipfilename, File_Archive::toFiles())
		);
	
		unlinkRecursive($temp_folder, true);
	
		return 1;
	} else {
		return 0;
	}
}

function create_albums_zip($app_id, $playlist, $album_positions, $album_ids, $total_tracks){
	if (valid_app_id($app_id)){	
		$zipfilename = "/share/Qweb/zip/albums/" . $playlist . ".zip";
		$temp_folder = "/share/Qweb/zip/albums/" . $playlist;
		
		if (file_exists($temp_folder)){
			unlinkRecursive($temp_folder, true);
		}
	
		mkdir($temp_folder);
		
		$files = array();
		$i=0;
		foreach ($album_positions as $a) {
			for ($j = 1; $j <= $total_tracks[$i]; $j++){
				// add check to see if file exists, then copy file to temp dir and add file to array
				if (file_exists("/share/MD0_DATA/Qmultimedia/" . $album_ids[$i] . "/" . $j . ".mp3")){
					copy("/share/MD0_DATA/Qmultimedia/" . $album_ids[$i] . "/" . $j . ".mp3", $temp_folder . "/" . $album_positions[$i] . "-" . $j . ".mp3" );
					array_push($files, $temp_folder . "/" . $album_positions[$i] . "-" . $j . ".mp3"  );   
				}  
			}
			$i++;
		}
	
		File_Archive::setOption("zipCompressionLevel", 0); 

		File_Archive::extract(
		    $files,
		    File_Archive::toArchive($zipfilename, File_Archive::toFiles())
		);
	
		unlinkRecursive($temp_folder, true);
	
		return 1;
	} else {
		return 0;
	}
}


function delete_album_folder($app_id, $album_id){
	if (valid_app_id($app_id)){
		$folder_to_delete = "/share/MD0_DATA/Qmultimedia/" . $album_id;
	
		if (file_exists($folder_to_delete)){
			unlinkRecursive($folder_to_delete, true);
		}
		return 1;
		
	}else {
		return 0;
	}
}

function unlinkRecursive($dir, $deleteRootToo)
{
    if(!$dh = @opendir($dir))
    {
        return;
    }
    while (false !== ($obj = readdir($dh)))
    {
        if($obj == '.' || $obj == '..')
        {
            continue;
        }

        if (!@unlink($dir . '/' . $obj))
        {
            unlinkRecursive($dir.'/'.$obj, true);
        }
    }

    closedir($dh);

    if ($deleteRootToo)
    {
        @rmdir($dir);
    }

    return;
}


	
	$s = new xmlrpc_server(
    array(
		      "create_songs_zip" => array(
		        "function" => "create_songs_zip",
		        "signature" => array(
		          array($xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcArray, $xmlrpcArray, $xmlrpcArray )
		        )
		      ),
				"create_albums_zip" => array(
	        "function" => "create_albums_zip",
	        "signature" => array(
	          array($xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcArray, $xmlrpcArray, $xmlrpcArray )
	        )
	      ),
				"delete_album_folder" => array(
	        "function" => "delete_album_folder",
	        "signature" => array(
	          array($xmlrpcString, $xmlrpcString, $xmlrpcString )
	        )
	      )
		    ), false);
  $s->functions_parameters_type = 'phpvals';
  $s->service();
?>