<?php
/**
 * IT Store — self-updater engine.
 *
 * Fetches the theme + all itstore* modules from a GitHub repository and updates
 * them in place: it downloads the branch/release archive, backs up the current
 * files, copies the new theme (themes/itstore) and every modules/itstore*
 * directory over the live install, runs each installed module's upgrade
 * scripts, and clears the cache.
 *
 * Nothing here runs on install — only when the back-office button or the cron
 * endpoint calls run()/check(), so a shop with no outbound network still
 * installs and works normally.
 *
 * @author  Syber Info <admin@syberinfo.com.au>
 * @license MIT
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class ItstoreUpdater
{
    /** @var string[] */
    public $log = [];

    protected $owner;
    protected $repo;
    protected $branch;
    protected $channel; // 'branch' | 'release'
    protected $token;

    public function __construct()
    {
        $this->owner = (string) (Configuration::get('ITSTORE_UPD_OWNER') ?: 'deepindersingh83');
        $this->repo = (string) (Configuration::get('ITSTORE_UPD_REPO') ?: 'gamedesign');
        $this->branch = (string) (Configuration::get('ITSTORE_UPD_BRANCH') ?: 'main');
        $this->channel = (string) (Configuration::get('ITSTORE_UPD_CHANNEL') ?: 'branch');
        $this->token = (string) Configuration::get('ITSTORE_UPD_TOKEN');
    }

    protected function log($msg)
    {
        $this->log[] = '[' . date('H:i:s') . '] ' . $msg;
    }

    /**
     * Give the update as much runway as the host allows.
     *
     * Downloading the archive, extracting it and copying the theme plus every
     * itstore* module easily runs past PHP's default 30–60s max_execution_time,
     * which is what surfaces as "Maximum execution time of N seconds exceeded".
     * We lift the wall-clock and memory ceilings (best-effort — some hosts hard
     * disable set_time_limit) and keep the request alive if the admin navigates
     * away. resetTimer() is called again at each heavy step so a per-step budget
     * host still gets a fresh slice.
     */
    protected function raiseLimits()
    {
        $this->resetTimer();
        if (function_exists('ignore_user_abort')) {
            @ignore_user_abort(true);
        }
        $mem = @ini_get('memory_limit');
        if ($mem !== false && $mem !== '-1') {
            $bytes = (int) $mem;
            if (stripos($mem, 'g') !== false) {
                $bytes *= 1024;
            }
            if ($bytes < 256) {
                @ini_set('memory_limit', '256M');
            }
        }
    }

    /** Reset the max-execution-time counter (best-effort). */
    protected function resetTimer()
    {
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }
        @ini_set('max_execution_time', '0');
    }

    /* ---------------------------------------------------------------- HTTP */

    protected function http($url, $binary = false)
    {
        if (!function_exists('curl_init')) {
            $this->log('cURL is not available on this server.');

            return false;
        }
        $headers = ['User-Agent: itstoreupdate'];
        if ($this->token !== '') {
            $headers[] = 'Authorization: token ' . $this->token;
        }
        if ($binary) {
            // The archive download is the single longest network step.
            $this->resetTimer();
        }
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_MAXREDIRS => 5,
            CURLOPT_CONNECTTIMEOUT => 20,
            CURLOPT_TIMEOUT => $binary ? 180 : 30,
            CURLOPT_SSL_VERIFYPEER => true,
            CURLOPT_HTTPHEADER => $headers,
        ]);
        $body = curl_exec($ch);
        $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($body === false || $code >= 400) {
            $this->log('HTTP ' . $code . ' fetching ' . $url . ($err ? ' — ' . $err : ''));

            return false;
        }

        return $body;
    }

    /* ------------------------------------------------------------- VERSION */

    public function getLocalVersion()
    {
        $stored = (string) Configuration::get('ITSTORE_UPD_INSTALLED_VERSION');
        if ($stored !== '') {
            return $stored;
        }
        $file = _PS_MODULE_DIR_ . 'itstoreupdate/version.json';
        if (is_file($file)) {
            $j = json_decode((string) file_get_contents($file), true);
            if (is_array($j) && isset($j['version'])) {
                return (string) $j['version'];
            }
        }

        return '0.0.0';
    }

    /**
     * Remote version + download URL for the configured channel.
     *
     * @return array{version:string,zip:string}|false
     */
    public function getRemote()
    {
        if ($this->channel === 'release') {
            $api = 'https://api.github.com/repos/' . $this->owner . '/' . $this->repo . '/releases/latest';
            $raw = $this->http($api);
            if ($raw === false) {
                return false;
            }
            $data = json_decode($raw, true);
            if (!is_array($data) || empty($data['tag_name'])) {
                $this->log('No published release found.');

                return false;
            }

            return [
                'version' => ltrim((string) $data['tag_name'], 'v'),
                'zip' => !empty($data['zipball_url']) ? (string) $data['zipball_url']
                    : 'https://codeload.github.com/' . $this->owner . '/' . $this->repo . '/zip/refs/tags/' . rawurlencode((string) $data['tag_name']),
            ];
        }

        // Branch channel: read the version.json shipped in the repo.
        $rawUrl = 'https://raw.githubusercontent.com/' . $this->owner . '/' . $this->repo
            . '/' . $this->branch . '/modules/itstoreupdate/version.json';
        $raw = $this->http($rawUrl);
        if ($raw === false) {
            return false;
        }
        $j = json_decode($raw, true);
        if (!is_array($j) || empty($j['version'])) {
            $this->log('Could not read remote version.json.');

            return false;
        }

        return [
            'version' => (string) $j['version'],
            'zip' => 'https://codeload.github.com/' . $this->owner . '/' . $this->repo
                . '/zip/refs/heads/' . str_replace('%2F', '/', rawurlencode($this->branch)),
        ];
    }

    /**
     * @return array{current:string,latest:string,update:bool}|false
     */
    public function check()
    {
        $remote = $this->getRemote();
        if ($remote === false) {
            return false;
        }
        $current = $this->getLocalVersion();
        Configuration::updateValue('ITSTORE_UPD_LAST_CHECK', date('Y-m-d H:i:s'));
        Configuration::updateValue('ITSTORE_UPD_LATEST_VERSION', $remote['version']);

        return [
            'current' => $current,
            'latest' => $remote['version'],
            'update' => version_compare($remote['version'], $current, '>'),
        ];
    }

    /* --------------------------------------------------------------- APPLY */

    /**
     * Download + apply an update. Returns true on success.
     */
    public function run($force = false)
    {
        $this->raiseLimits();
        $this->log('Starting update.');
        $remote = $this->getRemote();
        if ($remote === false) {
            $this->log('Aborted: could not resolve the remote release.');

            return false;
        }
        $current = $this->getLocalVersion();
        if (!$force && !version_compare($remote['version'], $current, '>')) {
            $this->log('Already up to date (v' . $current . ').');

            return true;
        }
        $this->log('Updating from v' . $current . ' to v' . $remote['version'] . '.');

        if (!class_exists('ZipArchive')) {
            $this->log('Aborted: the PHP zip extension is required.');

            return false;
        }

        $work = _PS_MODULE_DIR_ . 'itstoreupdate/work';
        $this->rrmdir($work);
        if (!@mkdir($work, 0775, true) && !is_dir($work)) {
            $this->log('Aborted: cannot create work dir ' . $work);

            return false;
        }

        $zipPath = $work . '/package.zip';
        $this->log('Downloading package…');
        $this->resetTimer();
        $bytes = $this->http($remote['zip'], true);
        if ($bytes === false || @file_put_contents($zipPath, $bytes) === false) {
            $this->log('Aborted: download failed.');
            $this->rrmdir($work);

            return false;
        }
        $this->log('Downloaded ' . number_format(strlen($bytes) / 1024, 0) . ' KB.');

        $zip = new ZipArchive();
        if ($zip->open($zipPath) !== true) {
            $this->log('Aborted: could not open the archive.');
            $this->rrmdir($work);

            return false;
        }
        $zip->extractTo($work);
        $zip->close();

        // The archive contains a single top-level folder (repo-branch).
        $root = $this->findExtractRoot($work);
        if ($root === false) {
            $this->log('Aborted: unexpected archive layout.');
            $this->rrmdir($work);

            return false;
        }

        $srcModules = $root . '/modules';
        $srcTheme = $root . '/themes/itstore';
        if (!is_dir($srcModules) || !is_dir($srcTheme)) {
            $this->log('Aborted: archive is missing modules/ or themes/itstore.');
            $this->rrmdir($work);

            return false;
        }

        $backup = _PS_MODULE_DIR_ . 'itstoreupdate/backups/' . date('Ymd-His');
        @mkdir($backup, 0775, true);

        // ----- Update theme
        $themeDest = rtrim(_PS_ALL_THEMES_DIR_, '/') . '/itstore';
        $this->backupAndCopy($srcTheme, $themeDest, $backup . '/themes/itstore');

        // ----- Update every itstore* module
        $updatedModules = [];
        foreach (scandir($srcModules) as $name) {
            if ($name === '.' || $name === '..' || strpos($name, 'itstore') !== 0) {
                continue;
            }
            $src = $srcModules . '/' . $name;
            if (!is_dir($src)) {
                continue;
            }
            $dest = rtrim(_PS_MODULE_DIR_, '/') . '/' . $name;
            // Update the updater itself last / without re-running its upgrade.
            $this->resetTimer();
            $this->backupAndCopy($src, $dest, $backup . '/modules/' . $name);
            $updatedModules[] = $name;
        }
        $this->log('Copied theme + ' . count($updatedModules) . ' module(s).');

        // ----- Run upgrade scripts for installed modules (skip self)
        foreach ($updatedModules as $name) {
            if ($name === 'itstoreupdate') {
                continue;
            }
            if (!Module::isInstalled($name)) {
                continue;
            }
            $this->resetTimer();
            try {
                $module = Module::getInstanceByName($name);
                if ($module && Module::initUpgradeModule($module)) {
                    $module->runUpgradeModule();
                    $this->log('Upgraded ' . $name . ' → v' . $module->version . '.');
                }
            } catch (Exception $e) {
                $this->log('Upgrade warning for ' . $name . ': ' . $e->getMessage());
            }
        }

        // ----- Finalise
        Configuration::updateValue('ITSTORE_UPD_INSTALLED_VERSION', $remote['version']);
        $this->clearCaches();
        $this->rrmdir($work);
        $this->log('Update complete. Now on v' . $remote['version'] . '. Backup: ' . $backup);

        return true;
    }

    /* ---------------------------------------------------------- FILESYSTEM */

    protected function findExtractRoot($work)
    {
        foreach (scandir($work) as $e) {
            if ($e === '.' || $e === '..') {
                continue;
            }
            $p = $work . '/' . $e;
            if (is_dir($p) && is_dir($p . '/modules')) {
                return $p;
            }
        }

        return false;
    }

    protected function backupAndCopy($src, $dest, $backupDir)
    {
        if (is_dir($dest)) {
            @mkdir(dirname($backupDir), 0775, true);
            $this->rcopy($dest, $backupDir);
        }
        $this->rcopy($src, $dest);
    }

    protected function rcopy($src, $dest)
    {
        if (is_file($src)) {
            @mkdir(dirname($dest), 0775, true);
            @copy($src, $dest);

            return;
        }
        if (!is_dir($src)) {
            return;
        }
        @mkdir($dest, 0775, true);
        foreach (scandir($src) as $e) {
            if ($e === '.' || $e === '..') {
                continue;
            }
            $this->rcopy($src . '/' . $e, $dest . '/' . $e);
        }
    }

    protected function rrmdir($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        foreach (scandir($dir) as $e) {
            if ($e === '.' || $e === '..') {
                continue;
            }
            $p = $dir . '/' . $e;
            if (is_dir($p)) {
                $this->rrmdir($p);
            } else {
                @unlink($p);
            }
        }
        @rmdir($dir);
    }

    protected function clearCaches()
    {
        try {
            if (method_exists('Tools', 'clearAllCache')) {
                Tools::clearAllCache();
            } else {
                Tools::clearSmartyCache();
                Tools::clearXMLCache();
            }
        } catch (Exception $e) {
            // best-effort
        }
        // Drop compiled Symfony cache so new PHP/templates are picked up.
        foreach (['prod', 'dev'] as $env) {
            $this->rrmdir(_PS_ROOT_DIR_ . '/var/cache/' . $env);
        }
        $this->log('Caches cleared.');
    }

    /**
     * Report which target directories are writable (for the BO pre-flight).
     */
    public static function writableReport()
    {
        $themeCache = rtrim(_PS_ALL_THEMES_DIR_, '/') . '/itstore/assets/cache';

        return [
            'modules' => is_writable(rtrim(_PS_MODULE_DIR_, '/')),
            'themes' => is_writable(rtrim(_PS_ALL_THEMES_DIR_, '/')),
            'self' => is_writable(_PS_MODULE_DIR_ . 'itstoreupdate'),
            'theme_cache' => is_dir($themeCache) && is_writable($themeCache),
        ];
    }
}
