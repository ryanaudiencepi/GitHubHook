<?php
require_once('class.GitHubHook.php');

// Initiate the GitHub Deployment Hook
$hook = new GitHubHook;

// Enable the debug log, kindly make `log/hook.log` writable
$hook->enableDebug();

// Adding path for repository root directory
$hook->addPath('/var/www/');

// Deploy the commits
$hook->deploy();