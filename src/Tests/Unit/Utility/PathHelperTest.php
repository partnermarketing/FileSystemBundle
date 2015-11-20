<?php

namespace Partnermarketing\FileSystemBundle\Tests\Unit\Utility;

use Partnermarketing\FileSystemBundle\Utility\PathHelper;

class PathHelperTest extends \PHPUnit_Framework_TestCase
{
    public function testArePathsEqualIgnoringFileExtension()
    {
        $trueCase = PathHelper::arePathsEqualIgnoringFileExtension('folder/file.jpg?qs', 'folder/file.png?qs');
        $this->assertTrue($trueCase);

        $falseCase = PathHelper::arePathsEqualIgnoringFileExtension('folder1/file1.jpg?qs', 'folder2/file2.png?qs');
        $this->assertFalse($falseCase);
    }

    /**
     * @dataProvider removeFileExtensionAndKeepQueryStringDataProvider
     */
    public function testRemoveFileExtensionAndKeepQueryString($input, $expected)
    {
        $this->assertEquals($expected, PathHelper::removeFileExtensionAndKeepQueryString($input));
    }

    public function removeFileExtensionAndKeepQueryStringDataProvider()
    {
        return [
            ['file.png', 'file'],
            ['file.file.file.png', 'file.file.file'],
            ['file.png?qs', 'file?qs'],
            ['path/file.jpg', 'path/file'],
            ['path/file.jpg?qs', 'path/file?qs'],
        ];
    }
}
