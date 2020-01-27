<?php
use org\bovigo\vfs\vfsStream;

// Test for MediaLibraryAdapter and others.
class QueriesTest extends WP_UnitTestCase
{
    private $files_used;
    private $post_id_high = 9999;
    private $dbless_tests_done = false;

    //public static function setUpBeforeClass()
    public function DoSetupDB()
    {
    //  if (! $this->dbless_tests_done)
    //    return;

      //$mysqli = new mysqli("127.0.0.1", "shortpixel", "w76TZ#QUEJaf", "shortpixel_test");
      $file = __DIR__ . '/test_posts.sql';
      if (! file_exists($file))
        $this->fail('File does not exists: '. $file);

      exec('mysql -ushortpixel -pw76TZ#QUEJaf shortpixel_test < ' . $file);

      $settings = new WPShortPixelSettings();
      $settings->backupImages = 0;
      $settings->autoMediaLibrary = false;
    }


    public function setUp()
    {
      $this->root = vfsStream::setup('root', null, $this->getTestFiles() );

    }

    private function getTestFiles()
    {
      $content = file_get_contents(__DIR__ . '/assets/test-image.jpg');
      $files = array(
          'images' => array(
            'mainfile.jpg' => $content,
            'mainfile-250x250.jpg' => $content,  //normal wp
            'mainfile-560x560.jpg' => $content,
            'mainfile-250x250.jpg.webp' => $content,  //normal wp
            'mainfile-250x250.webp' => $content,  //normal wp

            'mainfile-650x650-sufx.jpg' => $content,
            'mainfile-100x100-sufx.jpg' => $content,
            'mainfile-uai-750x500.jpg' => $content, //infix
            'mainfile-uai-500x500.jpg' => $content,
          ),

      );
      $this->files_used = $files;
      return $files;
    }

    public function testPostMetaSliceEmpty()
    {

      $result = WpShortPixelMediaLbraryAdapter::getPostsJoinLessReverse($this->post_id_high, 1, 30);
      $this->assertCount(0, $result);

      $this->dbless_tests_done = true;
    }

    public function testPostMetaSlice()
    {
      $this->doSetupDB();

      $result = WpShortPixelMediaLbraryAdapter::getPostsJoinLessReverse($this->post_id_high, 1, 30);
      $this->assertCount(30, $result);

      // only this test needs db for now.
      $this->dbless_tests_done = false;
    }

    private function getExpected()
    {
      $rooturl = $this->root->url();
      $expected1 = array();
      $expected2 = array();
      $expected3 = array();

      // things that should be filtered by the thumbs extension.
      $files_used = $this->files_used['images'];
      unset($files_used['mainfile.jpg']);
      unset($files_used['mainfile-250x250.jpg.webp']);
      unset($files_used['mainfile-250x250.webp']);

      $i = 0;
      foreach($files_used as $filename => $data)
      {
        $path = $rooturl . '/images/' . $filename;

        if ($i <= 1)
        {
            $expected1[] = $path;
            $expected2[] = $path;
            $expected3[] = $path;
        }
        elseif ($i >= 2 && $i <= 3)
        {
          $expected2[] = $path;
          $expected3[] = $path;
        }
        else {
          $expected3[] = $path;
        }

        $i++;
      }
      return array($expected1, $expected2, $expected3);
    }

    /**
    * Test Shortpixel meta facade.
    */
    public function testFindThumbs()
    {
        $rooturl = $this->root->url();
        $mainfile = $rooturl . '/images/mainfile.jpg';

        list ($expected1,$expected2,$expected3) = $this->getExpected();
        $this->assertTrue(file_exists($mainfile));

        $thumbs1 = WpShortPixelMediaLbraryAdapter::findThumbs($mainfile);

        $this->assertCount(2, $thumbs1);
        $this->assertEquals($expected1, $thumbs1);

        define('SHORTPIXEL_CUSTOM_THUMB_SUFFIXES', '-sufx');

        $this->assertTrue(defined('SHORTPIXEL_CUSTOM_THUMB_SUFFIXES'));

        $thumbs2 = WpShortPixelMediaLbraryAdapter::findThumbs($mainfile);

        $this->assertCount(4, $thumbs2);
        $this->assertEquals($expected2, $thumbs2);

        define('SHORTPIXEL_CUSTOM_THUMB_INFIXES', '-uai');

        $this->assertTrue(defined('SHORTPIXEL_CUSTOM_THUMB_INFIXES'));

        $thumbs3 = WpShortPixelMediaLbraryAdapter::findThumbs($mainfile);

        $this->assertCount(6, $thumbs3);
        $this->assertEquals($expected3, $thumbs3);

        $file_notexists = $rooturl . '/images/weirdfile.jpg';
        $thumbs4 = WpShortPixelMediaLbraryAdapter::findThumbs($file_notexists);
        $this->assertCount(0, $thumbs4);

        $dir_notexists = $rooturl . '/falsedirectory/';
        $thumbs5 = WpShortPixelMediaLbraryAdapter::findThumbs($dir_notexists);
        $this->assertCount(0, $thumbs5);

    }



}
