<?php
error_reporting(0);

/**
 * GitHub Post-Receive Deployment Hook.
 *
 * @author Chin Lee <kwangchin@gmail.com>
 * @copyright Copyright (C) 2012 Chin Lee
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @version 1.0
 */

class GitHubHook
{
    /**
     * @var string Remote IP of the person.
     * @since 1.0
     */
    private $_remoteIp = '';
    
    /**
     * @var object Payload from GitHub.
     * @since 1.0
     */
    private $_payload = '';
    
    /**
     * @var boolean Log debug messages.
     * @since 1.0
     */
    private $_logging = TRUE;
    
    /**
     * @var string path to repo directory
     * @since 1.0
     */
    private $_path = '';
    
    /**
     * @var array Branches.
     * @since 1.0
     */
    private $_branches = array();
    
    /**
     * @var array GitHub's public IP addresses for hooks (CIDR notation).
     */
    private $_github_public_cidrs = array('204.232.175.64/27', '192.30.252.0/22');
    
    /**
     * Constructor.
     * @since 1.0
     */
    function __construct()
    {
        /* Support for EC2 load balancers */
        if (isset($_SERVER['HTTP_X_FORWARDED_FOR']) && filter_var($_SERVER['HTTP_X_FORWARDED_FOR'], FILTER_VALIDATE_IP)) {
            $this->_remoteIp = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $this->_remoteIp = $_SERVER['REMOTE_ADDR'];
        }
        
        if (isset($_POST['payload'])) {
            $this->_payload = json_decode($_POST['payload']);
        } else {
            $this->_notFound('Payload not available from: ' . $this->_remoteIp);
        }
    }
    
    /**
     * Centralize our 404.
     * @param string $reason Reason of 404 Not Found.
     * @since 1.1
     */
    private function _notFound($reason = NULL)
    {
        if ($reason !== NULL) {
            $this->log($reason);
        }
        
        header('HTTP/1.1 404 Not Found');
        echo '404 Not Found.';
        exit;
    }
    
    /**
     * IP in CIDRs Match - checks whether an IP exists within an array of CIDR ranges.
     * @link - http://stackoverflow.com/questions/10243594/find-whether-a-given-ip-exists-in-cidr-or-not?lq=1
     * @param string $ip - IP address in '127.0.0.1' format
     * @param array $cidrs - array storing CIDRS in 192.168.1.20/27 format.
     * @return bool
     */
    private function ip_in_cidrs($ip, $cidrs)
    {
        $ipu = explode('.', $ip);
        
        foreach ($ipu as &$v) {
            $v = str_pad(decbin($v), 8, '0', STR_PAD_LEFT);
        }
        
        $ipu    = join('', $ipu);
        $result = FALSE;
        
        foreach ($cidrs as $cidr) {
            $parts = explode('/', $cidr);
            $ipc   = explode('.', $parts[0]);
            
            foreach ($ipc as &$v)
                $v = str_pad(decbin($v), 8, '0', STR_PAD_LEFT); {
                $ipc    = substr(join('', $ipc), 0, $parts[1]);
                $ipux   = substr($ipu, 0, $parts[1]);
                $result = ($ipc === $ipux);
            }
            
            if ($result)
                break;
        }
        
        return $result;
    }
    
    /**
     * Enable log of debug messages.
     * @since 1.0
     */
    public function disableLogging()
    {
        $this->_logging = FALSE;
    }
    
    /**
     * Add a path for repo directory
     * @param string $path Path name, defaults to '/var/www/'.
     * @since 1.0
     */
    public function addPath($path = '/var/www/')
    {
        $this->_path = $path;
    }
    
    /**
     * Log a message.
     * @param string $message Message to log.
     * @since 1.0
     */
    public function log($message)
    {
        if ($this->_logging) {
            openlog('php', LOG_CONS | LOG_NDELAY | LOG_PID, LOG_USER | LOG_PERROR);
            syslog(LOG_INFO, $message);
            closelog();
        }
    }
    
    /**
     * Deploys.
     * @since 1.0
     */
    public function deploy()
    {
        // Check the remote is a whitelisted GitHub public ip.
        if ($this->ip_in_cidrs($this->_remoteIp, $this->_github_public_cidrs)) {
            $repo_name    = $this->_payload->repository->name;
            $repo_url     = $this->_payload->repository->url;
            $path_to_repo = "{$this->_path}{$repo_name}";
            
            if (file_exists($path_to_repo)) {
                $this->log('Delete repo ' . $path_to_repo);
                shell_exec("rm -rf {$path_to_repo}");
            }
            
            $this->log('Adding repo ' . $repo_url . ' to path ' . $path_to_repo);
            shell_exec("cd {$this->_path} && git clone {$repo_url} {$repo_name} 2>&1");
            
            
            
        } else {
            // IP of remote is invalid.
            $this->_notFound('IP address not recognized: ' . $this->_remoteIp);
        }
    }
}
