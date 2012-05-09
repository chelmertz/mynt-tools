#!/usr/bin/env php
<?php

error_reporting(E_ALL|E_STRICT);

if(!isset($argv[1]) || !file_exists($argv[1])) {
	echo <<<USAGE
$ ./wp2mynt.php exported_file.xml

Generates a _posts folder in this directory based on the
exported_file.xml that you got from a Wordpress export
(within its admin section).

assuming
- wp categories aren't used, tags are
- before you run this script, prepare the xml file (since I don't wanna get messy with xml namespaces)
  - s/content:encoded>/content>
  - s/wp:post_date>/date>
  - s/wp:base_blog_url>/base_blog_url>
- that you only want the basic stuff. look through the xml for other data you might want
- also, there is zero error handling. chmod will probably help you in case you run into errors
USAGE;
	exit(1);
}
$xml = simplexml_load_file($argv[1]);

mkdir('_posts');

date_default_timezone_set('Europe/Stockholm');

$base_url = (string) $xml->channel->base_blog_url;

foreach($xml->channel->item as $post) {
	$tags = array();
	foreach($post->category as $category) {
		if('post_tag' == current($category->attributes()->domain)) {
			$tags[] = current($category->attributes()->nicename);
		}
	}
	$content = "---
layout: post.html
title: \"".str_replace('"', '\"', $post->title)."\"
url: \"".preg_replace("~^$base_url/?~", null, (string) $post->link)."\"
";
	if($tags) {
		$content .= "tags: [".implode(', ', $tags)."]";
	}
	$post_content = preg_replace('~\[cc lang=([^\]]+)\]~', '<pre><code lang="$1">', $post->content);
	$post_content = preg_replace('~\[cci lang=([^\]]+)\]~', '<code lang="$1">', $post_content);
	$post_content = str_replace('[/cc]', '</code></pre>', $post_content);
	$post_content = str_replace('[/cci]', '</code>', $post_content);
	$content .= "
---

$post_content";

	$filename = date('Y-m-d-H-i', strtotime((string)$post->date)).'-'.trim(preg_replace('~[^a-zA-Z0-9]+~', '-', (string)$post->title), '-').'.md';
	file_put_contents("_posts/$filename", $content);
}
exit(0);
