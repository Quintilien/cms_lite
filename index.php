<?php
// CMS Lite 4 — the web root should be public/ (see README).
// This redirect only exists in case the whole repository was dropped into the
// web root; .htaccess then protects the rest (Apache only).
header('Location: public/');
exit;
