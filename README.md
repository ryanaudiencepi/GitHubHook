Fork from https://github.com/kwangchin/GitHubHook

## Repo Directory Structure
Given that `/var/www/` is repository root directory.
<pre>
/var/www/project1
/var/www/project2
/var/www/project3
</pre>


## How it works
Instead of deploying site with hooks, this fork use github hooks to sync/update repository on your remote server. The difference between this fork is that we use `rm -rf` and `git clone` to update the repository. Also, we don't do any `git checkout`.

To set repository root directory under `/var/www/`, add the following settings to `hook.php`.
<pre>
$hook->addPath('/var/www/');
</pre>


## Security
You need to give apache permission to carry out actions for repository root directory. Something like `chown apache:apache -R /var/www/` works fine. Never ever use `0777` for permission! And yes, change your `hook.php` file name
