<?php

include("lib/xmlrpc.inc");
include("lib/xmlrpcs.inc");
include("lib/xmlrpc_wrappers.inc");

class HZip
{
  /**
   * Add files and sub-directories in a folder to zip file.
   * @param string $folder
   * @param ZipArchive $zipFile
   * @param int $exclusiveLength Number of text to be exclusived from the file path.
   */
  private static function folderToZip($folder, &$zipFile, $exclusiveLength)
  {
    $handle = opendir($folder);
    while (false !== $f = readdir($handle)) {
      if ($f != '.' && $f != '..') {
        $filePath = "$folder/$f";
        // Remove prefix from file path before add to zip.
        $localPath = substr($filePath, $exclusiveLength);
        if (is_file($filePath)) {
          $zipFile->addFile($filePath, $localPath);
        } elseif (is_dir($filePath)) {
          // Add sub-directory.
          $zipFile->addEmptyDir($localPath);
          self::folderToZip($filePath, $zipFile, $exclusiveLength);
        }
      }
    }
    closedir($handle);
  }

  /**
   * Zip a folder (include itself).
   * Usage:
   *   HZip::zipDir('/path/to/sourceDir', '/path/to/out.zip');
   *
   * @param string $sourcePath Path of directory to be zip.
   * @param string $outZipPath Path of output zip file.
   */
  public static function zipDir($sourcePath, $outZipPath)
  {
    $pathInfo = pathInfo($sourcePath);
    $parentPath = $pathInfo['dirname'];
    $dirName = $pathInfo['basename'];

    $z = new ZipArchive();
    $z->open($outZipPath, ZIPARCHIVE::CREATE);
    $z->addEmptyDir($dirName);
    self::folderToZip($sourcePath, $z, strlen("$parentPath/"));
    $z->close();
  }
}

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

function create_tracks_zip($app_id, $playlist, $albums, $tracks, $track_positions)
{
  if (valid_app_id($app_id) == 0) {
    $zip_file = "/share/Qweb/zip/tracks/" . $playlist . ".zip";
    $temp_folder = "/share/Qweb/zip/tracks/" . $playlist;

    if (file_exists($temp_folder)) {
      unlinkRecursive($temp_folder, true);
    }

    mkdir($temp_folder);

    $errors = array();
    $i = 0;
    foreach ($tracks as $t) {

      if (file_exists("/share/MD0_DATA/Qmultimedia/" . $albums[$i] . "/" . $t . ".mp3")) {
        copy("/share/MD0_DATA/Qmultimedia/" . $albums[$i] . "/" . $t . ".mp3", $temp_folder . "/" . $track_positions[$i] . ".mp3");
      } else {
        $error = $albums[$i] . "/" . $t;
        array_push($errors, $error);
      }

      $i++;
    }

    HZip::zipDir($temp_folder, $zip_file);

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

    $errors = array();
    $i = 0;

    foreach ($album_positions as $a) {
      for ($j = 1; $j <= $tracks[$i]; $j++) {
        if (file_exists("/share/MD0_DATA/Qmultimedia/" . $albums[$i] . "/" . $j . ".mp3")) {
          copy("/share/MD0_DATA/Qmultimedia/" . $albums[$i] . "/" . $j . ".mp3", $temp_folder . "/" . $album_positions[$i] . "-" . $j . ".mp3");
        } else {
          $error = $albums[$i] . "/" . $j;
          array_push($errors, $error);
        }
      }
      $i++;
    }

    HZip::zipDir($temp_folder, $zip_file);

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
    )
  ), false);
$s->functions_parameters_type = 'phpvals';
$s->service();

?>
