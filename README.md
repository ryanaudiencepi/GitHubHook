Fork from https://github.com/kwangchin/GitHubHook

## Repo Directory Structure
Given that `/var/www/` is repository root directory. `master`, `production` and `testing` are branch names.
<code><pre>
/var/www/master/project1
/var/www/master/project2
/var/www/master/project3
/var/www/production/project1
/var/www/production/project2
/var/www/production/project3
/var/www/testing/project1
/var/www/testing/project2
/var/www/testing/project3
</code></pre>


## How it works
Instead of deploying site with hooks, this fork use github hooks to sync/update repository on your remote server.

Using repository directory structure from previous example, if you want to checkout branches `master`, `production`, `testing` and place it under directory `/var/www/`, add the following settings to `hook.php`.
<code><pre>
$hook->addPath('/var/www/');
$hook->addBranch('master');
$hook->addBranch('production');
$hook->addBranch('testing');
</code></pre>


## Security
You need to give apache permission to create branch directory if it doesn't exist. Something like `chown apache:apache -R /var/www/` works fine. Never ever use `0777` for permission! And yes, change your `hook.php` file name
