<?php
header('Content-type: text/xml; charset=utf-8');
if(function_exists('date_default_timezone_set')) date_default_timezone_set('Asia/Shanghai');
include_once('inc/config.inc.php');
include_once('inc/db.class.php');

$tpl_xml = '<?xml version="1.0" encoding="UTF-8"?>'."\r\n"
	.'<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'
	.' $urlset'."\r\n"
	.'</urlset>';
$tpl_url = ' <url>'."\r\n"
	.'  <loc>$loc</loc>'."\r\n"
	.'  <lastmod>$lastmod</lastmod>'."\r\n"
	.'  <changefreq>$changefreq</changefreq>'."\r\n"
	.'  <priority>$priority</priority>'."\r\n"
	.' </url>';

$db = new DB("mysql:dbname=".TM_DB_NAME.";host=".TM_DB_HOST.";port=".TM_DB_PORT.";charset=utf-8", TM_DB_USER, TM_DB_PW);

$_hollow = $db->get_all('SELECT `hollow_sid`,`hollow_sname` FROM `hollow` WHERE `hollow_state`!=0', array());

$urlset = '';
if( $_hollow ) {
	foreach( $_hollow as $k => $v ) {
		if( !empty($v['hollow_sid']) ) {
			$urlset.="\r\n".str_replace( array( '$loc', '$lastmod', '$changefreq', '$priority' ),array( "http://$_SERVER[SERVER_NAME]/".str_replace(array('{','-','}'),'',$v['hollow_sid']), date("Y-m-d",time()), 'never', '1.0' ),$tpl_url );
		}
		if( !empty($v['hollow_sname']) ) {
			$urlset.="\r\n".str_replace( array( '$loc', '$lastmod', '$changefreq', '$priority' ),array( "http://$_SERVER[SERVER_NAME]/".$v['hollow_sname'], date("Y-m-d",time()), 'never', '1.0' ),$tpl_url );
		}
	}
}

$xml_context = str_replace( array('$urlset'), array($urlset), $tpl_xml );

if( !is_dir(__file__.'./sitemap/') ) {
	if(function_exists('mkdir')) @mkdir(__file__.'./sitemap/');
}
if( is_dir(__file__.'./sitemap/') && $file = fopen("./sitemap/hollow.xml","w") ) {
	fwrite( $file, $xml_context );
	fclose($file);
}
echo $xml_context;
?>