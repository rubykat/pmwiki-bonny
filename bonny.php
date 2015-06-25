<?php if (!defined('PmWiki')) exit();
/* Copyright 2006-2007 Kathryn Andersen
 * This file is part of the Bonny skin.
 *
 * This is licenced under the Gnu Public Licence, version 2 or later.
 */

/* ==============================================================
 * Version
 */
global $FmtPV;
$SkinVersion = '2015-06-25';
$FmtPV['$SkinName'] = "'Bonny'";
$FmtPV['$SkinVersion'] = "'$SkinVersion'";

/* ==============================================================
 * Settings
 */
/* CMS */
global $BonnyCMS, $BonnyCMSHover, $BonnyMenuBar, $BonnyIncludeSearch;
SDV($BonnyCMS,0);
SDV($BonnyCMSHover,0);
SDV($BonnyMenuBar,0);
SDV($BonnyIncludeSearch,1);
/* Theme setting */
# Set default theme here,
# the default loads without any cookie being set.
# Different defaults can be set also in config.php 
# or a groups local/GroupName.php (for different coloured groups for instance)
# by adding lines like  $BonnyTheme = 'blue';
global $BonnyTheme, $BonnyLayout;
SDV($BonnyTheme,'gblue');
SDV($BonnyLayout,'roomy');

global $BonnyStyleSet;
# By default style options are enabled, 
# SDV($BonnyStyleSet, 0); #disables option setting via cookies.
SDV($BonnyStyleSet, 1);

/* ==============================================================
 * Page Variables
 */
global $PageLogoUrl;
$FmtPV['$PageLogoUrl'] = "'$PageLogoUrl'";
global $BonnyLogoLink;
$FmtPV['$BonnyLogoLink'] = "'$BonnyLogoLink'";

if (function_exists('ClusterPageName')) {
    $FmtPV['$ClusterMenuBar'] = 'ClusterPageName($group, "MenuBar")';
}

/* ==============================================================
 * Markup
 */
/* (:noleft:) redefinition */
Markup('noleft','directives','/\\(:noleft:\\)/', 'NoLeftBar');
/* (:noright:) redefinition */
Markup('noright','directives','/\\(:noright:\\)/', 'NoRightBar');

/* external link markup */
global $IMapLinkFmt;
$IMapLinkFmt['http:'] = 
  "<a class='externallink' href='\$LinkUrl'>\$LinkText</a>";

Markup('bonny', '<split', '/\\(:bonny\\s+(\w+)\\s?(.*?):\\)/i', 'BonnyMarkupHelper');
function BonnyMarkupHelper($m) {
    global $pagename;
    return BonnyMarkup($pagename, $m[1], $m[2]);
}

/* ============================================================== */
/*
 * automatic loading of skin default config pages
 */
global $WikiLibDirs, $SkinDir;
$where = count($WikiLibDirs);
if ($where>1) $where--;
array_splice($WikiLibDirs, $where, 0, 
	     array(new PageStore("$SkinDir/wikilib.d/\$FullName")));

/* ----------------------------------------- */
/* Titles */
global $HTMLTitleFmt, $DefaultName, $TitleFmt, $WikiTitle;

// check if GroupTitle is installed
if ($FmtPV['$GroupTitlespaced'])  {
    $grouptitle = PageVar($pagename, '$GroupTitlespaced');
}
else $grouptitle = PageVar($pagename, '$Groupspaced');

$title_ns = PageVar($pagename, '$Title');
// don't use the DefaultName for the title
if ($title_ns == "$DefaultName") {
    $title = $grouptitle;
} else {
    $title = PageVar($pagename, '$Titlespaced');
}
SDV($TitleFmt, "<div class='box'><h1 class='pagetitle'>$title</h1></div>");
SDV($HTMLTitleFmt, "$WikiTitle - $grouptitle - $title");

/* ==============================================================
 * Functions
 */
function NoLeftBar() {
    global $HTMLStylesFmt, $PageLeftFmt;
    SetTmplDisplay('PageLeftFmt',0);
    $HTMLStylesFmt[] = "
    body.right { padding-right: 0; }
    body.right #wikihead, body.right #wikifoot { margin-right: 0; }
    body.right #wikimid { border-right: none; margin-right: 0; }
    body.right #wikititle, body.right #wikitext { margin-right: 0; }
    body.trio, body.left { padding-left: 0; }
    body.trio #wikihead, body.trio #wikifoot,
    body.left #wikihead, body.left #wikifoot { margin-left: 0; }
    body.trio #wikimid, body.left #wikimid { border-left: none; margin-left: 0; }
    body.trio #wikititle, body.trio #wikitext,
    body.left #wikititle, body.left #wikitext { margin-left: 0; }
    ";
    return '';
}

function NoRightBar() {
    global $HTMLStylesFmt, $PageRightFmt;
    SetTmplDisplay('PageRightFmt',0);
    $HTMLStylesFmt[] = "body.trio { padding-right: 0; }
    body.trio #wikihead, body.trio #wikifoot { margin-right: 0; }
    body.trio #wikimid { border-right: none; margin-right: 0; }
    body.trio #wikititle, body.trio #wikitext { margin-right: 0; }
    ";
    return '';
}

