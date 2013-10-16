<?php
require_once('class.GitHubHook.php');

// Initiate the GitHub Deployment Hook
$hook = new GitHubHook;

// Adding path for repository root directory
$hook->addPath('/var/www/');

// Deploy the commits
$hook->deploy();