<?php
namespace League\Flysystem\Adapter;

use League\Flysystem\Filesystem;
use Mockery;

class XftpAdapterTests extends \PHPUnit_Framework_TestCase
{
    public function setup()
    {

    }

    public function adapterProvider()
    {
        //$adapter    = new Xftp();
        $adapter = Mockery::mock('Nodefortytwo\Xftp\XftpAdapter')->makePartial();

        $adapter->shouldReceive('isConnected')->andReturn(true);

        $filesystem = new Filesystem($adapter);
        return [
            [$filesystem, $adapter],
        ];
    }
    /**
     * @dataProvider adapterProvider
     */
    public function testHas($filesystem, $adapter)
    {
        $contents = $filesystem->listContents('.', true);
        var_dump($contents);
        die();
    }
}

function ftp_systype($connection)
{
    static $connections = [
        'reconnect.me',
        'dont.reconnect.me',
    ];

    if (getenv('FTP_CLOSE_THROW') === 'DISCONNECT_CATCH') {
        throw new ErrorException('ftp_systype');
    }

    if (getenv('FTP_CLOSE_THROW') === 'DISCONNECT_RETHROW') {
        throw new ErrorException('does not contain the correct message');
    }

    if (is_string($connection) && array_key_exists($connection, $connections)) {
        $connections[$connection]++;

        if (strpos($connection, 'dont') !== false || $connections[$connection] < 2) {
            return false;
        }
    }

    return 'LINUX';
}

function ftp_ssl_connect($host)
{
    if ($host === 'fail.me') {
        return false;
    }

    if ($host === 'disconnect.check') {
        return tmpfile();
    }

    return $host;
}

function ftp_delete($conn, $path)
{
    if (strpos($path, 'rm.fail.txt')) {
        return false;
    }

    return true;
}

function ftp_rmdir($connection, $dirname)
{
    if (strpos($dirname, 'rmdir.fail') !== false) {
        return false;
    }

    return true;
}

function ftp_connect($host)
{
    return ftp_ssl_connect($host);
}

function ftp_pasv($connection)
{
    if ($connection === 'pasv.fail') {
        return false;
    }

    return true;
}

function ftp_rename()
{
    return true;
}

function ftp_close($connection)
{
    if (is_resource($connection)) {
        return fclose($connection);
    }

    return true;
}

function ftp_login($connection)
{
    if ($connection === 'login.fail') {
        trigger_error('FTP login failed!!', E_WARNING);

        return false;
    }

    return true;
}

function ftp_chdir($connection, $directory)
{
    if ($connection === 'chdir.fail') {
        return false;
    }

    if ($directory === 'not.found') {
        return false;
    }

    if ($directory === 'windows.not.found') {
        return false;
    }

    if (in_array($directory, ['file1.txt', 'file2.txt', 'dir1'])) {
        return false;
    }

    if ($directory === '0') {
        return false;
    }

    return true;
}

function ftp_pwd($connection)
{
    return 'dirname';
}

function ftp_raw($connection, $command)
{
    if ($command === 'STAT syno.not.found') {
        return [0 => '211- status of syno.not.found:', 1 => 'ftpd: assd: No such file or directory.', 2 => '211 End of status'];
    }

    if ($command === 'syno.unknowndir') {
        return [0 => '211- status of syno.unknowndir:', 1 => 'ftpd: assd: No such file or directory.', 2 => '211 End of status'];
    }

    if (strpos($command, 'unknowndir') !== false) {
        return false;
    }

    return [
        0 => '211-Status of somewhere/folder/dummy.txt:',
        1 => ' -rw-r--r-- 1 ftp ftp 0 Nov 24 13:59 somewhere/folder/dummy.txt',
        2 => '211 End of status',
    ];
}