function BonnyMarkup($pagename, $command, $argstr) {
    global $PageLayoutList, $SkinDir;
    $args = ParseArgs($argstr);

    $out = '';
    if ($command == 'themes')
    {
	# the themes are in the css directory in files theme_<name>.css
	$dir = "$SkinDir/css";
	$dfp = @opendir($dir); if (!$dfp) { return ''; }

	$themes = array();
	while (($cfile = readdir($dfp)) !== false)
	{
	    if ($cfile{0} == '.') continue;
	    if (preg_match('/theme_(\w+)\.css/', $cfile, $match))
	    {
		$theme = $match[1];
		$themes[] = $theme;
	    }
	}

	$action = ($args['set'] ? 'bonny_settheme' : 'bonny_theme');
	asort($themes);
	foreach ($themes as $theme)
	{
	    if ($args['list'])
	    {
		$out .= "* $theme\n";
	    }
	    else
	    {
		$out .= "* [[{*\$FullName}?$action=$theme|$theme]]\n";
	    }
	}
    }
    else if ($command == 'layouts')
    {
	$layouts = array_keys($PageLayoutList);
	asort($layouts);
	$action = ($args['set'] ? 'bonny_setlayout' : 'bonny_layout');
	foreach ($layouts as $lay)
	{
	    if ($args['list'])
	    {
		$out .= "* $lay\n";
	    }
	    else
	    {
		$out .= "* [[{*\$FullName}?$action=$lay|$lay]]\n";
	    }
	}
    }
    return $out;
}

/* ============================================================== */
/* Theme setting */

/*
 * Themes are defined by CSS files in the css directory.
 * They have the name theme_<name>.css
 * Therefore we can simply look in the directory for them.
 */

# option arrays, these can be expanded with more custom files.
# comment out any options not wanted.
# The keyword (left of arrow) is used to set the default. 
# Right of the arrow is the css file which gets loaded.

global $PageLayoutList;
SDVA($PageLayoutList, array (
        'snug' => 'layout_snug.css',
        'roomy' => 'layout_roomy.css',
        'snug_right' => 'layout_snug.css',
        'roomy_right' => 'layout_roomy.css',
        'trio' => 'layout_snug.css',
        'roomy_trio' => 'layout_roomy.css',
        ));

global $BodyClassList;
SDVA($BodyClassList, array (
        'snug' => 'snug left',
        'roomy' => 'roomy left',
        'snug_right' => 'snug right',
        'roomy_right' => 'roomy right',
        'trio' => 'snug trio',
        'roomy_trio' => 'roomy trio',
        ));

# The keywords must be defined below in the various Page...Lists
global $Now, $ThemeCss, $BonnyThemeCookie,
$LayoutCss, $BonnyLayoutCookie, $BodyClass;

# define variables
$st = $BonnyTheme;
$ThemeCss = "theme_$st.css";
$sl = $BonnyLayout;
$LayoutCss = $PageLayoutList[$sl];

if ($BonnyStyleSet == 1) {

# set cookie expire time (default 1 year)
SDV($ThemeCookieExpires,$Now+60*60*24*365);
SDV($LayoutCookieExpires,$Now+60*60*24*365);

# theme cookie routine 
SDV($BonnyThemeCookie, $CookiePrefix.'bonny_settheme');
if (isset($_COOKIE[$BonnyThemeCookie])) $st = $_COOKIE[$BonnyThemeCookie];
if (isset($_GET['bonny_settheme'])) {
  $st = $_GET['bonny_settheme'];
  setcookie($BonnyThemeCookie,$st,$ThemeCookieExpires,'/');}
  if (isset($_GET['bonny_theme'])) $st = $_GET['bonny_theme'];
if (file_exists("$SkinDir/css/theme_$st.css")) $ThemeCss = "theme_$st.css";
else $st = $BonnyTheme;

SDV($BonnyLayoutCookie, $CookiePrefix.'bonny_setlayout');
if (isset($_COOKIE[$BonnyLayoutCookie])) $sl = $_COOKIE[$BonnyLayoutCookie];
if (isset($_GET['bonny_setlayout'])) {
  $sl = $_GET['bonny_setlayout'];
  setcookie($BonnyLayoutCookie,$sl,$LayoutCookieExpires,'/');}
  if (isset($_GET['bonny_layout'])) $sl = $_GET['bonny_layout'];
if (@$PageLayoutList[$sl]) $LayoutCss = $PageLayoutList[$sl];
else $sl = $BonnyLayout;

#####end cookies
}

/* suppress the third column if not trio layout */
if ($sl != 'trio' && $sl != 'roomy_trio') {
    SetTmplDisplay('PageRightFmt',0);
}

# set the body classes from the layout
$BodyClass = $BodyClassList[$sl];

