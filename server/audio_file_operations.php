<?php
require_once "pear/PEAR/File/Archive.php";

include("lib/xmlrpc.inc");
include("lib/xmlrpcs.inc");
include("lib/xmlrpc_wrappers.inc");

function valid_app_id($app_id)
{
  return strcmp($app_id, "83u948$%@^po93kdERVsdfWRG932$%@jr3248jr2(K^!D39480^ry32HTEW984");
}

/**
 * function msg($app_id) returns
 * @param $app_id
 * @return array
 */
function msg($app_id)
{
  global $xmlrpcerruser;
  if (valid_app_id($app_id) != 0) {
    return new xmlrpcresp(0, $xmlrpcerruser + 1, 'DOH!');
  } else {
    return array(
      'name' => 'Matthew Ager',
      'age' => 30
    );
  }
}

/**
 * function create_tracks_zip($app_id, $playlist, $albums, $tracks, $track_positions) returns
 * @param $app_id
 * @param $playlist
 * @param $albums
 * @param $tracks
 * @param $track_positions
 * @return array or integer
 */
function create_tracks_zip($app_id, $playlist, $albums, $tracks, $track_positions)
{
  if (valid_app_id($app_id) == 0) {
    ini_set('max_execution_time', 0); //no limit
    $zip_file = "/share/Qweb/zip/tracks/" . $playlist . ".zip";
    $temp_folder = "/share/Qweb/zip/tracks/" . $playlist;

    if (file_exists($temp_folder)) {
      unlinkRecursive($temp_folder, true);
    }

    mkdir($temp_folder);

    $files = array();
    $errors = array();
    $i = 0;
    foreach ($tracks as $t) {

      if (file_exists("/share/MD0_DATA/Qmultimedia/" . $albums[$i] . "/" . $t . ".mp3")) {
        copy("/share/MD0_DATA/Qmultimedia/" . $albums[$i] . "/" . $t . ".mp3", $temp_folder . "/" . $track_positions[$i] . ".mp3");
        array_push($files, $temp_folder . "/" . $track_positions[$i] . ".mp3");
      } else {
        $error = $albums[$i] . "/" . $t;
        array_push($errors, $error);
      }

      $i++;
    }

    File_Archive::setOption("zipCompressionLevel", 0);

    File_Archive::extract(
      $files,
      File_Archive::toArchive($zip_file, File_Archive::toFiles())
    );

    unlinkRecursive($temp_folder, true);

    chmod($zip_file, 0755);

    if (count($errors) == 0) {
      return 1;
    } else {
      return $errors;
    }
  } else {
    return 0;
  }
}

/**
 * function create_albums_zip($app_id, $playlist, $albums, $tracks, $album_positions) returns
 * @param $app_id
 * @param $playlist
 * @param $albums
 * @param $tracks
 * @param $album_positions
 * @return array or integer
 */
function create_albums_zip($app_id, $playlist, $albums, $tracks, $album_positions)
{
  if (valid_app_id($app_id) == 0) {
    ini_set('max_execution_time', 0); //no limit
    $zip_file = "/share/Qweb/zip/albums/" . $playlist . ".zip";
    $temp_folder = "/share/Qweb/zip/albums/" . $playlist;

    if (file_exists($temp_folder)) {
      unlinkRecursive($temp_folder, true);
    }

    mkdir($temp_folder);

    $files = array();
    $errors = array();
    $i = 0;

    foreach ($album_positions as $a) {
      for ($j = 1; $j <= $tracks[$i]; $j++) {
        if (file_exists("/share/MD0_DATA/Qmultimedia/" . $albums[$i] . "/" . $j . ".mp3")) {
          copy("/share/MD0_DATA/Qmultimedia/" . $albums[$i] . "/" . $j . ".mp3", $temp_folder . "/" . $album_positions[$i] . "-" . $j . ".mp3");
          array_push($files, $temp_folder . "/" . $album_positions[$i] . "-" . $j . ".mp3");
        } else {
          $error = $albums[$i] . "/" . $j;
          array_push($errors, $error);
        }
      }
      $i++;
    }

    File_Archive::setOption("zipCompressionLevel", 0);

    File_Archive::extract(
      $files,
      File_Archive::toArchive($zip_file, File_Archive::toFiles())
    );

    unlinkRecursive($temp_folder, true);

    chmod($zip_file, 0755);

    if (count($errors) == 0) {
      return 1;
    } else {
      return $errors;
    }
  } else {
    return 0;
  }
}

/**
 * function delete_album_folder($app_id, $album_id) returns
 * @param $app_id
 * @param $album_id
 * @return integer
 */
function delete_album_folder($app_id, $album_id)
{
  if (valid_app_id($app_id) == 0) {
    $folder_to_delete = "/share/MD0_DATA/Qmultimedia/" . $album_id;

    if (file_exists($folder_to_delete)) {
      unlinkRecursive($folder_to_delete, true);
    }
    return 1;
  } else {
    return 0;
  }
}

/**
 * Recursively delete a directory
 *
 * @param string $dir Directory name
 * @param boolean $deleteRootToo Delete specified top-level directory as well
 */
function unlinkRecursive($dir, $deleteRootToo)
{
  if (!$dh = @opendir($dir)) {
    return;
  }
  while (false !== ($obj = readdir($dh))) {
    if ($obj == '.' || $obj == '..') {
      continue;
    }

    if (!@unlink($dir . '/' . $obj)) {
      unlinkRecursive($dir . '/' . $obj, true);
    }
  }

  closedir($dh);

  if ($deleteRootToo) {
    @rmdir($dir);
  }

  return;
}

$s = new xmlrpc_server(
  array(
    "msg" => array(
      "function" => "msg",
      "signature" => array(
        array($xmlrpcString, $xmlrpcString)
      )
    ),
    "create_tracks_zip" => array(
      "function" => "create_tracks_zip",
      "signature" => array(
        array($xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcArray, $xmlrpcArray, $xmlrpcArray)
      )
    ),
    "create_albums_zip" => array(
      "function" => "create_albums_zip",
      "signature" => array(
        array($xmlrpcString, $xmlrpcString, $xmlrpcString, $xmlrpcArray, $xmlrpcArray, $xmlrpcArray)
      )
    ),
    "delete_album_folder" => array(
      "function" => "delete_album_folder",
      "signature" => array(
        array($xmlrpcString, $xmlrpcString, $xmlrpcString)
      )
    )
  ), false);
$s->functions_parameters_type = 'phpvals';
$s->service();

?>
