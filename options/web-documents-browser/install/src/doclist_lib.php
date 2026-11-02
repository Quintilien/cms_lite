<?php
// Web Documents Browser (CMS Lite option): "doclist_allowed" setting and checks
// shared by doclist.php and display*.php. Rev. 4.7.0: moved out of src/config.php,
// together with the option. To be copied into src/ (see the option's README).

// Allowed directories: "doclist_allowed" key of config.ini, comma-separated
$doclist_allowed = (string)(cms_config_value($config_dir, 'doclist_allowed') ?? '');
if ( $doclist_allowed != "" ) {
  $doclist_allowed = explode(",", $doclist_allowed);
}
else
{
  $doclist_allowed = array();
}
for($i = 0; $i < count($doclist_allowed); $i++) {
  $doclist_allowed[$i] = trim($doclist_allowed[$i]);
}

// Rev. 4.3.2: a directory is accepted only if it is listed (as is) in doclist_allowed;
// a file only if it is a plain name (no path, not hidden) of a file in that directory.
function cms_doc_dir_ok($dir, $allowed) {
  return is_string($dir) && $dir !== '' && in_array($dir, $allowed, true) && is_dir($dir);
}
function cms_doc_file_ok($dir, $file) {
  if (!is_string($file) || $file === '' || $file !== basename($file) || $file[0] === '.'
      || strpbrk($file, "\\/\0") !== false) {
    return false;
  }
  $base = realpath($dir);
  $full = realpath($dir.'/'.$file);
  return $base !== false && $full !== false && is_file($full)
         && strpos($full, $base.DIRECTORY_SEPARATOR) === 0;
}
// URL of a document: each path segment encoded (spaces, quotes, accents...)
function cms_doc_url($dir, $file) {
  return implode('/', array_map('rawurlencode', explode('/', $dir.'/'.$file)));
}
