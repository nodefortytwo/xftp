<?php

namespace Nodefortytwo\Xftp;

use League\Flysystem\AdapterInterface;
use League\Flysystem\Adapter\Ftp;

class XftpAdapter extends Ftp
{
    /**
     * Normalize a Unix file entry.
     *
     * @param string $item
     * @param string $base
     *
     * @return array normalized file array
     */
    protected function normalizeUnixObject($item, $base)
    {
        $item = preg_replace('#\s+#', ' ', trim($item), 7);
        list($permissions,
            /* $number */,
            /* $owner */,
            /* $group */,
            $size,
            $month,
            $day,
            $time_year,
            $name) = explode(' ', $item, 9);
        $type  = $this->detectType($permissions);
        $path  = empty($base) ? $name : $base . $this->separator . $name;

        if ($type === 'dir') {
            return compact('type', 'path');
        }

        $permissions = $this->normalizePermissions($permissions);
        $visibility  = $permissions & 0044 ? AdapterInterface::VISIBILITY_PUBLIC : AdapterInterface::VISIBILITY_PRIVATE;
        $size        = (int) $size;

        if (strpos($time_year, ':')) {
            $time = explode(':', $time_year);
            $year = date('Y');
        } else {
            $year = $time_year;
            $time = [12, 00];
        }

        $month = date('m', strtotime($month));

        $timestamp = mktime($time[0], $time[1], 0, $month, $day, $year);

        return compact('type', 'path', 'visibility', 'size', 'timestamp');
    }
}