function ftp_rawlist($connection, $directory)
{
    $directory = str_replace("-A ", "", $directory);

    if (strpos($directory, 'fail.rawlist') !== false) {
        return false;
    }

    if ($directory === 'not.found') {
        return false;
    }

    if ($directory === 'windows.not.found') {
        return ["File not found"];
    }

    if (strpos($directory, 'file1.txt') !== false) {
        return [
            '-rw-r--r--   1 ftp      ftp           409 Aug 19 09:01 file1.txt',
        ];
    }

    if ($directory === '0') {
        return [
            '-rw-r--r--   1 ftp      ftp           409 Aug 19 09:01 0',
        ];
    }

    if (strpos($directory, 'file2.txt') !== false) {
        return [
            '05-23-15  12:09PM                  684 file2.txt',
        ];
    }

    if (strpos($directory, 'dir1') !== false) {
        return [
            '2015-05-23  12:09       <DIR>          dir1',
        ];
    }

    if (strpos($directory, 'rmdir.nested.fail') !== false) {
        return [
            'drwxr-xr-x   2 ftp      ftp          4096 Oct 13  2012 .',
            'drwxr-xr-x   4 ftp      ftp          4096 Nov 24 13:58 ..',
            '-rw-r--r--   1 ftp      ftp           409 Oct 13  2012 rm.fail.txt',
        ];
    }

    if (strpos($directory, 'lastfiledir') !== false) {
        return [
            'drwxr-xr-x   2 ftp      ftp          4096 Feb  6  2012 .',
            'drwxr-xr-x   4 ftp      ftp          4096 Feb  6 13:58 ..',
            '-rw-r--r--   1 ftp      ftp           409 Aug 19 09:01 file1.txt',
            '-rw-r--r--   1 ftp      ftp           409 Aug 14 09:01 file2.txt',
            '-rw-r--r--   1 ftp      ftp           409 Feb  6 10:06 file3.txt',
            '-rw-r--r--   1 ftp      ftp           409 Mar 20  2014 file4.txt',
        ];
    }

    if (strpos($directory, 'spaced.files') !== false) {
        return [
            'drwxr-xr-x   2 ftp      ftp          4096 Feb  6  2012 .',
            'drwxr-xr-x   4 ftp      ftp          4096 Feb  6 13:58 ..',
            '-rw-r--r--   1 ftp      ftp           409 Aug 19 09:01  file1.txt',

        ];
    }

    return [
        'drwxr-xr-x   4 ftp      ftp          4096 Nov 24 13:58 .',
        'drwxr-xr-x  16 ftp      ftp          4096 Sep  2 13:01 ..',
        'drwxr-xr-x   2 ftp      ftp          4096 Oct 13  2012 cgi-bin',
        'drwxr-xr-x   2 ftp      ftp          4096 Nov 24 13:59 folder',
        '-rw-r--r--   1 ftp      ftp           409 Oct 13  2012 index.html',
        '',
        'somewhere/cgi-bin:',
        'drwxr-xr-x   2 ftp      ftp          4096 Oct 13  2012 .',
        'drwxr-xr-x   4 ftp      ftp          4096 Nov 24 13:58 ..',
        '',
        'somewhere/folder:',
        'drwxr-xr-x   2 ftp      ftp          4096 Nov 24 13:59 .',
        'drwxr-xr-x   4 ftp      ftp          4096 Nov 24 13:58 ..',
        '-rw-r--r--   1 ftp      ftp             0 Nov 24 13:59 dummy.txt',
    ];
}

function ftp_mdtm($connection, $path)
{
    switch ($path) {
        case 'lastfiledir/file1.txt':
            return 1408438882;
            break;

        case 'lastfiledir/file2.txt':
            return 1408006883;
            break;

        case 'lastfiledir/file3.txt':
            return 1423217165;
            break;

        case 'lastfiledir/file4.txt':
            return 1395305765;
            break;

        case 'some/file.ext':
            return 1408438882;
            break;
        default:
            return -1;
            break;
    }
}

function ftp_mkdir($connection, $dirname)
{
    if (strpos($dirname, 'mkdir.fail') !== false) {
        return false;
    }

    return true;
}

function ftp_fput($connection, $path)
{
    if (strpos($path, 'write.fail') !== false) {
        return false;
    }

    return true;
}

function ftp_fget($connection, $resource, $path)
{
    if (strpos($path, 'not.found') !== false) {
        return false;
    }

    \fwrite($resource, 'contents');
    rewind($resource);

    return true;
}

function ftp_nlist($connection, $directory)
{
    return ['./some.nested'];
}

function ftp_chmod($connection, $mode, $path)
{
    if (strpos($path, 'chmod.fail') !== false) {
        return false;
    }

    return true;
}

function ftp_set_option($connection, $option, $value)
{
    putenv('USE_PASSV_ADDREESS' . $option . '=' . ($value ? 'YES' : 'NO'));

    return true;
}
