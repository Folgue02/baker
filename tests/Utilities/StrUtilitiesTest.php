<?php
namespace App\Utilities;

use PHPUnit\Framework\TestCase;

class StrUtilitiesTest extends TestCase
{
    // ====== joinPath
    public function testJoinPaths_onlyBasePath(): void
    {
        $expected = '/Users/folgue02/Desktop';
        $result = StrUtilities::joinPaths('/Users/folgue02/Desktop');

        $this->assertEquals($expected, $result);
    }

    public function testJoinPaths_withSegments(): void
    {
        $expected = '/Users/folgue02/Desktop/scripts/start.sh';
        $result = StrUtilities::joinPaths('/Users/folgue02/Desktop/', 'scripts/start.sh');

        $this->assertEquals($expected, $result);
    }

    // ====== canonicalPath();

    public function testCanonicalPath_simple(): void
    {
        $expected = '/Users/folgue02/Desktop/scripts/start.sh';
        $result = StrUtilities::canonicalPath('scripts/start.sh', '/Users/folgue02/Desktop/');

        $this->assertEquals($expected, $result);
    }

    public function testCanonicalPath_absolutePathNoCwd(): void
    {
        $expected = '/Users/folgue02/Desktop/scripts/start.sh';
        $result = StrUtilities::canonicalPath('/Users/folgue02/Desktop/scripts/start.sh');

        $this->assertEquals($expected, $result);
    }

    public function testCanonicalPath_absoluteWithSpecialDirs(): void
    {
        $expected = '/Users/folgue02/Desktop/scripts/start.sh';
        $result = StrUtilities::canonicalPath('/Users/folgue02/Desktop/scripts/.././scripts/php/../start.sh');

        $this->assertEquals($expected, $result);
    }

    public function testCanonicalPath_relativeWithSpecialDirs(): void
    {
        $expected = '/Users/folgue02/Desktop/scripts/start.sh';
        $result = StrUtilities::canonicalPath('scripts/.././scripts/php/../start.sh', '/Users/folgue02/Desktop');

        $this->assertEquals($expected, $result);
    }

    public function testRelativePathTo_onlyFile(): void
    {
        $expected = '/file.php';
        $result = StrUtilities::relativePathTo('/Users/folgue02/app', '/Users/folgue02/app/file.php');

        $this->assertEquals($expected, $result);
    }

    public function testRelativePathTo_fileInSubdir(): void
    {
        $expected = '/calendar/listing.php';
        $result = StrUtilities::relativePathTo('/Users/folgue02/app', '/Users/folgue02/app/calendar/listing.php');

        $this->assertEquals($expected, $result);
    }

    public function testRelativePathTo_unrelatedPath(): void
    {
        $expected = null;
        $result = StrUtilities::relativePathTo('/Users/folgue02/app', '/Users/folgue02/Documents/calendar/listing.php');

        $this->assertEquals($expected, $result);
    }
}
