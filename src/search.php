<?php

//***********
require_once('workflows.php');

$w = new Workflows();
if (!isset($query)) { $query = urlencode( "{query}" ); }

$username = $w->get( 'github.username', 'settings.plist' );
$password = $w->get( 'github.password', 'settings.plist' );

//$url = "https://api.github.com/search/repositories?q=$query"; // preview only
$url = "https://api.github.com/legacy/repos/search/$query";

if($username && $password) {
	exec('sh auth.sh -u '.escapeshellarg($username).' -p '.escapeshellarg($password).' --url '.escapeshellarg($url), $output, $return_var);

	$data = implode($output);
	$content = substr($data, strpos($data, "{"));
	$content = substr($content, 0, strrpos($content, "}")+1);
	$repos = json_decode( $content );
} else {
	$content = $w->request( $url );
	$repos = json_decode( $content );
}

if (isset($repos->message)) {
	$w->result( $repos->message, $repos->message, 'Github Limit', $repos->message, 'icon.png', 'no' );
} else {
	$repos = $repos->repositories;
	foreach($repos as $repo ) {
		$lang = $repo->language ? ' ('.$repo->language.')' : '';
		$repo->full_name = $repo->username.'/'.$repo->name;
		$w->result( 'git-'.$repo->full_name, $repo->url, $repo->name.''.$lang, $repo->description, 'icon.png', 'yes' );
	}
	
	if ( count( $w->results() ) == 0 ){
		$w->result( 'git', null, 'No Repository found', 'No Repository found that match your query', 'icon.png', 'no' );
	}
}

echo $w->toxml();

?>